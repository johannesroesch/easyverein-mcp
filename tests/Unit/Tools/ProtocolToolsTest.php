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

    // ── createProtocol ────────────────────────────────────────────────────────

    public function testCreateProtocolUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createProtocol', ['token' => 'tok', 'name' => 'Board Meeting']);
        $this->assertPostTo('/protocol/');
        $this->assertBodyContains('name', 'Board Meeting');
    }

    // ── updateProtocol ────────────────────────────────────────────────────────

    public function testUpdateProtocolUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateProtocol', ['token' => 'tok', 'id' => 3, 'is_locked' => true]);
        $this->assertPatchTo('/protocol/3/');
        $this->assertBodyContains('is_locked', true);
    }

    // ── listProtocolElements ──────────────────────────────────────────────────

    public function testListProtocolElementsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listProtocolElements', ['token' => 'tok']);
        $this->assertGetTo('/protocol-element/');
    }

    // ── getProtocolElement ────────────────────────────────────────────────────

    public function testGetProtocolElementUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getProtocolElement', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/protocol-element/5/');
    }

    // ── createProtocolElement ─────────────────────────────────────────────────

    public function testCreateProtocolElementUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createProtocolElement', ['token' => 'tok', 'title' => 'Agenda Item 1']);
        $this->assertPostTo('/protocol-element/');
        $this->assertBodyContains('title', 'Agenda Item 1');
    }

    // ── updateProtocolElement ─────────────────────────────────────────────────

    public function testUpdateProtocolElementUsesPatch(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('updateProtocolElement', ['token' => 'tok', 'id' => 5, 'state' => 'done']);
        $this->assertPatchTo('/protocol-element/5/');
        $this->assertBodyContains('state', 'done');
    }

    // ── deleteProtocolElement ─────────────────────────────────────────────────

    public function testDeleteProtocolElementReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteProtocolElement', ['token' => 'tok', 'id' => 5]);
        $this->assertDeletedMessage($result);
    }

    // ── listProtocolElementComments ───────────────────────────────────────────

    public function testListProtocolElementCommentsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listProtocolElementComments', ['token' => 'tok']);
        $this->assertGetTo('/protocol-element-comment/');
    }

    // ── getProtocolElementComment ─────────────────────────────────────────────

    public function testGetProtocolElementCommentUsesCorrectPath(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('getProtocolElementComment', ['token' => 'tok', 'id' => 8]);
        $this->assertGetTo('/protocol-element-comment/8/');
    }

    // ── createProtocolElementComment ──────────────────────────────────────────

    public function testCreateProtocolElementCommentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createProtocolElementComment', ['token' => 'tok', 'title' => 'Comment', 'text' => 'Details']);
        $this->assertPostTo('/protocol-element-comment/');
        $this->assertBodyContains('title', 'Comment');
    }

    // ── updateProtocolElementComment ──────────────────────────────────────────

    public function testUpdateProtocolElementCommentUsesPatch(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('updateProtocolElementComment', ['token' => 'tok', 'id' => 8, 'text' => 'Updated']);
        $this->assertPatchTo('/protocol-element-comment/8/');
        $this->assertBodyContains('text', 'Updated');
    }

    // ── deleteProtocolElementComment ──────────────────────────────────────────

    public function testDeleteProtocolElementCommentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteProtocolElementComment', ['token' => 'tok', 'id' => 8]);
        $this->assertDeletedMessage($result);
    }

    // ── listProtocolUploads ───────────────────────────────────────────────────

    public function testListProtocolUploadsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listProtocolUploads', ['token' => 'tok']);
        $this->assertGetTo('/protocol-upload/');
    }

    // ── getProtocolUpload ─────────────────────────────────────────────────────

    public function testGetProtocolUploadUsesCorrectPath(): void
    {
        $this->addOk('{"id":11}');
        $this->tools->dispatch('getProtocolUpload', ['token' => 'tok', 'id' => 11]);
        $this->assertGetTo('/protocol-upload/11/');
    }

    // ── createProtocolUpload ──────────────────────────────────────────────────

    public function testCreateProtocolUploadUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createProtocolUpload', ['token' => 'tok', 'protocol' => 3, 'name' => 'Attachment']);
        $this->assertPostTo('/protocol-upload/');
        $this->assertBodyContains('protocol', 3);
    }

    // ── deleteProtocolUpload ──────────────────────────────────────────────────

    public function testDeleteProtocolUploadReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteProtocolUpload', ['token' => 'tok', 'id' => 11]);
        $this->assertDeletedMessage($result);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentProtocolTool', ['token' => 'tok']);
    }
}
