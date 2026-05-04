<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\ContactDetailsTools;

class ContactDetailsToolsTest extends AbstractToolsTest
{
    private ContactDetailsTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new ContactDetailsTools($this->apiClient);
    }

    public function testGetDefinitionsReturnsArray(): void
    {
        self::assertNotEmpty($this->tools->getDefinitions());
    }

    public function testAllDefinitionsHaveNameAndDescription(): void
    {
        foreach ($this->tools->getDefinitions() as $def) {
            self::assertArrayHasKey('name', $def);
            self::assertArrayHasKey('description', $def);
        }
    }

    public function testListContactDetailsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetails', ['token' => 'tok']);
        $this->assertGetTo('/contact-details/');
    }

    public function testGetContactDetailsUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getContactDetails', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/contact-details/7/');
    }

    public function testCreateContactDetailsUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createContactDetails', ['token' => 'tok', 'name' => 'Mustermann']);
        $this->assertPostTo('/contact-details/');
    }

    public function testUpdateContactDetailsUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateContactDetails', ['token' => 'tok', 'id' => 7, 'name' => 'Updated']);
        $this->assertPatchTo('/contact-details/7/');
    }

    public function testDeleteContactDetailsReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteContactDetails', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    public function testListContactDetailsGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetailGroups', ['token' => 'tok']);
        $this->assertGetTo('/contact-details-group/');
    }

    // ── listContactDetailsChangeRequests ─────────────────────────────────────

    public function testListContactDetailsChangeRequestsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetailsChangeRequests', ['token' => 'tok']);
        $this->assertGetTo('/contact-details-change-request/');
    }

    public function testListContactDetailsChangeRequestsWithFilter(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetailsChangeRequests', ['token' => 'tok', 'contactDetails' => 5]);
        self::assertStringContainsString('contactDetails=5', $this->lastCall()['url']);
    }

    // ── getContactDetailsChangeRequest ────────────────────────────────────────

    public function testGetContactDetailsChangeRequestUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getContactDetailsChangeRequest', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/contact-details-change-request/3/');
    }

    // ── createContactDetailsChangeRequest ─────────────────────────────────────

    public function testCreateContactDetailsChangeRequestUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createContactDetailsChangeRequest', ['token' => 'tok', 'field_name' => 'city', 'field_value' => 'Berlin']);
        $this->assertPostTo('/contact-details-change-request/');
        $this->assertBodyContains('field_name', 'city');
        $this->assertBodyContains('field_value', 'Berlin');
    }

    // ── updateContactDetailsChangeRequest ─────────────────────────────────────

    public function testUpdateContactDetailsChangeRequestUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateContactDetailsChangeRequest', ['token' => 'tok', 'id' => 3, 'field_value' => 'Munich']);
        $this->assertPatchTo('/contact-details-change-request/3/');
        $this->assertBodyContains('field_value', 'Munich');
    }

    // ── deleteContactDetailsChangeRequest ─────────────────────────────────────

    public function testDeleteContactDetailsChangeRequestReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteContactDetailsChangeRequest', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── listContactDetailsLogs ────────────────────────────────────────────────

    public function testListContactDetailsLogsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetailsLogs', ['token' => 'tok']);
        $this->assertGetTo('/contact-details-log/');
    }

    // ── getContactDetailsLog ──────────────────────────────────────────────────

    public function testGetContactDetailsLogUsesCorrectPath(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('getContactDetailsLog', ['token' => 'tok', 'id' => 8]);
        $this->assertGetTo('/contact-details-log/8/');
    }

    // ── listContactDetailsCustomFieldAssignments ──────────────────────────────

    public function testListContactDetailsCustomFieldAssignmentsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetailsCustomFieldAssignments', ['token' => 'tok']);
        $this->assertGetTo('/contact-details-custom-field-assignment/');
    }

    // ── getContactDetailsCustomFieldAssignment ────────────────────────────────

    public function testGetContactDetailsCustomFieldAssignmentUsesCorrectPath(): void
    {
        $this->addOk('{"id":12}');
        $this->tools->dispatch('getContactDetailsCustomFieldAssignment', ['token' => 'tok', 'id' => 12]);
        $this->assertGetTo('/contact-details-custom-field-assignment/12/');
    }

    // ── createContactDetailsCustomFieldAssignment ─────────────────────────────

    public function testCreateContactDetailsCustomFieldAssignmentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createContactDetailsCustomFieldAssignment', ['token' => 'tok', 'custom_field' => 4, 'value' => 'test']);
        $this->assertPostTo('/contact-details-custom-field-assignment/');
        $this->assertBodyContains('custom_field', 4);
    }

    // ── updateContactDetailsCustomFieldAssignment ─────────────────────────────

    public function testUpdateContactDetailsCustomFieldAssignmentUsesPatch(): void
    {
        $this->addOk('{"id":12}');
        $this->tools->dispatch('updateContactDetailsCustomFieldAssignment', ['token' => 'tok', 'id' => 12, 'value' => 'updated']);
        $this->assertPatchTo('/contact-details-custom-field-assignment/12/');
        $this->assertBodyContains('value', 'updated');
    }

    // ── deleteContactDetailsCustomFieldAssignment ─────────────────────────────

    public function testDeleteContactDetailsCustomFieldAssignmentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteContactDetailsCustomFieldAssignment', ['token' => 'tok', 'id' => 12]);
        $this->assertDeletedMessage($result);
    }

    // ── listFormerMemberData ──────────────────────────────────────────────────

    public function testListFormerMemberDataUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listFormerMemberData', ['token' => 'tok']);
        $this->assertGetTo('/former-member-data/');
    }

    // ── getFormerMemberData ───────────────────────────────────────────────────

    public function testGetFormerMemberDataUsesCorrectPath(): void
    {
        $this->addOk('{"id":15}');
        $this->tools->dispatch('getFormerMemberData', ['token' => 'tok', 'id' => 15]);
        $this->assertGetTo('/former-member-data/15/');
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistent', ['token' => 'tok']);
    }
}
