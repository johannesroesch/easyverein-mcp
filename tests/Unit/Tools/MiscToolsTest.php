<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\MiscTools;

class MiscToolsTest extends AbstractToolsTest
{
    private MiscTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new MiscTools($this->apiClient);
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

    public function testListTasksUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listTasks', ['token' => 'tok']);
        $this->assertGetTo('/task/');
    }

    public function testGetTaskUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getTask', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/task/4/');
    }

    public function testCreateTaskUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createTask', ['token' => 'tok', 'name' => 'Important Task']);
        $this->assertPostTo('/task/');
    }

    public function testDeleteTaskReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteTask', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    public function testListInventoryObjectsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInventoryObjects', ['token' => 'tok']);
        $this->assertGetTo('/inventory-object/');
    }

    public function testGetOrganizationUsesGet(): void
    {
        $this->addOk('{"id":1,"name":"Test Club"}');
        $this->tools->dispatch('getOrganization', ['token' => 'tok']);
        $this->assertGetTo('/organization/');
    }

    public function testListCustomFieldsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCustomFields', ['token' => 'tok']);
        $this->assertGetTo('/custom-field/');
    }

    public function testListDocumentTemplatesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listDocumentTemplates', ['token' => 'tok']);
        $this->assertGetTo('/document-template/');
    }

    public function testListProtocolsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listProtocols', ['token' => 'tok']);
        $this->assertGetTo('/protocol/');
    }

    public function testListCalendarsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCalendars', ['token' => 'tok']);
        $this->assertGetTo('/calendar/');
    }

    public function testListLocationsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listLocations', ['token' => 'tok']);
        $this->assertGetTo('/location/');
    }

    public function testGetWastebasketUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listWastebasket', ['token' => 'tok']);
        $this->assertGetTo('/wastebasket/');
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentMiscTool', ['token' => 'tok']);
    }
}
