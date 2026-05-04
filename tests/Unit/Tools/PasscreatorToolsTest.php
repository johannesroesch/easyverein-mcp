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

    // ── getPassField ──────────────────────────────────────────────────────────

    public function testGetPassFieldUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getPassField', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/pass-field/5/');
    }

    // ── createPassField ───────────────────────────────────────────────────────

    public function testCreatePassFieldUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPassField', ['token' => 'tok', 'field_name' => 'memberName', 'value' => 'Max']);
        $this->assertPostTo('/pass-field/');
        $this->assertBodyContains('field_name', 'memberName');
    }

    // ── updatePassField ───────────────────────────────────────────────────────

    public function testUpdatePassFieldUsesPatch(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('updatePassField', ['token' => 'tok', 'id' => 5, 'value' => 'Anna']);
        $this->assertPatchTo('/pass-field/5/');
        $this->assertBodyContains('value', 'Anna');
    }

    // ── deletePassField ───────────────────────────────────────────────────────

    public function testDeletePassFieldReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePassField', ['token' => 'tok', 'id' => 5]);
        $this->assertDeletedMessage($result);
    }

    // ── getPassTemplate ───────────────────────────────────────────────────────

    public function testGetPassTemplateUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getPassTemplate', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/pass-template/2/');
    }

    // ── createPassTemplate ────────────────────────────────────────────────────

    public function testCreatePassTemplateUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPassTemplate', ['token' => 'tok', 'name' => 'Member Card']);
        $this->assertPostTo('/pass-template/');
        $this->assertBodyContains('name', 'Member Card');
    }

    // ── updatePassTemplate ────────────────────────────────────────────────────

    public function testUpdatePassTemplateUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updatePassTemplate', ['token' => 'tok', 'id' => 2, 'name' => 'Gold Card']);
        $this->assertPatchTo('/pass-template/2/');
        $this->assertBodyContains('name', 'Gold Card');
    }

    // ── deletePassTemplate ────────────────────────────────────────────────────

    public function testDeletePassTemplateReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePassTemplate', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── updatePasscreatorIntegration ──────────────────────────────────────────

    public function testUpdatePasscreatorIntegrationUsesPatch(): void
    {
        $this->addOk('{"id":1}');
        $this->tools->dispatch('updatePasscreatorIntegration', ['token' => 'tok', 'is_active' => true]);
        $this->assertPatchTo('/passcreator-integration/');
        $this->assertBodyContains('is_active', true);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('noSuchPass', ['token' => 'tok']);
    }
}
