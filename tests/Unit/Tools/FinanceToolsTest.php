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

    // ── updateBooking ─────────────────────────────────────────────────────────

    public function testUpdateBookingUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateBooking', ['token' => 'tok', 'id' => 3, 'amount' => 50.5]);
        $this->assertPatchTo('/booking/3/');
        $this->assertBodyContains('amount', 50.5);
    }

    // ── getInvoice ────────────────────────────────────────────────────────────

    public function testGetInvoiceUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getInvoice', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/invoice/5/');
    }

    // ── updateInvoice ─────────────────────────────────────────────────────────

    public function testUpdateInvoiceUsesPatch(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('updateInvoice', ['token' => 'tok', 'id' => 5, 'receiver' => 'Hans']);
        $this->assertPatchTo('/invoice/5/');
        $this->assertBodyContains('receiver', 'Hans');
    }

    // ── listInvoiceItems ──────────────────────────────────────────────────────

    public function testListInvoiceItemsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInvoiceItems', ['token' => 'tok']);
        $this->assertGetTo('/invoice-item/');
    }

    public function testListInvoiceItemsWithInvoiceFilter(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInvoiceItems', ['token' => 'tok', 'invoice' => 9]);
        self::assertStringContainsString('invoice=9', $this->lastCall()['url']);
    }

    // ── getInvoiceItem ────────────────────────────────────────────────────────

    public function testGetInvoiceItemUsesCorrectPath(): void
    {
        $this->addOk('{"id":14}');
        $this->tools->dispatch('getInvoiceItem', ['token' => 'tok', 'id' => 14]);
        $this->assertGetTo('/invoice-item/14/');
    }

    // ── createInvoiceItem ─────────────────────────────────────────────────────

    public function testCreateInvoiceItemUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createInvoiceItem', ['token' => 'tok', 'title' => 'Membership', 'unit_price' => 50.0]);
        $this->assertPostTo('/invoice-item/');
        $this->assertBodyContains('title', 'Membership');
    }

    // ── updateInvoiceItem ─────────────────────────────────────────────────────

    public function testUpdateInvoiceItemUsesPatch(): void
    {
        $this->addOk('{"id":14}');
        $this->tools->dispatch('updateInvoiceItem', ['token' => 'tok', 'id' => 14, 'quantity' => 2.5]);
        $this->assertPatchTo('/invoice-item/14/');
        $this->assertBodyContains('quantity', 2.5);
    }

    // ── deleteInvoiceItem ─────────────────────────────────────────────────────

    public function testDeleteInvoiceItemReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteInvoiceItem', ['token' => 'tok', 'id' => 14]);
        $this->assertDeletedMessage($result);
    }

    // ── getBankAccount ────────────────────────────────────────────────────────

    public function testGetBankAccountUsesCorrectPath(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('getBankAccount', ['token' => 'tok', 'id' => 6]);
        $this->assertGetTo('/bank-account/6/');
    }

    // ── updateBankAccount ─────────────────────────────────────────────────────

    public function testUpdateBankAccountUsesPatch(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('updateBankAccount', ['token' => 'tok', 'id' => 6, 'name' => 'Sparkasse']);
        $this->assertPatchTo('/bank-account/6/');
        $this->assertBodyContains('name', 'Sparkasse');
    }

    // ── deleteBankAccount ─────────────────────────────────────────────────────

    public function testDeleteBankAccountReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteBankAccount', ['token' => 'tok', 'id' => 6]);
        $this->assertDeletedMessage($result);
    }

    // ── getBookingProject ─────────────────────────────────────────────────────

    public function testGetBookingProjectUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getBookingProject', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/booking-project/4/');
    }

    // ── createBookingProject ──────────────────────────────────────────────────

    public function testCreateBookingProjectUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createBookingProject', ['token' => 'tok', 'name' => 'Summer Event']);
        $this->assertPostTo('/booking-project/');
        $this->assertBodyContains('name', 'Summer Event');
    }

    // ── updateBookingProject ──────────────────────────────────────────────────

    public function testUpdateBookingProjectUsesPatch(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('updateBookingProject', ['token' => 'tok', 'id' => 4, 'completed' => true]);
        $this->assertPatchTo('/booking-project/4/');
        $this->assertBodyContains('completed', true);
    }

    // ── deleteBookingProject ──────────────────────────────────────────────────

    public function testDeleteBookingProjectReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteBookingProject', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    // ── listBillingAccounts ───────────────────────────────────────────────────

    public function testListBillingAccountsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listBillingAccounts', ['token' => 'tok']);
        $this->assertGetTo('/billing-account/');
    }

    // ── getBillingAccount ─────────────────────────────────────────────────────

    public function testGetBillingAccountUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getBillingAccount', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/billing-account/7/');
    }

    // ── createBillingAccount ──────────────────────────────────────────────────

    public function testCreateBillingAccountUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createBillingAccount', ['token' => 'tok', 'name' => 'Kasse']);
        $this->assertPostTo('/billing-account/');
        $this->assertBodyContains('name', 'Kasse');
    }

    // ── updateBillingAccount ──────────────────────────────────────────────────

    public function testUpdateBillingAccountUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateBillingAccount', ['token' => 'tok', 'id' => 7, 'name' => 'Hauptkasse']);
        $this->assertPatchTo('/billing-account/7/');
        $this->assertBodyContains('name', 'Hauptkasse');
    }

    // ── deleteBillingAccount ──────────────────────────────────────────────────

    public function testDeleteBillingAccountReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteBillingAccount', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── listCustomTaxRates ────────────────────────────────────────────────────

    public function testListCustomTaxRatesUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listCustomTaxRates', ['token' => 'tok']);
        $this->assertGetTo('/custom-tax-rate/');
    }

    // ── getCustomTaxRate ──────────────────────────────────────────────────────

    public function testGetCustomTaxRateUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getCustomTaxRate', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/custom-tax-rate/2/');
    }

    // ── createCustomTaxRate ───────────────────────────────────────────────────

    public function testCreateCustomTaxRateUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createCustomTaxRate', ['token' => 'tok', 'tax_name' => 'Reduced', 'custom_tax_rate' => 7.0]);
        $this->assertPostTo('/custom-tax-rate/');
        $this->assertBodyContains('tax_name', 'Reduced');
    }

    // ── updateCustomTaxRate ───────────────────────────────────────────────────

    public function testUpdateCustomTaxRateUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updateCustomTaxRate', ['token' => 'tok', 'id' => 2, 'custom_tax_rate' => 19.5]);
        $this->assertPatchTo('/custom-tax-rate/2/');
        $this->assertBodyContains('custom_tax_rate', 19.5);
    }

    // ── deleteCustomTaxRate ───────────────────────────────────────────────────

    public function testDeleteCustomTaxRateReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteCustomTaxRate', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── listDebitOrders ───────────────────────────────────────────────────────

    public function testListDebitOrdersUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listDebitOrders', ['token' => 'tok']);
        $this->assertGetTo('/debit-order/');
    }

    // ── getDebitOrder ─────────────────────────────────────────────────────────

    public function testGetDebitOrderUsesCorrectPath(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('getDebitOrder', ['token' => 'tok', 'id' => 8]);
        $this->assertGetTo('/debit-order/8/');
    }

    // ── createDebitOrder ──────────────────────────────────────────────────────

    public function testCreateDebitOrderUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createDebitOrder', ['token' => 'tok', 'member' => 5, 'amount' => 25.0]);
        $this->assertPostTo('/debit-order/');
        $this->assertBodyContains('member', 5);
    }

    // ── updateDebitOrder ──────────────────────────────────────────────────────

    public function testUpdateDebitOrderUsesPatch(): void
    {
        $this->addOk('{"id":8}');
        $this->tools->dispatch('updateDebitOrder', ['token' => 'tok', 'id' => 8, 'amount' => 30.5]);
        $this->assertPatchTo('/debit-order/8/');
        $this->assertBodyContains('amount', 30.5);
    }

    // ── deleteDebitOrder ──────────────────────────────────────────────────────

    public function testDeleteDebitOrderReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteDebitOrder', ['token' => 'tok', 'id' => 8]);
        $this->assertDeletedMessage($result);
    }

    // ── listPaymentMethods ────────────────────────────────────────────────────

    public function testListPaymentMethodsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPaymentMethods', ['token' => 'tok']);
        $this->assertGetTo('/payment-method/');
    }

    // ── getPaymentMethod ──────────────────────────────────────────────────────

    public function testGetPaymentMethodUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getPaymentMethod', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/payment-method/3/');
    }

    // ── createPaymentMethod ───────────────────────────────────────────────────

    public function testCreatePaymentMethodUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createPaymentMethod', ['token' => 'tok', 'name' => 'Direct Debit']);
        $this->assertPostTo('/payment-method/');
        $this->assertBodyContains('name', 'Direct Debit');
    }

    // ── updatePaymentMethod ───────────────────────────────────────────────────

    public function testUpdatePaymentMethodUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updatePaymentMethod', ['token' => 'tok', 'id' => 3, 'name' => 'Bank Transfer']);
        $this->assertPatchTo('/payment-method/3/');
        $this->assertBodyContains('name', 'Bank Transfer');
    }

    // ── deletePaymentMethod ───────────────────────────────────────────────────

    public function testDeletePaymentMethodReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePaymentMethod', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── listParticipationPriceGroups ──────────────────────────────────────────

    public function testListParticipationPriceGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listParticipationPriceGroups', ['token' => 'tok']);
        $this->assertGetTo('/participation-price-group/');
    }

    // ── getParticipationPriceGroup ────────────────────────────────────────────

    public function testGetParticipationPriceGroupUsesCorrectPath(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('getParticipationPriceGroup', ['token' => 'tok', 'id' => 9]);
        $this->assertGetTo('/participation-price-group/9/');
    }

    // ── createParticipationPriceGroup ─────────────────────────────────────────

    public function testCreateParticipationPriceGroupUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createParticipationPriceGroup', ['token' => 'tok', 'participation' => 5, 'price_group' => 3]);
        $this->assertPostTo('/participation-price-group/');
        $this->assertBodyContains('participation', 5);
    }

    // ── updateParticipationPriceGroup ─────────────────────────────────────────

    public function testUpdateParticipationPriceGroupUsesPatch(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('updateParticipationPriceGroup', ['token' => 'tok', 'id' => 9, 'pieces' => 5.5]);
        $this->assertPatchTo('/participation-price-group/9/');
        $this->assertBodyContains('pieces', 5.5);
    }

    // ── deleteParticipationPriceGroup ─────────────────────────────────────────

    public function testDeleteParticipationPriceGroupReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteParticipationPriceGroup', ['token' => 'tok', 'id' => 9]);
        $this->assertDeletedMessage($result);
    }

    // ── cancellation ──────────────────────────────────────────────────────────

    public function testCancellationUsesPost(): void
    {
        $this->addOk('{"status":"ok"}', 200);
        $this->tools->dispatch('cancellation', ['token' => 'tok', 'member' => 7, 'date' => '2025-12-31']);
        $this->assertPostTo('/cancellation/');
        $this->assertBodyContains('member', 7);
        $this->assertBodyContains('date', '2025-12-31');
    }

    // ── checkDiscountCode ─────────────────────────────────────────────────────

    public function testCheckDiscountCodeUsesPost(): void
    {
        $this->addOk('{"valid":true}', 200);
        $this->tools->dispatch('checkDiscountCode', ['token' => 'tok', 'discount_code' => 'SAVE10', 'event' => 1]);
        $this->assertPostTo('/check-discount-code/');
        $this->assertBodyContains('discount_code', 'SAVE10');
    }
}
