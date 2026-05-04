<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\ProtocolTools;

class ProtocolToolsTest extends AbstractToolsTest
{
    private ProtocolTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new ProtocolTools($this->apiClient);
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

    public function testListProtocolsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listProtocols', ['token' => 'tok']);
        $this->assertGetTo('/protocol/');
    }

    public function testGetProtocolUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getProtocol', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/protocol/3/');
    }

    public function testDeleteProtocolReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteProtocol', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentProtocolTool', ['token' => 'tok']);
    }
}
