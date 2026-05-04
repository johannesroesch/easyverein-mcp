<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit;

use EasyVerein\Mcp\PromptRegistry;
use PHPUnit\Framework\TestCase;

class PromptRegistryTest extends TestCase
{
    private PromptRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new PromptRegistry();
    }

    public function testCountIs15(): void
    {
        self::assertSame(15, $this->registry->count());
    }

    public function testListHas15Entries(): void
    {
        $list = $this->registry->list();
        self::assertCount(15, $list['prompts']);
    }

    public function testAllListEntriesHaveNameAndDescription(): void
    {
        foreach ($this->registry->list()['prompts'] as $prompt) {
            self::assertArrayHasKey('name', $prompt);
            self::assertArrayHasKey('description', $prompt);
            self::assertNotEmpty($prompt['name']);
            self::assertNotEmpty($prompt['description']);
        }
    }

    public function testGetMemberOverviewHasUserMessage(): void
    {
        $result = $this->registry->get('member-overview', []);
        $this->assertHasUserMessage($result);
    }

    public function testGetMemberSearchInterpolatesQuery(): void
    {
        $result = $this->registry->get('member-search', ['query' => 'Max Mustermann']);
        $text   = $result['messages'][0]['content']['text'];
        self::assertStringContainsString('Max Mustermann', $text);
    }

    public function testGetMonthlyBookingsInterpolatesMonthAndYear(): void
    {
        $result = $this->registry->get('monthly-bookings', ['month' => '3', 'year' => '2025']);
        $text   = $result['messages'][0]['content']['text'];
        self::assertStringContainsString('3', $text);
        self::assertStringContainsString('2025', $text);
    }

    public function testGetInvoiceForMemberInterpolatesMember(): void
    {
        $result = $this->registry->get('invoice-for-member', ['member' => 'Erika Musterfrau']);
        $text   = $result['messages'][0]['content']['text'];
        self::assertStringContainsString('Erika Musterfrau', $text);
    }

    public function testGetEventParticipantsInterpolatesEvent(): void
    {
        $result = $this->registry->get('event-participants', ['event' => 'Sommerfest']);
        $text   = $result['messages'][0]['content']['text'];
        self::assertStringContainsString('Sommerfest', $text);
    }

    public function testGetMemberBirthdayInterpolatesMonth(): void
    {
        $result = $this->registry->get('member-birthday', ['month' => '12']);
        $text   = $result['messages'][0]['content']['text'];
        self::assertStringContainsString('12', $text);
    }

    /** @dataProvider allPromptNames */
    public function testGetReturnsDescriptionAndMessages(string $name): void
    {
        $result = $this->registry->get($name, [
            'query'  => 'test',
            'month'  => '1',
            'year'   => '2025',
            'member' => 'Test',
            'event'  => 'TestEvent',
        ]);
        self::assertArrayHasKey('description', $result);
        self::assertArrayHasKey('messages', $result);
        self::assertNotEmpty($result['messages']);
        self::assertSame('user', $result['messages'][0]['role']);
    }

    public static function allPromptNames(): array
    {
        return [
            ['member-overview'],
            ['member-search'],
            ['member-onboard'],
            ['open-invoices'],
            ['monthly-bookings'],
            ['invoice-for-member'],
            ['upcoming-events'],
            ['event-participants'],
            ['club-summary'],
            ['pending-tasks'],
            ['finance-summary'],
            ['member-birthday'],
            ['event-create'],
            ['inventory-overview'],
            ['forum-overview'],
        ];
    }

    public function testGetUnknownPromptThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->get('non-existent-prompt', []);
    }

    private function assertHasUserMessage(array $result): void
    {
        self::assertArrayHasKey('messages', $result);
        $messages = $result['messages'];
        self::assertNotEmpty($messages);
        self::assertSame('user', $messages[0]['role']);
        self::assertNotEmpty($messages[0]['content']['text']);
    }
}
