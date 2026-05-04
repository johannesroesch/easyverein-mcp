<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\EventTools;

class EventToolsTest extends AbstractToolsTest
{
    private EventTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new EventTools($this->apiClient);
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

    public function testListEventsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listEvents', ['token' => 'tok']);
        $this->assertGetTo('/event/');
    }

    public function testGetEventUsesCorrectPath(): void
    {
        $this->addOk('{"id":10}');
        $this->tools->dispatch('getEvent', ['token' => 'tok', 'id' => 10]);
        $this->assertGetTo('/event/10/');
    }

    public function testCreateEventUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createEvent', ['token' => 'tok', 'name' => 'Sommerfest']);
        $this->assertPostTo('/event/');
        $this->assertBodyContains('name', 'Sommerfest');
    }

    public function testUpdateEventUsesPatch(): void
    {
        $this->addOk('{"id":10}');
        $this->tools->dispatch('updateEvent', ['token' => 'tok', 'id' => 10, 'title' => 'Updated']);
        $this->assertPatchTo('/event/10/');
    }

    public function testDeleteEventReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteEvent', ['token' => 'tok', 'id' => 10]);
        $this->assertDeletedMessage($result);
    }

    public function testListParticipationsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listParticipations', ['token' => 'tok']);
        $this->assertGetTo('/participation/');
    }

    public function testListParticipationsWithEventFilter(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listParticipations', ['token' => 'tok', 'event' => 5]);
        $url = $this->lastCall()['url'];
        self::assertStringContainsString('event=5', $url);
    }

    // ── getParticipation ──────────────────────────────────────────────────────

    public function testGetParticipationUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getParticipation', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/participation/3/');
    }

    // ── createParticipation ───────────────────────────────────────────────────

    public function testCreateParticipationUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createParticipation', ['token' => 'tok', 'name' => 'Max Mustermann']);
        $this->assertPostTo('/participation/');
        $this->assertBodyContains('name', 'Max Mustermann');
    }

    // ── updateParticipation ───────────────────────────────────────────────────

    public function testUpdateParticipationUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateParticipation', ['token' => 'tok', 'id' => 3, 'state' => 2]);
        $this->assertPatchTo('/participation/3/');
        $this->assertBodyContains('state', 2);
    }

    // ── deleteParticipation ───────────────────────────────────────────────────

    public function testDeleteParticipationReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteParticipation', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── listEventCustomFieldAssignments ───────────────────────────────────────

    public function testListEventCustomFieldAssignmentsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listEventCustomFieldAssignments', ['token' => 'tok']);
        $this->assertGetTo('/event-custom-field-assignment/');
    }

    // ── getEventCustomFieldAssignment ─────────────────────────────────────────

    public function testGetEventCustomFieldAssignmentUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getEventCustomFieldAssignment', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/event-custom-field-assignment/7/');
    }

    // ── createEventCustomFieldAssignment ─────────────────────────────────────

    public function testCreateEventCustomFieldAssignmentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createEventCustomFieldAssignment', ['token' => 'tok', 'custom_field' => 2, 'value' => 'yes']);
        $this->assertPostTo('/event-custom-field-assignment/');
        $this->assertBodyContains('custom_field', 2);
    }

    // ── updateEventCustomFieldAssignment ─────────────────────────────────────

    public function testUpdateEventCustomFieldAssignmentUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateEventCustomFieldAssignment', ['token' => 'tok', 'id' => 7, 'value' => 'no']);
        $this->assertPatchTo('/event-custom-field-assignment/7/');
        $this->assertBodyContains('value', 'no');
    }

    // ── deleteEventCustomFieldAssignment ─────────────────────────────────────

    public function testDeleteEventCustomFieldAssignmentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteEventCustomFieldAssignment', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── listApplicationForms ─────────────────────────────────────────────────

    public function testListApplicationFormsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listApplicationForms', ['token' => 'tok']);
        $this->assertGetTo('/application-form/');
    }

    // ── getApplicationForm ────────────────────────────────────────────────────

    public function testGetApplicationFormUsesCorrectPath(): void
    {
        $this->addOk('{"id":22}');
        $this->tools->dispatch('getApplicationForm', ['token' => 'tok', 'id' => 22]);
        $this->assertGetTo('/application-form/22/');
    }

    // ── createApplicationForm ─────────────────────────────────────────────────

    public function testCreateApplicationFormUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createApplicationForm', ['token' => 'tok', 'title' => 'Signup Form']);
        $this->assertPostTo('/application-form/');
        $this->assertBodyContains('title', 'Signup Form');
    }

    // ── updateApplicationForm ─────────────────────────────────────────────────

    public function testUpdateApplicationFormUsesPatch(): void
    {
        $this->addOk('{"id":22}');
        $this->tools->dispatch('updateApplicationForm', ['token' => 'tok', 'id' => 22, 'title' => 'Updated']);
        $this->assertPatchTo('/application-form/22/');
        $this->assertBodyContains('title', 'Updated');
    }

    // ── deleteApplicationForm ─────────────────────────────────────────────────

    public function testDeleteApplicationFormReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteApplicationForm', ['token' => 'tok', 'id' => 22]);
        $this->assertDeletedMessage($result);
    }

    // ── listApplicationFormElements ───────────────────────────────────────────

    public function testListApplicationFormElementsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listApplicationFormElements', ['token' => 'tok']);
        $this->assertGetTo('/application-form-element/');
    }

    // ── getApplicationFormElement ─────────────────────────────────────────────

    public function testGetApplicationFormElementUsesCorrectPath(): void
    {
        $this->addOk('{"id":30}');
        $this->tools->dispatch('getApplicationFormElement', ['token' => 'tok', 'id' => 30]);
        $this->assertGetTo('/application-form-element/30/');
    }

    // ── deleteApplicationFormElement ──────────────────────────────────────────

    public function testDeleteApplicationFormElementReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteApplicationFormElement', ['token' => 'tok', 'id' => 30]);
        $this->assertDeletedMessage($result);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('unknownEvent', ['token' => 'tok']);
    }

    public function testCreateApplicationFormElementConvertsIdToUrl(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createApplicationFormElement', [
            'token'            => 'tok',
            'application_form' => 22719,
            'kind'             => 'headline',
        ]);
        $this->assertPostTo('/application-form-element/');
        $body = json_decode($this->lastCall()['body'], true);
        self::assertSame('https://easyverein.com/api/v3.0/application-form/22719', $body['application_form']);
    }

    public function testUpdateApplicationFormElementConvertsIdToUrl(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updateApplicationFormElement', [
            'token'            => 'tok',
            'id'               => 1,
            'application_form' => 22719,
        ]);
        $this->assertPatchTo('/application-form-element/1/');
        $body = json_decode($this->lastCall()['body'], true);
        self::assertSame('https://easyverein.com/api/v3.0/application-form/22719', $body['application_form']);
    }
}
