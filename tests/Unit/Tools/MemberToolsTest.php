<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\MemberTools;

class MemberToolsTest extends AbstractToolsTest
{
    private MemberTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new MemberTools($this->apiClient);
    }

    // ── getDefinitions ───────────────────────────────────────────────────────

    public function testGetDefinitionsReturnsArray(): void
    {
        $defs = $this->tools->getDefinitions();
        self::assertIsArray($defs);
        self::assertNotEmpty($defs);
    }

    public function testAllDefinitionsHaveNameAndDescription(): void
    {
        foreach ($this->tools->getDefinitions() as $def) {
            self::assertArrayHasKey('name', $def);
            self::assertArrayHasKey('description', $def);
        }
    }

    public function testSomeDefinitionsHaveUri(): void
    {
        $withUri    = 0;
        $withoutUri = 0;
        foreach ($this->tools->getDefinitions() as $def) {
            if (isset($def['uri'])) {
                $withUri++;
            } else {
                $withoutUri++;
            }
        }
        self::assertGreaterThan(0, $withUri, 'Expected some entries with uri (resources)');
        self::assertGreaterThan(0, $withoutUri, 'Expected some entries without uri (tools)');
    }

    // ── listMembers ───────────────────────────────────────────────────────────

    public function testListMembersUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listMembers', ['token' => 'tok']);
        $this->assertGetTo('/member/');
    }

    public function testListMembersWithPagination(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listMembers', ['token' => 'tok', 'limit' => 10, 'page' => 2]);
        $url = $this->lastCall()['url'];
        self::assertStringContainsString('limit=10', $url);
        self::assertStringContainsString('page=2', $url);
    }

    // ── getMember ─────────────────────────────────────────────────────────────

    public function testGetMemberUsesCorrectPath(): void
    {
        $this->addOk('{"id":42}');
        $this->tools->dispatch('getMember', ['token' => 'tok', 'id' => 42]);
        $this->assertGetTo('/member/42/');
    }

    // ── createMember ──────────────────────────────────────────────────────────

    public function testCreateMemberUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMember', ['token' => 'tok', 'join_date' => '2025-01-01']);
        $this->assertPostTo('/member/');
    }

    public function testCreateMemberBodyContainsFields(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMember', ['token' => 'tok', 'join_date' => '2025-01-01', 'membership_number' => 'M-001']);
        $this->assertBodyContains('join_date', '2025-01-01');
        $this->assertBodyContains('membership_number', 'M-001');
    }

    public function testCreateMemberBodyExcludesMissingFields(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMember', ['token' => 'tok', 'join_date' => '2025-01-01']);
        $this->assertBodyNotContains('membership_number');
    }

    // ── updateMember ──────────────────────────────────────────────────────────

    public function testUpdateMemberUsesPatch(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('updateMember', ['token' => 'tok', 'id' => 5, 'join_date' => '2024-01-01']);
        $this->assertPatchTo('/member/5/');
    }

    // ── deleteMember ──────────────────────────────────────────────────────────

    public function testDeleteMemberUsesDelete(): void
    {
        $this->addDeleted();
        $this->tools->dispatch('deleteMember', ['token' => 'tok', 'id' => 3]);
        $this->assertDeleteTo('/member/3/');
    }

    public function testDeleteMemberReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteMember', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── Member Groups ─────────────────────────────────────────────────────────

    public function testListMemberGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listMemberGroups', ['token' => 'tok']);
        $this->assertGetTo('/member-group/');
    }

    public function testCreateMemberGroupUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMemberGroup', ['token' => 'tok', 'name' => 'Test Group']);
        $this->assertPostTo('/member-group/');
        $this->assertBodyContains('name', 'Test Group');
    }

    public function testDeleteMemberGroupReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteMemberGroup', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── Member Group Assignments ───────────────────────────────────────────────

    public function testListMemberGroupAssignmentsWithMemberFilter(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listMemberGroupAssignments', ['token' => 'tok', 'member' => 42]);
        $url = $this->lastCall()['url'];
        self::assertStringContainsString('member=42', $url);
    }

    // ── getMemberGroup ────────────────────────────────────────────────────────

    public function testGetMemberGroupUsesCorrectPath(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('getMemberGroup', ['token' => 'tok', 'id' => 9]);
        $this->assertGetTo('/member-group/9/');
    }

    // ── updateMemberGroup ─────────────────────────────────────────────────────

    public function testUpdateMemberGroupUsesPatch(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('updateMemberGroup', ['token' => 'tok', 'id' => 9, 'name' => 'Updated']);
        $this->assertPatchTo('/member-group/9/');
        $this->assertBodyContains('name', 'Updated');
    }

    // ── createMemberGroupAssignment ───────────────────────────────────────────

    public function testCreateMemberGroupAssignmentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMemberGroupAssignment', ['token' => 'tok', 'member' => 5, 'member_group' => 2]);
        $this->assertPostTo('/member-group-assignment/');
        $this->assertBodyContains('member', 5);
        $this->assertBodyContains('member_group', 2);
    }

    // ── deleteMemberGroupAssignment ───────────────────────────────────────────

    public function testDeleteMemberGroupAssignmentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteMemberGroupAssignment', ['token' => 'tok', 'id' => 10]);
        $this->assertDeletedMessage($result);
    }

    // ── getMemberCustomFieldAssignment ────────────────────────────────────────

    public function testGetMemberCustomFieldAssignmentUsesCorrectPath(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('getMemberCustomFieldAssignment', ['token' => 'tok', 'id' => 11]);
        $this->assertGetTo('/member-custom-field-assignment/11/');
    }

    // ── createMemberCustomFieldAssignment ────────────────────────────────────

    public function testCreateMemberCustomFieldAssignmentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMemberCustomFieldAssignment', ['token' => 'tok', 'custom_field' => 3, 'value' => 'red']);
        $this->assertPostTo('/member-custom-field-assignment/');
        $this->assertBodyContains('custom_field', 3);
        $this->assertBodyContains('value', 'red');
    }

    // ── updateMemberCustomFieldAssignment ────────────────────────────────────

    public function testUpdateMemberCustomFieldAssignmentUsesPatch(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('updateMemberCustomFieldAssignment', ['token' => 'tok', 'id' => 11, 'value' => 'blue']);
        $this->assertPatchTo('/member-custom-field-assignment/11/');
        $this->assertBodyContains('value', 'blue');
    }

    // ── deleteMemberCustomFieldAssignment ────────────────────────────────────

    public function testDeleteMemberCustomFieldAssignmentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteMemberCustomFieldAssignment', ['token' => 'tok', 'id' => 11]);
        $this->assertDeletedMessage($result);
    }

    // ── listMemberCustomFieldAssignmentChangeRequests ─────────────────────────

    public function testListMemberCustomFieldAssignmentChangeRequestsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listMemberCustomFieldAssignmentChangeRequests', ['token' => 'tok']);
        $this->assertGetTo('/member-custom-field-assignment-change-request/');
    }

    // ── getMemberCustomFieldAssignmentChangeRequest ───────────────────────────

    public function testGetMemberCustomFieldAssignmentChangeRequestUsesCorrectPath(): void
    {
        $this->addOk('{"id":20}');
        $this->tools->dispatch('getMemberCustomFieldAssignmentChangeRequest', ['token' => 'tok', 'id' => 20]);
        $this->assertGetTo('/member-custom-field-assignment-change-request/20/');
    }

    // ── createMemberCustomFieldAssignmentChangeRequest ────────────────────────

    public function testCreateMemberCustomFieldAssignmentChangeRequestUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createMemberCustomFieldAssignmentChangeRequest', ['token' => 'tok', 'field_value' => 'new']);
        $this->assertPostTo('/member-custom-field-assignment-change-request/');
        $this->assertBodyContains('field_value', 'new');
    }

    // ── updateMemberCustomFieldAssignmentChangeRequest ────────────────────────

    public function testUpdateMemberCustomFieldAssignmentChangeRequestUsesPatch(): void
    {
        $this->addOk('{"id":20}');
        $this->tools->dispatch('updateMemberCustomFieldAssignmentChangeRequest', ['token' => 'tok', 'id' => 20, 'field_value' => 'updated']);
        $this->assertPatchTo('/member-custom-field-assignment-change-request/20/');
        $this->assertBodyContains('field_value', 'updated');
    }

    // ── deleteMemberCustomFieldAssignmentChangeRequest ────────────────────────

    public function testDeleteMemberCustomFieldAssignmentChangeRequestReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteMemberCustomFieldAssignmentChangeRequest', ['token' => 'tok', 'id' => 20]);
        $this->assertDeletedMessage($result);
    }

    // ── listMemberCustomFieldAssignments with member filter ───────────────────

    public function testListMemberCustomFieldAssignmentsWithMemberFilter(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listMemberCustomFieldAssignments', ['token' => 'tok', 'member' => 7]);
        $url = $this->lastCall()['url'];
        self::assertStringContainsString('member=7', $url);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentTool', ['token' => 'tok']);
    }
}
