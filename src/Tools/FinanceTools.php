<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

class FinanceTools extends AbstractTools
{
    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id   = ['id'   => ['type' => 'integer', 'description' => 'Record ID']];
        $body = ['body' => ['type' => 'string',  'description' => 'JSON body']];

        $bookingFields = [
            'amount'            => ['type' => 'number',  'description' => 'Amount'],
            'billing_account'   => ['type' => 'integer', 'description' => 'Billing account ID'],
            'description'       => ['type' => 'string',  'description' => 'Description'],
            'date'              => ['type' => 'string',  'description' => 'Date (YYYY-MM-DD)'],
            'receiver'          => ['type' => 'string',  'description' => 'Recipient name'],
            'billing_id'        => ['type' => 'string',  'description' => 'Billing reference ID'],
            'payment_difference' => ['type' => 'number',  'description' => 'Zahlenwert'],
            'counterpart_iban'  => ['type' => 'string',  'description' => 'Counterpart IBAN'],
            'counterpart_bic'   => ['type' => 'string',  'description' => 'Counterpart BIC'],
            'twingle_donation'  => ['type' => 'boolean', 'description' => 'Twingle donation flag'],
            'sphere'            => ['type' => 'integer', 'description' => 'SKR42 sphere'],
        ];

        $invoiceFields = [
            'related_bookings'                      => ['type' => 'array',   'description' => 'Referenzewert'],
            'related_address'                       => ['type' => 'integer', 'description' => 'Recipient address ID'],
            'payed_from_user'                       => ['type' => 'integer', 'description' => 'Referenzwert'],
            'approved_from_admin'                   => ['type' => 'integer', 'description' => 'Referenzwert'],
            'canceled_invoice'                      => ['type' => 'integer', 'description' => 'Referenzwert'],
            'bank_account'                          => ['type' => 'integer', 'description' => 'Referenzwert'],
            'gross'                                 => ['type' => 'boolean', 'description' => 'Whether amount is gross'],
            'cancellation_description'              => ['type' => 'string',  'description' => 'Zeichenwert'],
            'template_name'                         => ['type' => 'string',  'description' => 'Zeichenwert'],
            'date'                                  => ['type' => 'string',  'description' => 'Date (YYYY-MM-DD)'],
            'date_it_happend'                       => ['type' => 'string',  'description' => 'Datumswert'],
            'date_sent'                             => ['type' => 'string',  'description' => 'Datumswert'],
            'inv_number'                            => ['type' => 'string',  'description' => 'Invoice number'],
            'receiver'                              => ['type' => 'string',  'description' => 'Recipient name'],
            'description'                           => ['type' => 'string',  'description' => 'Description'],
            'total_price'                           => ['type' => 'number',  'description' => 'Total amount'],
            'kind'                                  => ['type' => 'string',  'description' => 'Invoice type'],
            'ref_number'                            => ['type' => 'string',  'description' => 'Zeichenwert'],
            'is_draft'                              => ['type' => 'boolean', 'description' => 'Whether this is a draft'],
            'is_template'                           => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'creation_date_for_recurring_invoices'  => ['type' => 'string',  'description' => 'Datumswert'],
            'recurring_invoices_interval'           => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'payment_information'                   => ['type' => 'string',  'description' => 'Zeichenwert'],
            'is_request'                            => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'tax_rate'                              => ['type' => 'number',  'description' => 'Tax rate (%)'],
            'tax_name'                              => ['type' => 'string',  'description' => 'Tax name'],
            'actual_call_state_name'                => ['type' => 'string',  'description' => 'Zeichenwert'],
            'call_state_delay_days'                 => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'accnumber'                             => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'guid'                                  => ['type' => 'string',  'description' => 'Zeichenwert'],
            'selection_acc'                         => ['type' => 'integer', 'description' => 'Referenzwert'],
            'remove_file_on_delete'                 => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'custom_payment_method'                 => ['type' => 'integer', 'description' => 'ID of a custom payment method defined in the organization'],
            'is_receipt'                            => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'mode'                                  => ['type' => 'string',  'description' => 'Modus Der Rechnung'],
            'offer_status'                          => ['type' => 'string',  'description' => 'Status Des Angebots'],
            'offer_valid_until'                     => ['type' => 'string',  'description' => 'Datumswert'],
            'offer_number'                          => ['type' => 'string',  'description' => 'Zahlenwert'],
            'related_offer'                         => ['type' => 'integer', 'description' => 'Referenzwert'],
            'closing_description'                   => ['type' => 'string',  'description' => 'Zeichenwert'],
            'use_address_balance'                   => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
        ];

        $invoiceItemFields = [
            'quantity'                          => ['type' => 'number',  'description' => 'Quantity'],
            'unit_price'                        => ['type' => 'number',  'description' => 'Unit price'],
            'title'                             => ['type' => 'string',  'description' => 'Title'],
            'description'                       => ['type' => 'string',  'description' => 'Description'],
            'tax_rate'                          => ['type' => 'number',  'description' => 'Tax rate (%)'],
            'gross'                             => ['type' => 'boolean', 'description' => 'Whether amount is gross'],
            'tax_name'                          => ['type' => 'string',  'description' => 'Tax name'],
            'billing_account'                   => ['type' => 'integer', 'description' => 'Billing account ID'],
            'sphere'                            => ['type' => 'integer', 'description' => 'SKR42 sphere'],
            'cost_centre'                       => ['type' => 'string',  'description' => 'Cost centre reference'],
            'deducted_existing_balance'         => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'electronic_total_price_with_tax'   => ['type' => 'number',  'description' => 'Zahlenwert'],
        ];

        $bankAccountFields = [
            'name'                          => ['type' => 'string',  'description' => 'Name'],
            'color'                         => ['type' => 'string',  'description' => 'Hex color (e.g. #FF0000)'],
            'short'                         => ['type' => 'string',  'description' => 'Short code (max 4 chars)'],
            'billing_account'               => ['type' => 'integer', 'description' => 'Billing account ID'],
            'account_holder'                => ['type' => 'string',  'description' => 'Account holder name'],
            'bank_name'                     => ['type' => 'string',  'description' => 'Bank name'],
            'iban'                          => ['type' => 'string',  'description' => 'IBAN'],
            'bic'                           => ['type' => 'string',  'description' => 'BIC/Swift'],
            'startsaldo'                    => ['type' => 'number',  'description' => 'Opening balance'],
            'import_saldo'                  => ['type' => 'number',  'description' => 'Balance at the time of the last bank import (read from import file)'],
            'sphere'                        => ['type' => 'integer', 'description' => 'SKR42 sphere'],
            'compute_startsaldo_on_import'  => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'last_imported_date'            => ['type' => 'string',  'description' => 'Datum'],
        ];

        $bookingProjectFields = [
            'name'                  => ['type' => 'string',  'description' => 'Name'],
            'color'                 => ['type' => 'string',  'description' => 'Hex color (e.g. #FF0000)'],
            'short'                 => ['type' => 'string',  'description' => 'Short code (max 4 chars)'],
            'budget'                => ['type' => 'number',  'description' => 'Budget amount'],
            'completed'             => ['type' => 'boolean', 'description' => 'Whether completed'],
            'project_cost_centre'   => ['type' => 'string',  'description' => 'Cost centre code for accounting/controlling purposes'],
        ];

        $billingAccountFields = [
            'name'           => ['type' => 'string',  'description' => 'Name'],
            'exclude_in_eur' => ['type' => 'boolean', 'description' => 'Exclude this billing account from EUR totals/reports'],
            'number'         => ['type' => 'integer', 'description' => 'Account number'],
            'default_sphere' => ['type' => 'integer', 'description' => 'Zahlenweret'],
        ];

        $customTaxRateFields = [
            'tax_name'        => ['type' => 'string', 'description' => 'Tax name'],
            'custom_tax_rate' => ['type' => 'number', 'description' => 'Tax rate value'],
        ];

        $paymentMethodFields = [
            'name'              => ['type' => 'string', 'description' => 'Name'],
            'terms_of_payment'  => ['type' => 'string', 'description' => 'Payment terms'],
            'link'              => ['type' => 'string', 'description' => 'URL link'],
            'delete_after_date' => ['type' => 'string', 'description' => 'Datumswert'],
            'deleted_by'        => ['type' => 'string', 'description' => 'Zeichenwert'],
        ];

        $participationPriceGroupFields = [
            'participation' => ['type' => 'integer', 'description' => 'Participation ID'],
            'price_group'   => ['type' => 'integer', 'description' => 'Price group ID'],
            'pieces'        => ['type' => 'number',  'description' => 'Quantity in stock'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listBookings',       'uri' => 'easyverein://booking/{?limit,page}',         'description' => 'List all bookings.',                     'required' => [],     'props' => $pagination],
            ['name' => 'getBooking',         'uri' => 'easyverein://booking/{id}',                   'description' => 'Get a booking by ID.',                    'required' => ['id'], 'props' => $id],
            ['name' => 'listInvoices',       'uri' => 'easyverein://invoice/{?limit,page}',         'description' => 'List all invoices.',                     'required' => [],     'props' => $pagination],
            ['name' => 'getInvoice',         'uri' => 'easyverein://invoice/{id}',                   'description' => 'Get an invoice by ID.',                   'required' => ['id'], 'props' => $id],
            ['name' => 'listInvoiceItems',   'uri' => 'easyverein://invoice-item/{?limit,page,invoice}', 'description' => 'List invoice items.',            'required' => [],     'props' => $pagination + ['invoice' => ['type' => 'integer', 'description' => 'Filter by invoice ID']]],
            ['name' => 'getInvoiceItem',     'uri' => 'easyverein://invoice-item/{id}',              'description' => 'Get an invoice item by ID.',              'required' => ['id'], 'props' => $id],
            ['name' => 'listBankAccounts',   'uri' => 'easyverein://bank-account/{?limit,page}',    'description' => 'List all bank accounts.',                'required' => [],     'props' => $pagination],
            ['name' => 'getBankAccount',     'uri' => 'easyverein://bank-account/{id}',              'description' => 'Get a bank account by ID.',               'required' => ['id'], 'props' => $id],
            ['name' => 'listBookingProjects',      'uri' => 'easyverein://booking-project/{?limit,page}',          'description' => 'List all booking projects.',             'required' => [],     'props' => $pagination],
            ['name' => 'getBookingProject',         'uri' => 'easyverein://booking-project/{id}',                    'description' => 'Get a booking project by ID.',            'required' => ['id'], 'props' => $id],
            ['name' => 'listBillingAccounts',       'uri' => 'easyverein://billing-account/{?limit,page}',          'description' => 'List all billing accounts.',             'required' => [],     'props' => $pagination],
            ['name' => 'getBillingAccount',         'uri' => 'easyverein://billing-account/{id}',                    'description' => 'Get a billing account by ID.',            'required' => ['id'], 'props' => $id],
            ['name' => 'listCustomTaxRates',        'uri' => 'easyverein://custom-tax-rate/{?limit,page}',          'description' => 'List all custom tax rates.',             'required' => [],     'props' => $pagination],
            ['name' => 'getCustomTaxRate',          'uri' => 'easyverein://custom-tax-rate/{id}',                    'description' => 'Get a custom tax rate by ID.',            'required' => ['id'], 'props' => $id],
            ['name' => 'listDebitOrders',           'uri' => 'easyverein://debit-order/{?limit,page}',              'description' => 'List all debit orders.',                 'required' => [],     'props' => $pagination],
            ['name' => 'getDebitOrder',             'uri' => 'easyverein://debit-order/{id}',                        'description' => 'Get a debit order by ID.',                'required' => ['id'], 'props' => $id],
            ['name' => 'listPaymentMethods',        'uri' => 'easyverein://payment-method/{?limit,page}',           'description' => 'List all payment methods.',              'required' => [],     'props' => $pagination],
            ['name' => 'getPaymentMethod',          'uri' => 'easyverein://payment-method/{id}',                     'description' => 'Get a payment method by ID.',             'required' => ['id'], 'props' => $id],
            ['name' => 'listParticipationPriceGroups', 'uri' => 'easyverein://participation-price-group/{?limit,page}', 'description' => 'List all participation price groups.', 'required' => [], 'props' => $pagination],
            ['name' => 'getParticipationPriceGroup',   'uri' => 'easyverein://participation-price-group/{id}',      'description' => 'Get a participation price group by ID.', 'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createBooking',       'description' => 'Create a new booking.',        'required' => [],     'props' => $bookingFields],
            ['name' => 'updateBooking',       'description' => 'Update a booking.',            'required' => ['id'], 'props' => $id + $bookingFields],
            ['name' => 'deleteBooking',       'description' => 'Delete a booking.',            'required' => ['id'], 'props' => $id],
            ['name' => 'createInvoice',       'description' => 'Create a new invoice.',        'required' => [],     'props' => $invoiceFields],
            ['name' => 'updateInvoice',       'description' => 'Update an invoice.',           'required' => ['id'], 'props' => $id + $invoiceFields],
            ['name' => 'deleteInvoice',       'description' => 'Delete an invoice.',           'required' => ['id'], 'props' => $id],
            ['name' => 'createInvoiceItem',   'description' => 'Create a new invoice item.',   'required' => [],     'props' => $invoiceItemFields],
            ['name' => 'updateInvoiceItem',   'description' => 'Update an invoice item.',      'required' => ['id'], 'props' => $id + $invoiceItemFields],
            ['name' => 'deleteInvoiceItem',   'description' => 'Delete an invoice item.',      'required' => ['id'], 'props' => $id],
            ['name' => 'createBankAccount',   'description' => 'Create a new bank account.',   'required' => [],     'props' => $bankAccountFields],
            ['name' => 'updateBankAccount',   'description' => 'Update a bank account.',       'required' => ['id'], 'props' => $id + $bankAccountFields],
            ['name' => 'deleteBankAccount',   'description' => 'Delete a bank account.',       'required' => ['id'], 'props' => $id],
            ['name' => 'createBookingProject',     'description' => 'Create a new booking project.',           'required' => [],     'props' => $bookingProjectFields],
            ['name' => 'updateBookingProject',     'description' => 'Update a booking project.',               'required' => ['id'], 'props' => $id + $bookingProjectFields],
            ['name' => 'deleteBookingProject',     'description' => 'Delete a booking project.',               'required' => ['id'], 'props' => $id],
            ['name' => 'createBillingAccount',     'description' => 'Create a new billing account.',           'required' => [],     'props' => $billingAccountFields],
            ['name' => 'updateBillingAccount',     'description' => 'Update a billing account.',               'required' => ['id'], 'props' => $id + $billingAccountFields],
            ['name' => 'deleteBillingAccount',     'description' => 'Delete a billing account.',               'required' => ['id'], 'props' => $id],
            ['name' => 'createCustomTaxRate',      'description' => 'Create a new custom tax rate.',           'required' => [],     'props' => $customTaxRateFields],
            ['name' => 'updateCustomTaxRate',      'description' => 'Update a custom tax rate.',               'required' => ['id'], 'props' => $id + $customTaxRateFields],
            ['name' => 'deleteCustomTaxRate',      'description' => 'Delete a custom tax rate.',               'required' => ['id'], 'props' => $id],
            ['name' => 'createDebitOrder',         'description' => 'Create a new debit order.',               'required' => [],     'props' => ['member' => ['type' => 'integer', 'description' => 'Member ID'], 'amount' => ['type' => 'number', 'description' => 'Debit amount'], 'debit_collection_date' => ['type' => 'string', 'description' => 'Debit collection date (YYYY-MM-DD)'], 'iban' => ['type' => 'string', 'description' => 'IBAN'], 'bic' => ['type' => 'string', 'description' => 'BIC'], 'account_holder' => ['type' => 'string', 'description' => 'Account holder name']]],
            ['name' => 'updateDebitOrder',         'description' => 'Update a debit order.',                   'required' => ['id'], 'props' => $id + ['member' => ['type' => 'integer', 'description' => 'Member ID'], 'amount' => ['type' => 'number', 'description' => 'Debit amount'], 'debit_collection_date' => ['type' => 'string', 'description' => 'Debit collection date (YYYY-MM-DD)'], 'iban' => ['type' => 'string', 'description' => 'IBAN'], 'bic' => ['type' => 'string', 'description' => 'BIC'], 'account_holder' => ['type' => 'string', 'description' => 'Account holder name']]],
            ['name' => 'deleteDebitOrder',         'description' => 'Delete a debit order.',                   'required' => ['id'],         'props' => $id],
            ['name' => 'createPaymentMethod',      'description' => 'Create a new payment method.',            'required' => [],     'props' => $paymentMethodFields],
            ['name' => 'updatePaymentMethod',      'description' => 'Update a payment method.',                'required' => ['id'], 'props' => $id + $paymentMethodFields],
            ['name' => 'deletePaymentMethod',      'description' => 'Delete a payment method.',                'required' => ['id'], 'props' => $id],
            ['name' => 'createParticipationPriceGroup', 'description' => 'Create a participation price group.','required' => [],     'props' => $participationPriceGroupFields],
            ['name' => 'updateParticipationPriceGroup', 'description' => 'Update a participation price group.','required' => ['id'], 'props' => $id + $participationPriceGroupFields],
            ['name' => 'deleteParticipationPriceGroup', 'description' => 'Delete a participation price group.','required' => ['id'], 'props' => $id],
            ['name' => 'cancellation',             'description' => 'Process a membership cancellation.',      'required' => [],     'props' => ['member' => ['type' => 'integer', 'description' => 'Member ID to cancel'], 'date' => ['type' => 'string', 'description' => 'Cancellation date (YYYY-MM-DD)']]],
            ['name' => 'checkDiscountCode',        'description' => 'Check validity of a discount code.',      'required' => [],     'props' => ['discount_code' => ['type' => 'string', 'description' => 'Discount code to validate'], 'event' => ['type' => 'integer', 'description' => 'Event ID']]],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listBookings'        => $this->client->get($p['token'], '/booking/', $this->pagination($p)),
            'getBooking'          => $this->client->get($p['token'], '/booking/' . $p['id'] . '/'),
            'createBooking'       => $this->client->post($p['token'], '/booking/', $this->bodyFrom($p, ['amount', 'billing_account', 'description', 'date', 'receiver', 'billing_id', 'payment_difference', 'counterpart_iban', 'counterpart_bic', 'twingle_donation', 'sphere'])),
            'updateBooking'       => $this->client->patch($p['token'], '/booking/' . $p['id'] . '/', $this->bodyFrom($p, ['amount', 'billing_account', 'description', 'date', 'receiver', 'billing_id', 'payment_difference', 'counterpart_iban', 'counterpart_bic', 'twingle_donation', 'sphere'])),
            'deleteBooking'       => $this->deleted($p['token'], '/booking/' . $p['id'] . '/', 'Booking'),
            'listInvoices'        => $this->client->get($p['token'], '/invoice/', $this->pagination($p)),
            'getInvoice'          => $this->client->get($p['token'], '/invoice/' . $p['id'] . '/'),
            'createInvoice'       => $this->client->post($p['token'], '/invoice/', $this->bodyFrom($p, ['related_bookings', 'related_address', 'payed_from_user', 'approved_from_admin', 'canceled_invoice', 'bank_account', 'gross', 'cancellation_description', 'template_name', 'date', 'date_it_happend', 'date_sent', 'inv_number', 'receiver', 'description', 'total_price', 'kind', 'ref_number', 'is_draft', 'is_template', 'creation_date_for_recurring_invoices', 'recurring_invoices_interval', 'payment_information', 'is_request', 'tax_rate', 'tax_name', 'actual_call_state_name', 'call_state_delay_days', 'accnumber', 'guid', 'selection_acc', 'remove_file_on_delete', 'custom_payment_method', 'is_receipt', 'mode', 'offer_status', 'offer_valid_until', 'offer_number', 'related_offer', 'closing_description', 'use_address_balance'])),
            'updateInvoice'       => $this->client->patch($p['token'], '/invoice/' . $p['id'] . '/', $this->bodyFrom($p, ['related_bookings', 'related_address', 'payed_from_user', 'approved_from_admin', 'canceled_invoice', 'bank_account', 'gross', 'cancellation_description', 'template_name', 'date', 'date_it_happend', 'date_sent', 'inv_number', 'receiver', 'description', 'total_price', 'kind', 'ref_number', 'is_draft', 'is_template', 'creation_date_for_recurring_invoices', 'recurring_invoices_interval', 'payment_information', 'is_request', 'tax_rate', 'tax_name', 'actual_call_state_name', 'call_state_delay_days', 'accnumber', 'guid', 'selection_acc', 'remove_file_on_delete', 'custom_payment_method', 'is_receipt', 'mode', 'offer_status', 'offer_valid_until', 'offer_number', 'related_offer', 'closing_description', 'use_address_balance'])),
            'deleteInvoice'       => $this->deleted($p['token'], '/invoice/' . $p['id'] . '/', 'Invoice'),
            'listInvoiceItems'    => $this->client->get($p['token'], '/invoice-item/', $this->pagination($p) + $this->optional($p, 'invoice')),
            'getInvoiceItem'      => $this->client->get($p['token'], '/invoice-item/' . $p['id'] . '/'),
            'createInvoiceItem'   => $this->client->post($p['token'], '/invoice-item/', $this->bodyFrom($p, ['quantity', 'unit_price', 'title', 'description', 'tax_rate', 'gross', 'tax_name', 'billing_account', 'sphere', 'cost_centre', 'deducted_existing_balance', 'electronic_total_price_with_tax'])),
            'updateInvoiceItem'   => $this->client->patch($p['token'], '/invoice-item/' . $p['id'] . '/', $this->bodyFrom($p, ['quantity', 'unit_price', 'title', 'description', 'tax_rate', 'gross', 'tax_name', 'billing_account', 'sphere', 'cost_centre', 'deducted_existing_balance', 'electronic_total_price_with_tax'])),
            'deleteInvoiceItem'   => $this->deleted($p['token'], '/invoice-item/' . $p['id'] . '/', 'InvoiceItem'),
            'listBankAccounts'    => $this->client->get($p['token'], '/bank-account/', $this->pagination($p)),
            'getBankAccount'      => $this->client->get($p['token'], '/bank-account/' . $p['id'] . '/'),
            'createBankAccount'   => $this->client->post($p['token'], '/bank-account/', $this->bodyFrom($p, ['name', 'color', 'short', 'billing_account', 'account_holder', 'bank_name', 'iban', 'bic', 'startsaldo', 'import_saldo', 'sphere', 'compute_startsaldo_on_import', 'last_imported_date'])),
            'updateBankAccount'   => $this->client->patch($p['token'], '/bank-account/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'color', 'short', 'billing_account', 'account_holder', 'bank_name', 'iban', 'bic', 'startsaldo', 'import_saldo', 'sphere', 'compute_startsaldo_on_import', 'last_imported_date'])),
            'deleteBankAccount'   => $this->deleted($p['token'], '/bank-account/' . $p['id'] . '/', 'BankAccount'),
            'listBookingProjects'          => $this->client->get($p['token'], '/booking-project/', $this->pagination($p)),
            'getBookingProject'            => $this->client->get($p['token'], '/booking-project/' . $p['id'] . '/'),
            'createBookingProject'         => $this->client->post($p['token'], '/booking-project/', $this->bodyFrom($p, ['name', 'color', 'short', 'budget', 'completed', 'project_cost_centre'])),
            'updateBookingProject'         => $this->client->patch($p['token'], '/booking-project/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'color', 'short', 'budget', 'completed', 'project_cost_centre'])),
            'deleteBookingProject'         => $this->deleted($p['token'], '/booking-project/' . $p['id'] . '/', 'BookingProject'),
            'listBillingAccounts'          => $this->client->get($p['token'], '/billing-account/', $this->pagination($p)),
            'getBillingAccount'            => $this->client->get($p['token'], '/billing-account/' . $p['id'] . '/'),
            'createBillingAccount'         => $this->client->post($p['token'], '/billing-account/', $this->bodyFrom($p, ['name', 'exclude_in_eur', 'number', 'default_sphere'])),
            'updateBillingAccount'         => $this->client->patch($p['token'], '/billing-account/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'exclude_in_eur', 'number', 'default_sphere'])),
            'deleteBillingAccount'         => $this->deleted($p['token'], '/billing-account/' . $p['id'] . '/', 'BillingAccount'),
            'listCustomTaxRates'           => $this->client->get($p['token'], '/custom-tax-rate/', $this->pagination($p)),
            'getCustomTaxRate'             => $this->client->get($p['token'], '/custom-tax-rate/' . $p['id'] . '/'),
            'createCustomTaxRate'          => $this->client->post($p['token'], '/custom-tax-rate/', $this->bodyFrom($p, ['tax_name', 'custom_tax_rate'])),
            'updateCustomTaxRate'          => $this->client->patch($p['token'], '/custom-tax-rate/' . $p['id'] . '/', $this->bodyFrom($p, ['tax_name', 'custom_tax_rate'])),
            'deleteCustomTaxRate'          => $this->deleted($p['token'], '/custom-tax-rate/' . $p['id'] . '/', 'CustomTaxRate'),
            'listDebitOrders'              => $this->client->get($p['token'], '/debit-order/', $this->pagination($p)),
            'getDebitOrder'                => $this->client->get($p['token'], '/debit-order/' . $p['id'] . '/'),
            'createDebitOrder'             => $this->client->post($p['token'], '/debit-order/', $this->bodyFrom($p, ['member', 'amount', 'debit_collection_date', 'iban', 'bic', 'account_holder'])),
            'updateDebitOrder'             => $this->client->patch($p['token'], '/debit-order/' . $p['id'] . '/', $this->bodyFrom($p, ['member', 'amount', 'debit_collection_date', 'iban', 'bic', 'account_holder'])),
            'deleteDebitOrder'             => $this->deleted($p['token'], '/debit-order/' . $p['id'] . '/', 'DebitOrder'),
            'listPaymentMethods'           => $this->client->get($p['token'], '/payment-method/', $this->pagination($p)),
            'getPaymentMethod'             => $this->client->get($p['token'], '/payment-method/' . $p['id'] . '/'),
            'createPaymentMethod'          => $this->client->post($p['token'], '/payment-method/', $this->bodyFrom($p, ['name', 'terms_of_payment', 'link', 'delete_after_date', 'deleted_by'])),
            'updatePaymentMethod'          => $this->client->patch($p['token'], '/payment-method/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'terms_of_payment', 'link', 'delete_after_date', 'deleted_by'])),
            'deletePaymentMethod'          => $this->deleted($p['token'], '/payment-method/' . $p['id'] . '/', 'PaymentMethod'),
            'listParticipationPriceGroups' => $this->client->get($p['token'], '/participation-price-group/', $this->pagination($p)),
            'getParticipationPriceGroup'   => $this->client->get($p['token'], '/participation-price-group/' . $p['id'] . '/'),
            'createParticipationPriceGroup' => $this->client->post($p['token'], '/participation-price-group/', $this->bodyFrom($p, ['participation', 'price_group', 'pieces'])),
            'updateParticipationPriceGroup' => $this->client->patch($p['token'], '/participation-price-group/' . $p['id'] . '/', $this->bodyFrom($p, ['participation', 'price_group', 'pieces'])),
            'deleteParticipationPriceGroup' => $this->deleted($p['token'], '/participation-price-group/' . $p['id'] . '/', 'ParticipationPriceGroup'),
            'cancellation'                 => $this->client->post($p['token'], '/cancellation/', $this->bodyFrom($p, ['member', 'date'])),
            'checkDiscountCode'            => $this->client->post($p['token'], '/check-discount-code/', $this->bodyFrom($p, ['discount_code', 'event'])),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }

}
