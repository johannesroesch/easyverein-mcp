<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\PasscreatorTools;

class PasscreatorToolsTest extends AbstractToolsTest
{
    private PasscreatorTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new PasscreatorTools($this->apiClient);
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

    public function testListPassesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPasses', ['token' => 'tok']);
        $this->assertGetTo('/pass/');
    }

    public function testGetPassUsesCorrectPath(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('getPass', ['token' => 'tok', 'id' => 11]);
        $this->assertGetTo('/pass/11/');
    }

    public function testCreatePassUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPass', ['token' => 'tok', 'name' => 'My Pass', 'member' => 42]);
        $this->assertPostTo('/pass/');
        $this->assertBodyContains('name', 'My Pass');
        $this->assertBodyContains('member', 42);
    }

    public function testUpdatePassUsesPatch(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('updatePass', ['token' => 'tok', 'id' => 11, 'name' => 'Updated Pass']);
        $this->assertPatchTo('/pass/11/');
    }

    public function testDeletePassReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePass', ['token' => 'tok', 'id' => 11]);
        $this->assertDeletedMessage($result);
    }

    public function testListPassFieldsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPassFields', ['token' => 'tok']);
        $this->assertGetTo('/pass-field/');
    }

    public function testListPassTemplatesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPassTemplates', ['token' => 'tok']);
        $this->assertGetTo('/pass-template/');
    }

    public function testGetPasscreatorIntegrationUsesGet(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('getPasscreatorIntegration', ['token' => 'tok']);
        $this->assertGetTo('/passcreator-integration/');
    }

    public function testCreatePassBodyExcludesMissingFields(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPass', ['token' => 'tok', 'name' => 'Only Name']);
        $this->assertBodyContains('name', 'Only Name');
        $this->assertBodyNotContains('serial_number');
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('noSuchPass', ['token' => 'tok']);
    }
}
