<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\InventoryTools;

class InventoryToolsTest extends AbstractToolsTest
{
    private InventoryTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new InventoryTools($this->apiClient);
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

    public function testListInventoryObjectsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInventoryObjects', ['token' => 'tok']);
        $this->assertGetTo('/inventory-object/');
    }

    public function testGetInventoryObjectUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getInventoryObject', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/inventory-object/7/');
    }

    public function testDeleteInventoryObjectReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteInventoryObject', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentInventoryTool', ['token' => 'tok']);
    }
}
