<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

use EasyVerein\Mcp\ApiClient;

class MemberTools
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

        $memberFields = [
            'join_date'                                    => ['type' => 'string',  'description' => 'Join date (YYYY-MM-DD)'],
            'resignation_date'                             => ['type' => 'string',  'description' => 'Datumswert'],
            'resignation_notice_date'                      => ['type' => 'string',  'description' => 'Datumswert'],
            'declaration_of_application'                   => ['type' => 'string',  'description' => 'Pfadwert'],
            'payment_start_date'                           => ['type' => 'string',  'description' => 'Payment start date'],
            'payment_amount'                               => ['type' => 'number',  'description' => 'Payment amount'],
            'payment_intervall_months'                     => ['type' => 'integer', 'description' => 'Payment interval in months'],
            'related_member'                               => ['type' => 'integer', 'description' => 'ID of a related/linked member (e.g. family member)'],
            'use_balance_for_membership_fee'               => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'bulletin_board_new_post_notification'         => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'integration_dosb_sport'                       => ['type' => 'array',   'description' => 'DOSB sport type codes for the member (array of strings)'],
            'integration_dosb_gender'                      => ['type' => 'string',  'description' => 'Zeichenwert'],
            'integration_lsb_sport'                        => ['type' => 'array',   'description' => 'LSB sport type codes for the member (array of strings)'],
            'integration_lsb_gender'                       => ['type' => 'string',  'description' => 'Zeichenwert'],
            'is_application'                               => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'membership_number'                            => ['type' => 'string',  'description' => 'Membership number'],
            'sepa_mandate_file'                            => ['type' => 'string',  'description' => 'Pfadwert'],
            'is_chairman'                                  => ['type' => 'boolean', 'description' => 'Whether this member is on the board'],
            'chairman_permission_group'                    => ['type' => 'integer', 'description' => 'Permission group ID assigned to board members (Vorstand)'],
            'profile_picture'                              => ['type' => 'string',  'description' => 'Pfadwert'],
            'contact_details'                              => ['type' => 'integer', 'description' => 'Contact details ID'],
            'email_or_user_name'                           => ['type' => 'string',  'description' => 'Email / username'],
            'editable_by_related_members'                  => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'require_password_change'                      => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'is_blocked'                                   => ['type' => 'boolean', 'description' => 'Whether the member is blocked'],
            'block_reason'                                 => ['type' => 'string',  'description' => 'Zeichenwert'],
            'show_warnings_and_notes_to_admins_in_profile' => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'is_matrix_searchable'                         => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'matrix_block_reason'                          => ['type' => 'string',  'description' => 'Zeichenwert'],
            'blocked_from_matrix'                          => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'matrix_communication_permission'              => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'use_matrix_group_settings'                    => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
        ];

        $memberGroupFields = [
            'name'                                          => ['type' => 'string',  'description' => 'Name'],
            'color'                                         => ['type' => 'string',  'description' => 'Hex color (e.g. #FF0000)'],
            'short'                                         => ['type' => 'string',  'description' => 'Short code (max 4 chars)'],
            'next_group'                                    => ['type' => 'integer', 'description' => 'Next group ID (for age-based transitions)'],
            'payment_amount'                                => ['type' => 'number',  'description' => 'Payment amount'],
            'assignment_delete_after_booking'               => ['type' => 'boolean', 'description' => 'Boolischer Wert'],
            'use_payment_formula'                           => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'payment_formula'                               => ['type' => 'string',  'description' => 'Zeichenwert'],
            'payment_interval'                              => ['type' => 'integer', 'description' => 'Payment interval in months'],
            'name_on_invoice'                               => ['type' => 'string',  'description' => 'Zeichenwert'],
            'description_on_invoice'                        => ['type' => 'string',  'description' => 'Zeichenwert'],
            'show_in_applicationform'                       => ['type' => 'boolean', 'description' => 'Show in application form'],
            'age_permission'                                => ['type' => 'integer', 'description' => 'Age permission setting'],
            'keep_membership_after_age_based_group_change'  => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'tax_rate'                                      => ['type' => 'number',  'description' => 'Tax rate (%)'],
            'cost_centre'                                   => ['type' => 'string',  'description' => 'Cost centre reference'],
            'user_shares'                                   => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_bookings'                                 => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_protocols'                                => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_members'                                  => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_members_groupaccess'                      => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_membership_cte'                           => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_edit'                                     => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_bank_data'                                => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_forum'                                    => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_board'                                    => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_board_links'                              => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_allow_ics_export'                         => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_invoice_request'                          => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_inventory'                                => ['type' => 'string',  'description' => 'Zeichenwert'],
            'user_group_account'                            => ['type' => 'integer', 'description' => 'Referenzwert'],
            'billing_account'                               => ['type' => 'integer', 'description' => 'Billing account ID'],
            'order_sequence'                                => ['type' => 'integer', 'description' => 'Order/sequence number'],
            'is_only_visible_to_admins'                     => ['type' => 'boolean', 'description' => 'Boolischer Wert'],
            'sphere'                                        => ['type' => 'integer', 'description' => 'SKR42 sphere'],
            'participations_per_week'                       => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'is_matrix_searchable'                          => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'matrix_communication_permission'               => ['type' => 'integer', 'description' => 'Zahlenwert'],
            'use_automated_matrix_chat'                     => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'matrix_moderator_ids'                          => ['type' => 'string',  'description' => 'Liste Von Mitglieder Ids, Die Als Moderatoren Für Den Gr...'],
        ];

        $memberGroupAssignmentFields = [
            'member'          => ['type' => 'integer', 'description' => 'Member ID'],
            'member_group'    => ['type' => 'integer', 'description' => 'Member group ID'],
            'payment_active'  => ['type' => 'boolean', 'description' => 'Whether payment is active for this assignment'],
            'start'           => ['type' => 'string',  'description' => 'Start datetime'],
            'end'             => ['type' => 'string',  'description' => 'End datetime'],
        ];

        $memberCustomFieldAssignmentFields = [
            'custom_field'     => ['type' => 'integer', 'description' => 'Custom field ID'],
            'value'            => ['type' => 'string',  'description' => 'Field value'],
            'selected_options' => ['type' => 'array',   'description' => 'Selected option IDs (array)'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listMembers',                             'uri' => 'easyverein://member/{?limit,page,search}',                                         'description' => 'List all members.',                                                     'required' => [],     'props' => $pagination + ['search' => ['type' => 'string', 'description' => 'Search term']]],
            ['name' => 'getMember',                               'uri' => 'easyverein://member/{id}',                                                           'description' => 'Get a member by ID.',                                                   'required' => ['id'], 'props' => $id],
            ['name' => 'listMemberGroups',                        'uri' => 'easyverein://member-group/{?limit,page}',                                          'description' => 'List all member groups.',                                               'required' => [],     'props' => $pagination],
            ['name' => 'getMemberGroup',                          'uri' => 'easyverein://member-group/{id}',                                                     'description' => 'Get a member group by ID.',                                             'required' => ['id'], 'props' => $id],
            ['name' => 'listMemberGroupAssignments',              'uri' => 'easyverein://member-group-assignment/{?limit,page,member,memberGroup}',            'description' => 'List member group assignments.',                                        'required' => [],     'props' => $pagination + ['member' => ['type' => 'integer', 'description' => 'Filter by member ID'], 'memberGroup' => ['type' => 'integer', 'description' => 'Filter by member group ID']]],
            ['name' => 'listMemberCustomFieldAssignments',        'uri' => 'easyverein://member-custom-field-assignment/{?limit,page,member}',                 'description' => 'List member custom field assignments.',                                 'required' => [],     'props' => $pagination + ['member' => ['type' => 'integer', 'description' => 'Filter by member ID']]],
            ['name' => 'getMemberCustomFieldAssignment',          'uri' => 'easyverein://member-custom-field-assignment/{id}',                                   'description' => 'Get a member custom field assignment by ID.',                           'required' => ['id'], 'props' => $id],
            ['name' => 'listMemberCustomFieldAssignmentChangeRequests', 'uri' => 'easyverein://member-custom-field-assignment-change-request/{?limit,page,member}', 'description' => 'List member custom field assignment change requests.',             'required' => [],     'props' => $pagination + ['member' => ['type' => 'integer', 'description' => 'Filter by member ID']]],
            ['name' => 'getMemberCustomFieldAssignmentChangeRequest',   'uri' => 'easyverein://member-custom-field-assignment-change-request/{id}',              'description' => 'Get a member custom field assignment change request by ID.',          'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createMember',                            'description' => 'Create a new member.',                                                       'required' => [],     'props' => $memberFields],
            ['name' => 'updateMember',                            'description' => 'Update a member.',                                                           'required' => ['id'], 'props' => $id + $memberFields],
            ['name' => 'deleteMember',                            'description' => 'Delete a member.',                                                           'required' => ['id'], 'props' => $id],
            ['name' => 'createMemberGroup',                       'description' => 'Create a new member group.',                                                 'required' => [],     'props' => $memberGroupFields],
            ['name' => 'updateMemberGroup',                       'description' => 'Update a member group.',                                                     'required' => ['id'], 'props' => $id + $memberGroupFields],
            ['name' => 'deleteMemberGroup',                       'description' => 'Delete a member group.',                                                     'required' => ['id'], 'props' => $id],
            ['name' => 'createMemberGroupAssignment',             'description' => 'Assign a member to a group.',                                                'required' => [],     'props' => $memberGroupAssignmentFields],
            ['name' => 'deleteMemberGroupAssignment',             'description' => 'Remove a member from a group.',                                              'required' => ['id'], 'props' => $id],
            ['name' => 'createMemberCustomFieldAssignment',       'description' => 'Create a member custom field assignment.',                                   'required' => [],     'props' => $memberCustomFieldAssignmentFields],
            ['name' => 'updateMemberCustomFieldAssignment',       'description' => 'Update a member custom field assignment.',                                   'required' => ['id'], 'props' => $id + $memberCustomFieldAssignmentFields],
            ['name' => 'deleteMemberCustomFieldAssignment',       'description' => 'Delete a member custom field assignment.',                                   'required' => ['id'], 'props' => $id],
            ['name' => 'createMemberCustomFieldAssignmentChangeRequest', 'description' => 'Create a member custom field assignment change request.',             'required' => [],     'props' => ['field_value' => ['type' => 'string', 'description' => 'Field value'], 'selected_options' => ['type' => 'array', 'description' => 'Selected option IDs (array)']]],
            ['name' => 'updateMemberCustomFieldAssignmentChangeRequest', 'description' => 'Update a member custom field assignment change request.',             'required' => ['id'], 'props' => $id + ['field_value' => ['type' => 'string', 'description' => 'Field value'], 'selected_options' => ['type' => 'array', 'description' => 'Selected option IDs (array)']]],
            ['name' => 'deleteMemberCustomFieldAssignmentChangeRequest', 'description' => 'Delete a member custom field assignment change request.',             'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listMembers'                 => $this->client->get($p['token'], '/member/', $this->pagination($p) + $this->optional($p, 'search')),
            'getMember'                   => $this->client->get($p['token'], '/member/' . $p['id'] . '/'),
            'createMember'                => $this->client->post($p['token'], '/member/', $this->bodyFrom($p, ['join_date', 'resignation_date', 'resignation_notice_date', 'declaration_of_application', 'payment_start_date', 'payment_amount', 'payment_intervall_months', 'related_member', 'use_balance_for_membership_fee', 'bulletin_board_new_post_notification', 'integration_dosb_sport', 'integration_dosb_gender', 'integration_lsb_sport', 'integration_lsb_gender', 'is_application', 'membership_number', 'sepa_mandate_file', 'is_chairman', 'chairman_permission_group', 'profile_picture', 'contact_details', 'email_or_user_name', 'editable_by_related_members', 'require_password_change', 'is_blocked', 'block_reason', 'show_warnings_and_notes_to_admins_in_profile', 'is_matrix_searchable', 'matrix_block_reason', 'blocked_from_matrix', 'matrix_communication_permission', 'use_matrix_group_settings'])),
            'updateMember'                => $this->client->patch($p['token'], '/member/' . $p['id'] . '/', $this->bodyFrom($p, ['join_date', 'resignation_date', 'resignation_notice_date', 'declaration_of_application', 'payment_start_date', 'payment_amount', 'payment_intervall_months', 'related_member', 'use_balance_for_membership_fee', 'bulletin_board_new_post_notification', 'integration_dosb_sport', 'integration_dosb_gender', 'integration_lsb_sport', 'integration_lsb_gender', 'is_application', 'membership_number', 'sepa_mandate_file', 'is_chairman', 'chairman_permission_group', 'profile_picture', 'contact_details', 'email_or_user_name', 'editable_by_related_members', 'require_password_change', 'is_blocked', 'block_reason', 'show_warnings_and_notes_to_admins_in_profile', 'is_matrix_searchable', 'matrix_block_reason', 'blocked_from_matrix', 'matrix_communication_permission', 'use_matrix_group_settings'])),
            'deleteMember'                => $this->deleted($p['token'], '/member/' . $p['id'] . '/', 'Member'),
            'listMemberGroups'            => $this->client->get($p['token'], '/member-group/', $this->pagination($p)),
            'getMemberGroup'              => $this->client->get($p['token'], '/member-group/' . $p['id'] . '/'),
            'createMemberGroup'           => $this->client->post($p['token'], '/member-group/', $this->bodyFrom($p, ['name', 'color', 'short', 'next_group', 'payment_amount', 'assignment_delete_after_booking', 'use_payment_formula', 'payment_formula', 'payment_interval', 'name_on_invoice', 'description_on_invoice', 'show_in_applicationform', 'age_permission', 'keep_membership_after_age_based_group_change', 'tax_rate', 'cost_centre', 'user_shares', 'user_bookings', 'user_protocols', 'user_members', 'user_members_groupaccess', 'user_membership_cte', 'user_edit', 'user_bank_data', 'user_forum', 'user_board', 'user_board_links', 'user_allow_ics_export', 'user_invoice_request', 'user_inventory', 'user_group_account', 'billing_account', 'order_sequence', 'is_only_visible_to_admins', 'sphere', 'participations_per_week', 'is_matrix_searchable', 'matrix_communication_permission', 'use_automated_matrix_chat', 'matrix_moderator_ids'])),
            'updateMemberGroup'           => $this->client->patch($p['token'], '/member-group/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'color', 'short', 'next_group', 'payment_amount', 'assignment_delete_after_booking', 'use_payment_formula', 'payment_formula', 'payment_interval', 'name_on_invoice', 'description_on_invoice', 'show_in_applicationform', 'age_permission', 'keep_membership_after_age_based_group_change', 'tax_rate', 'cost_centre', 'user_shares', 'user_bookings', 'user_protocols', 'user_members', 'user_members_groupaccess', 'user_membership_cte', 'user_edit', 'user_bank_data', 'user_forum', 'user_board', 'user_board_links', 'user_allow_ics_export', 'user_invoice_request', 'user_inventory', 'user_group_account', 'billing_account', 'order_sequence', 'is_only_visible_to_admins', 'sphere', 'participations_per_week', 'is_matrix_searchable', 'matrix_communication_permission', 'use_automated_matrix_chat', 'matrix_moderator_ids'])),
            'deleteMemberGroup'           => $this->deleted($p['token'], '/member-group/' . $p['id'] . '/', 'MemberGroup'),
            'listMemberGroupAssignments'  => $this->client->get($p['token'], '/member-group-assignment/', $this->pagination($p) + $this->optional($p, 'member') + $this->optional($p, 'memberGroup')),
            'createMemberGroupAssignment' => $this->client->post($p['token'], '/member-group-assignment/', $this->bodyFrom($p, ['member', 'member_group', 'payment_active', 'start', 'end'])),
            'deleteMemberGroupAssignment' => $this->deleted($p['token'], '/member-group-assignment/' . $p['id'] . '/', 'MemberGroupAssignment'),
            'listMemberCustomFieldAssignments'  => $this->client->get($p['token'], '/member-custom-field-assignment/', $this->pagination($p) + $this->optional($p, 'member')),
            'getMemberCustomFieldAssignment'    => $this->client->get($p['token'], '/member-custom-field-assignment/' . $p['id'] . '/'),
            'createMemberCustomFieldAssignment' => $this->client->post($p['token'], '/member-custom-field-assignment/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'updateMemberCustomFieldAssignment' => $this->client->patch($p['token'], '/member-custom-field-assignment/' . $p['id'] . '/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'deleteMemberCustomFieldAssignment' => $this->deleted($p['token'], '/member-custom-field-assignment/' . $p['id'] . '/', 'MemberCustomFieldAssignment'),
            'listMemberCustomFieldAssignmentChangeRequests'  => $this->client->get($p['token'], '/member-custom-field-assignment-change-request/', $this->pagination($p) + $this->optional($p, 'member')),
            'getMemberCustomFieldAssignmentChangeRequest'    => $this->client->get($p['token'], '/member-custom-field-assignment-change-request/' . $p['id'] . '/'),
            'createMemberCustomFieldAssignmentChangeRequest' => $this->client->post($p['token'], '/member-custom-field-assignment-change-request/', $this->bodyFrom($p, ['field_value', 'selected_options'])),
            'updateMemberCustomFieldAssignmentChangeRequest' => $this->client->patch($p['token'], '/member-custom-field-assignment-change-request/' . $p['id'] . '/', $this->bodyFrom($p, ['field_value', 'selected_options'])),
            'deleteMemberCustomFieldAssignmentChangeRequest' => $this->deleted($p['token'], '/member-custom-field-assignment-change-request/' . $p['id'] . '/', 'MemberCustomFieldAssignmentChangeRequest'),
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
