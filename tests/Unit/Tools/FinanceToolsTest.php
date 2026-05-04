<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\FinanceTools;

class FinanceToolsTest extends AbstractToolsTest
{
    private FinanceTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new FinanceTools($this->apiClient);
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

    public function testListBookingsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listBookings', ['token' => 'tok']);
        $this->assertGetTo('/booking/');
    }

    public function testGetBookingUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getBooking', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/booking/3/');
    }

    public function testCreateBookingUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createBooking', ['token' => 'tok', 'amount' => 99.50]);
        $this->assertPostTo('/booking/');
        $this->assertBodyContains('amount', 99.50);
    }

    public function testDeleteBookingReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteBooking', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    public function testListInvoicesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInvoices', ['token' => 'tok']);
        $this->assertGetTo('/invoice/');
    }

    public function testCreateInvoiceUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createInvoice', ['token' => 'tok', 'amount' => 100.0]);
        $this->assertPostTo('/invoice/');
    }

    public function testDeleteInvoiceReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteInvoice', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    public function testListBankAccountsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listBankAccounts', ['token' => 'tok']);
        $this->assertGetTo('/bank-account/');
    }

    public function testListBookingProjectsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listBookingProjects', ['token' => 'tok']);
        $this->assertGetTo('/booking-project/');
    }

    public function testCreateBankAccountBodyExcludesMissingFields(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createBankAccount', ['token' => 'tok', 'name' => 'Girokonto']);
        $this->assertBodyContains('name', 'Girokonto');
        $this->assertBodyNotContains('import_saldo');
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('unknownFinance', ['token' => 'tok']);
    }
}
