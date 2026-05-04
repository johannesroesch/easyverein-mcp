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
