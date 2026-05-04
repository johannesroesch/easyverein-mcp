<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

/**
 * Tests for inferAnnotations() logic exposed via tools/list.
 *
 * Annotation rules:
 *   list* / get*  → readOnlyHint:true,  destructiveHint:false, idempotentHint:true
 *   create*       → readOnlyHint:false, destructiveHint:false, idempotentHint:false
 *   update*       → readOnlyHint:false, destructiveHint:false, idempotentHint:true
 *   delete*       → readOnlyHint:false, destructiveHint:true,  idempotentHint:true
 *   cancellation  → readOnlyHint:false, destructiveHint:true,  idempotentHint:false
 *   refreshToken  → readOnlyHint:false, destructiveHint:false, idempotentHint:false
 */
class McpServerAnnotationsTest extends AbstractMcpTest
{
    private function getTools(): array
    {
        $result = $this->post($this->jsonRpc('tools/list'));
        return $result['data']['result']['tools'];
    }

    private function findTool(string $name): ?array
    {
        return array_values(array_filter($this->getTools(), fn($t) => $t['name'] === $name))[0] ?? null;
    }

    // ── list* tools are registered as resources, not tools —————————————————————
    // ── checkDiscountCode is readOnly (explicitly annotated) ─────────────────

    public function testCheckDiscountCodeIsReadOnly(): void
    {
        $tool = $this->findTool('checkDiscountCode');
        self::assertNotNull($tool);
        self::assertTrue($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertTrue($tool['annotations']['idempotentHint']);
    }

    // ── get* → readOnly (getToken starts with "get") ──────────────────────────

    public function testGetToolIsReadOnly(): void
    {
        $tool = $this->findTool('getToken');
        self::assertNotNull($tool);
        self::assertTrue($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertTrue($tool['annotations']['idempotentHint']);
    }

    // ── create* → not readOnly, not destructive, not idempotent ──────────────

    public function testCreateToolIsNotReadOnlyNotIdempotent(): void
    {
        $tool = $this->findTool('createMember');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertFalse($tool['annotations']['idempotentHint']);
    }

    public function testCreateEventAnnotations(): void
    {
        $tool = $this->findTool('createEvent');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertFalse($tool['annotations']['idempotentHint']);
    }

    // ── update* → not readOnly, not destructive, idempotent ──────────────────

    public function testUpdateToolIsIdempotentNotDestructive(): void
    {
        $tool = $this->findTool('updateMember');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertTrue($tool['annotations']['idempotentHint']);
    }

    public function testUpdateInvoiceAnnotations(): void
    {
        $tool = $this->findTool('updateInvoice');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertTrue($tool['annotations']['idempotentHint']);
    }

    // ── delete* → destructive, idempotent ────────────────────────────────────

    public function testDeleteToolIsDestructive(): void
    {
        $tool = $this->findTool('deleteMember');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertTrue($tool['annotations']['destructiveHint']);
        self::assertTrue($tool['annotations']['idempotentHint']);
    }

    public function testDeleteBookingAnnotations(): void
    {
        $tool = $this->findTool('deleteBooking');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertTrue($tool['annotations']['destructiveHint']);
        self::assertTrue($tool['annotations']['idempotentHint']);
    }

    // ── cancellation → destructive, not idempotent ───────────────────────────

    public function testCancellationIsDestructiveNotIdempotent(): void
    {
        $tool = $this->findTool('cancellation');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertTrue($tool['annotations']['destructiveHint']);
        self::assertFalse($tool['annotations']['idempotentHint']);
    }

    // ── refreshToken → not readOnly, not destructive, not idempotent ─────────

    public function testRefreshTokenAnnotations(): void
    {
        $tool = $this->findTool('refreshToken');
        self::assertNotNull($tool);
        self::assertFalse($tool['annotations']['readOnlyHint']);
        self::assertFalse($tool['annotations']['destructiveHint']);
        self::assertFalse($tool['annotations']['idempotentHint']);
    }

    // ── All tools have annotations ────────────────────────────────────────────

    public function testAllToolsHaveAnnotations(): void
    {
        foreach ($this->getTools() as $tool) {
            self::assertArrayHasKey('annotations', $tool, "Tool {$tool['name']} missing annotations");
            self::assertArrayHasKey('readOnlyHint', $tool['annotations']);
            self::assertArrayHasKey('destructiveHint', $tool['annotations']);
            self::assertArrayHasKey('idempotentHint', $tool['annotations']);
        }
    }
}
