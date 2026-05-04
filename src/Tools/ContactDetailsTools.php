<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

use EasyVerein\Mcp\ApiClient;

class ContactDetailsTools
{
    public function __construct(private readonly ApiClient $client) {}

    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id   = ['id'   => ['type' => 'integer', 'description' => 'Record ID']];
        $body = ['body' => ['type' => 'string',  'description' => 'JSON body']];

        $contactDetailsFields = [
            'private_email'                    => ['type' => 'string',  'description' => 'Private email address'],
            'company_email'                    => ['type' => 'string',  'description' => 'Zeichenwert'],
            'is_company'                       => ['type' => 'boolean', 'description' => 'Whether this is a company address'],
            'method_of_payment_name'           => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'salutation'                       => ['type' => 'string',  'description' => 'Salutation'],
            'first_name'                       => ['type' => 'string',  'description' => 'First name'],
            'family_name'                      => ['type' => 'string',  'description' => 'Last name'],
            'name_affix'                       => ['type' => 'string',  'description' => 'Zeichenwert'],
            'date_of_birth'                    => ['type' => 'string',  'description' => 'Date of birth (YYYY-MM-DD)'],
            'preferred_email_field'            => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'preferred_communication_way'      => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'company_name'                     => ['type' => 'string',  'description' => 'Company name'],
            'invoice_company'                  => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'send_invoice_company_mail'        => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'company_email_invoice'            => ['type' => 'string',  'description' => 'Zeichenwert'],
            'address_company'                  => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'private_phone'                    => ['type' => 'string',  'description' => 'Private phone number'],
            'company_phone'                    => ['type' => 'string',  'description' => 'Company phone number'],
            'mobile_phone'                     => ['type' => 'string',  'description' => 'Mobile phone number'],
            'street'                           => ['type' => 'string',  'description' => 'Street + house number'],
            'city'                             => ['type' => 'string',  'description' => 'City'],
            'state'                            => ['type' => 'string',  'description' => 'State / status'],
            'zip'                              => ['type' => 'string',  'description' => 'Postal code'],
            'country'                          => ['type' => 'string',  'description' => 'Country'],
            'country_code'                     => ['type' => 'string',  'description' => 'Country code (ISO)'],
            'company_street'                   => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_city'                     => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_state'                    => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_zip'                      => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_country'                  => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_country_code'             => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_name_invoice'             => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_street_invoice'           => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_city_invoice'             => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_state_invoice'            => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_zip_invoice'              => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_country_invoice'          => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_country_code_invoice'     => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_address_suffix_invoice'   => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_phone_invoice'            => ['type' => 'string',  'description' => 'Zeichenwert'],
            'professional_role'                => ['type' => 'string',  'description' => 'Zeichenwert'],
            'balance'                          => ['type' => 'number',  'description' => 'Zahlenwert'],
            'iban'                             => ['type' => 'string',  'description' => 'IBAN'],
            'bic'                              => ['type' => 'string',  'description' => 'BIC/Swift'],
            'internal_note'                    => ['type' => 'string',  'description' => 'Zeichenwert'],
            'bank_account_owner'               => ['type' => 'string',  'description' => 'Zeichenwert'],
            'sepa_mandate'                     => ['type' => 'string',  'description' => 'Zeichenwert'],
            'sepa_date'                        => ['type' => 'string',  'description' => 'Datumswert'],
            'method_of_payment'                => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'datev_account_number'             => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'copied_from_parent_start_date'    => ['type' => 'string',  'description' => 'Start date from which this record was copied from the parent contact (YYYY-MM-DD)'],
            'copied_from_parent_end_date'      => ['type' => 'string',  'description' => 'End date until which this record was copied from the parent contact (YYYY-MM-DD)'],
            'copied_from_parent_end_date_action' => ['type' => 'string', 'description' => 'Action to perform when the copy period ends (e.g. delete, keep)'],
            'address_suffix'                   => ['type' => 'string',  'description' => 'Zeichenwert'],
            'company_address_suffix'           => ['type' => 'string',  'description' => 'Zeichenwert'],
            'custom_payment_method'            => ['type' => 'integer', 'description' => 'ID of a custom payment method defined in the organization'],
        ];

        $contactDetailsGroupFields = [
            'name'           => ['type' => 'string',  'description' => 'Name'],
            'color'          => ['type' => 'string',  'description' => 'Hex color (e.g. #FF0000)'],
            'short'          => ['type' => 'string',  'description' => 'Short code (max 4 chars)'],
            'order_sequence' => ['type' => 'integer', 'description' => 'Order/sequence number'],
        ];

        $contactDetailsChangeRequestFields = [
            'time_stamp'       => ['type' => 'string',  'description' => 'Datumswert'],
            'field_value'      => ['type' => 'string',  'description' => 'Zeichenfeld'],
            'field_name'       => ['type' => 'string',  'description' => 'Zeichenfeld'],
            'address'          => ['type' => 'integer', 'description' => 'Referenzwert'],
            'requesting_user'  => ['type' => 'integer', 'description' => 'Referenzwert'],
        ];

        $contactDetailsCustomFieldAssignmentFields = [
            'custom_field'     => ['type' => 'integer', 'description' => 'Custom field ID'],
            'value'            => ['type' => 'string',  'description' => 'Field value'],
            'selected_options' => ['type' => 'array',   'description' => 'Selected option IDs (array)'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listContactDetails',                         'uri' => 'easyverein://contact-details/{?limit,page,member}',                            'description' => 'List contact details.',                                              'required' => [],     'props' => $pagination + ['member' => ['type' => 'integer', 'description' => 'Filter by member ID']]],
            ['name' => 'getContactDetails',                          'uri' => 'easyverein://contact-details/{id}',                                              'description' => 'Get contact details by ID.',                                         'required' => ['id'], 'props' => $id],
            ['name' => 'listContactDetailGroups',                    'uri' => 'easyverein://contact-details-group/{?limit,page}',                             'description' => 'List contact detail groups.',                                        'required' => [],     'props' => $pagination],
            ['name' => 'listContactDetailsChangeRequests',           'uri' => 'easyverein://contact-details-change-request/{?limit,page,contactDetails}',     'description' => 'List contact details change requests.',                              'required' => [],     'props' => $pagination + ['contactDetails' => ['type' => 'integer', 'description' => 'Filter by contact details ID']]],
            ['name' => 'getContactDetailsChangeRequest',             'uri' => 'easyverein://contact-details-change-request/{id}',                               'description' => 'Get a contact details change request by ID.',                        'required' => ['id'], 'props' => $id],
            ['name' => 'listContactDetailsLogs',                     'uri' => 'easyverein://contact-details-log/{?limit,page,contactDetails}',                'description' => 'List contact details logs (read-only).',                             'required' => [],     'props' => $pagination + ['contactDetails' => ['type' => 'integer', 'description' => 'Filter by contact details ID']]],
            ['name' => 'getContactDetailsLog',                       'uri' => 'easyverein://contact-details-log/{id}',                                          'description' => 'Get a contact details log entry by ID.',                             'required' => ['id'], 'props' => $id],
            ['name' => 'listContactDetailsCustomFieldAssignments',   'uri' => 'easyverein://contact-details-custom-field-assignment/{?limit,page,contactDetails}', 'description' => 'List contact details custom field assignments.',              'required' => [],     'props' => $pagination + ['contactDetails' => ['type' => 'integer', 'description' => 'Filter by contact details ID']]],
            ['name' => 'getContactDetailsCustomFieldAssignment',     'uri' => 'easyverein://contact-details-custom-field-assignment/{id}',                      'description' => 'Get a contact details custom field assignment by ID.',               'required' => ['id'], 'props' => $id],
            ['name' => 'listFormerMemberData',                       'uri' => 'easyverein://former-member-data/{?limit,page}',                                'description' => 'List former member data (read-only).',                               'required' => [],     'props' => $pagination],
            ['name' => 'getFormerMemberData',                        'uri' => 'easyverein://former-member-data/{id}',                                           'description' => 'Get former member data by ID (read-only).',                          'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createContactDetails',                       'description' => 'Create contact details.',                                               'required' => [],     'props' => $contactDetailsFields],
            ['name' => 'updateContactDetails',                       'description' => 'Update contact details.',                                               'required' => ['id'], 'props' => $id + $contactDetailsFields],
            ['name' => 'deleteContactDetails',                       'description' => 'Delete contact details.',                                               'required' => ['id'], 'props' => $id],
            ['name' => 'createContactDetailsChangeRequest',          'description' => 'Create a contact details change request.',                              'required' => [],     'props' => $contactDetailsChangeRequestFields],
            ['name' => 'updateContactDetailsChangeRequest',          'description' => 'Update a contact details change request.',                              'required' => ['id'], 'props' => $id + $contactDetailsChangeRequestFields],
            ['name' => 'deleteContactDetailsChangeRequest',          'description' => 'Delete a contact details change request.',                              'required' => ['id'], 'props' => $id],
            ['name' => 'createContactDetailsCustomFieldAssignment',  'description' => 'Create a contact details custom field assignment.',                     'required' => [],     'props' => $contactDetailsCustomFieldAssignmentFields],
            ['name' => 'updateContactDetailsCustomFieldAssignment',  'description' => 'Update a contact details custom field assignment.',                     'required' => ['id'], 'props' => $id + $contactDetailsCustomFieldAssignmentFields],
            ['name' => 'deleteContactDetailsCustomFieldAssignment',  'description' => 'Delete a contact details custom field assignment.',                     'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listContactDetails'      => $this->client->get($p['token'], '/contact-details/', $this->pagination($p) + $this->optional($p, 'member')),
            'getContactDetails'       => $this->client->get($p['token'], '/contact-details/' . $p['id'] . '/'),
            'createContactDetails'    => $this->client->post($p['token'], '/contact-details/', $this->bodyFrom($p, ['private_email', 'company_email', 'is_company', 'method_of_payment_name', 'salutation', 'first_name', 'family_name', 'name_affix', 'date_of_birth', 'preferred_email_field', 'preferred_communication_way', 'company_name', 'invoice_company', 'send_invoice_company_mail', 'company_email_invoice', 'address_company', 'private_phone', 'company_phone', 'mobile_phone', 'street', 'city', 'state', 'zip', 'country', 'country_code', 'company_street', 'company_city', 'company_state', 'company_zip', 'company_country', 'company_country_code', 'company_name_invoice', 'company_street_invoice', 'company_city_invoice', 'company_state_invoice', 'company_zip_invoice', 'company_country_invoice', 'company_country_code_invoice', 'company_address_suffix_invoice', 'company_phone_invoice', 'professional_role', 'balance', 'iban', 'bic', 'internal_note', 'bank_account_owner', 'sepa_mandate', 'sepa_date', 'method_of_payment', 'datev_account_number', 'copied_from_parent_start_date', 'copied_from_parent_end_date', 'copied_from_parent_end_date_action', 'address_suffix', 'company_address_suffix', 'custom_payment_method'])),
            'updateContactDetails'    => $this->client->patch($p['token'], '/contact-details/' . $p['id'] . '/', $this->bodyFrom($p, ['private_email', 'company_email', 'is_company', 'method_of_payment_name', 'salutation', 'first_name', 'family_name', 'name_affix', 'date_of_birth', 'preferred_email_field', 'preferred_communication_way', 'company_name', 'invoice_company', 'send_invoice_company_mail', 'company_email_invoice', 'address_company', 'private_phone', 'company_phone', 'mobile_phone', 'street', 'city', 'state', 'zip', 'country', 'country_code', 'company_street', 'company_city', 'company_state', 'company_zip', 'company_country', 'company_country_code', 'company_name_invoice', 'company_street_invoice', 'company_city_invoice', 'company_state_invoice', 'company_zip_invoice', 'company_country_invoice', 'company_country_code_invoice', 'company_address_suffix_invoice', 'company_phone_invoice', 'professional_role', 'balance', 'iban', 'bic', 'internal_note', 'bank_account_owner', 'sepa_mandate', 'sepa_date', 'method_of_payment', 'datev_account_number', 'copied_from_parent_start_date', 'copied_from_parent_end_date', 'copied_from_parent_end_date_action', 'address_suffix', 'company_address_suffix', 'custom_payment_method'])),
            'deleteContactDetails'    => $this->deleted($p['token'], '/contact-details/' . $p['id'] . '/', 'ContactDetails'),
            'listContactDetailGroups' => $this->client->get($p['token'], '/contact-details-group/', $this->pagination($p)),
            'listContactDetailsChangeRequests'  => $this->client->get($p['token'], '/contact-details-change-request/', $this->pagination($p) + $this->optional($p, 'contactDetails')),
            'getContactDetailsChangeRequest'    => $this->client->get($p['token'], '/contact-details-change-request/' . $p['id'] . '/'),
            'createContactDetailsChangeRequest' => $this->client->post($p['token'], '/contact-details-change-request/', $this->bodyFrom($p, ['time_stamp', 'field_value', 'field_name', 'address', 'requesting_user'])),
            'updateContactDetailsChangeRequest' => $this->client->patch($p['token'], '/contact-details-change-request/' . $p['id'] . '/', $this->bodyFrom($p, ['time_stamp', 'field_value', 'field_name', 'address', 'requesting_user'])),
            'deleteContactDetailsChangeRequest' => $this->deleted($p['token'], '/contact-details-change-request/' . $p['id'] . '/', 'ContactDetailsChangeRequest'),
            'listContactDetailsLogs'  => $this->client->get($p['token'], '/contact-details-log/', $this->pagination($p) + $this->optional($p, 'contactDetails')),
            'getContactDetailsLog'    => $this->client->get($p['token'], '/contact-details-log/' . $p['id'] . '/'),
            'listContactDetailsCustomFieldAssignments'  => $this->client->get($p['token'], '/contact-details-custom-field-assignment/', $this->pagination($p) + $this->optional($p, 'contactDetails')),
            'getContactDetailsCustomFieldAssignment'    => $this->client->get($p['token'], '/contact-details-custom-field-assignment/' . $p['id'] . '/'),
            'createContactDetailsCustomFieldAssignment' => $this->client->post($p['token'], '/contact-details-custom-field-assignment/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'updateContactDetailsCustomFieldAssignment' => $this->client->patch($p['token'], '/contact-details-custom-field-assignment/' . $p['id'] . '/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'deleteContactDetailsCustomFieldAssignment' => $this->deleted($p['token'], '/contact-details-custom-field-assignment/' . $p['id'] . '/', 'ContactDetailsCustomFieldAssignment'),
            'listFormerMemberData' => $this->client->get($p['token'], '/former-member-data/', $this->pagination($p)),
            'getFormerMemberData'  => $this->client->get($p['token'], '/former-member-data/' . $p['id'] . '/'),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }

    private function bodyFrom(array $p, array $fields): string
    {
        $body = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $p)) {
                $body[$field] = $p[$field];
            }
        }
        return json_encode($body);
    }

    private function pagination(array $p): array
    {
        $q = [];
        if (isset($p['limit']))  $q['limit']  = $p['limit'];
        if (isset($p['page']))   $q['page']   = $p['page'];
        return $q;
    }

    private function optional(array $p, string $key): array
    {
        return isset($p[$key]) ? [$key => $p[$key]] : [];
    }

    private function deleted(string $token, string $path, string $label): string
    {
        $this->client->delete($token, $path);
        return json_encode(['message' => "$label deleted."]);
    }
}
