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

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentTool', ['token' => 'tok']);
    }
}
