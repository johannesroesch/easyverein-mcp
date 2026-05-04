<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

use EasyVerein\Mcp\ApiClient;

class EventTools
{
    public function __construct(private readonly ApiClient $client) {}

    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id   = ['id'   => ['type' => 'integer', 'description' => 'Record ID']];

        $eventFields = [
            'location_object'           => ['type' => 'integer', 'description' => 'Location ID'],
            'parent'                    => ['type' => 'integer', 'description' => 'Parent record ID'],
            'name'                      => ['type' => 'string',  'description' => 'Name'],
            'location_name'             => ['type' => 'string',  'description' => 'Location name (if no location object)'],
            'description'               => ['type' => 'string',  'description' => 'Description'],
            'prologue'                  => ['type' => 'string',  'description' => 'Zeichenwert'],
            'min_participators'         => ['type' => 'integer', 'description' => 'Minimum participants'],
            'max_participators'         => ['type' => 'integer', 'description' => 'Maximum participants'],
            'start_participation'       => ['type' => 'string',  'description' => 'Registration opens at (datetime)'],
            'end_participation'         => ['type' => 'string',  'description' => 'Registration closes at (datetime)'],
            'access'                    => ['type' => 'integer', 'description' => 'Access level'],
            'note'                      => ['type' => 'string',  'description' => 'Zeichenwert'],
            'start'                     => ['type' => 'string',  'description' => 'Start datetime'],
            'end'                       => ['type' => 'string',  'description' => 'End datetime'],
            'all_day'                   => ['type' => 'boolean', 'description' => 'All-day event flag'],
            'weekdays'                  => ['type' => 'string',  'description' => 'Zeichenwert'],
            'confirmation_to_addresses' => ['type' => 'array',   'description' => 'Referenzewert'],
            'send_mail_check'           => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'show_memberarea'           => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'is_public'                 => ['type' => 'boolean', 'description' => 'Whether publicly visible'],
            'mass_participations'       => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'canceled'                  => ['type' => 'boolean', 'description' => 'Whether canceled'],
            'is_reservation'            => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'creator'                   => ['type' => 'integer', 'description' => 'Referenzewert'],
            'reservation_parent_event'  => ['type' => 'integer', 'description' => 'Referenzwert'],
        ];

        $participationFields = [
            'participation_address' => ['type' => 'integer', 'description' => 'Participant address ID'],
            'name'                  => ['type' => 'string',  'description' => 'Name'],
            'show_name'             => ['type' => 'boolean', 'description' => 'Display name for participation'],
            'state'                 => ['type' => 'integer', 'description' => 'State / status'],
            'description'           => ['type' => 'string',  'description' => 'Description'],
            'is_companion'          => ['type' => 'boolean', 'description' => 'Whether this is a companion registration'],
        ];

        $eventCustomFieldAssignmentFields = [
            'custom_field'     => ['type' => 'integer', 'description' => 'Custom field ID'],
            'value'            => ['type' => 'string',  'description' => 'Field value'],
            'selected_options' => ['type' => 'array',   'description' => 'Selected option IDs (array)'],
        ];

        $applicationFormFields = [
            'title'               => ['type' => 'string',  'description' => 'Title'],
            'public'              => ['type' => 'boolean', 'description' => 'Whether publicly accessible'],
            'hide_privacy_notice' => ['type' => 'boolean', 'description' => 'Whether to hide the privacy notice on the application form'],
            'show_in_select'      => ['type' => 'boolean', 'description' => 'Whether this form appears in selection lists (e.g. member signup)'],
            'language'            => ['type' => 'string',  'description' => 'Language code'],
            'formular_kind'       => ['type' => 'string',  'description' => 'Form type'],
        ];

        $applicationFormElementFields = [
            'application_form'       => ['type' => 'integer', 'description' => 'ID of the application form this element belongs to'],
            'allowed_member_groups'  => ['type' => 'array',   'description' => 'Member group IDs allowed to see/fill this element (empty = all)'],
            'delete_after_date'      => ['type' => 'string',  'description' => 'Date after which this element is automatically deleted (YYYY-MM-DD)'],
            'position'               => ['type' => 'integer', 'description' => 'Display position/order within the form'],
            'kind'                   => ['type' => 'string',  'description' => 'Element type: "headline", "text", "textarea", "checkbox", "select", "member_group", "separator", "custom_field"'],
            'content'                => ['type' => 'string',  'description' => 'Content or label text of the element (e.g. headline text)'],
            'required'               => ['type' => 'boolean', 'description' => 'Whether this field is mandatory'],
            'label'                  => ['type' => 'string',  'description' => 'Display label shown to the applicant'],
            'default_value'          => ['type' => 'string',  'description' => 'Pre-filled default value for the element'],
            'order'                  => ['type' => 'string',  'description' => 'Sort order for member group selection (e.g. "name")'],
            'max_member_group_count' => ['type' => 'integer', 'description' => 'Maximum number of member groups a user may select'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listEvents',        'uri' => 'easyverein://event/{?limit,page,search}',                   'description' => 'List all events.',                                'required' => [],     'props' => $pagination + ['search' => ['type' => 'string', 'description' => 'Search term']]],
            ['name' => 'getEvent',          'uri' => 'easyverein://event/{id}',                                     'description' => 'Get an event by ID.',                             'required' => ['id'], 'props' => $id],
            ['name' => 'listParticipations','uri' => 'easyverein://participation/{?limit,page,event,member}',     'description' => 'List participations. Filter by event or member.', 'required' => [],     'props' => $pagination + ['event' => ['type' => 'integer', 'description' => 'Filter by event ID'], 'member' => ['type' => 'integer', 'description' => 'Filter by member ID']]],
            ['name' => 'getParticipation',  'uri' => 'easyverein://participation/{id}',                            'description' => 'Get a participation by ID.',                       'required' => ['id'], 'props' => $id],

            // Resources (read-only) – continued
            ['name' => 'listEventCustomFieldAssignments', 'uri' => 'easyverein://event-custom-field-assignment/{?limit,page,event}', 'description' => 'List event custom field assignments.', 'required' => [], 'props' => $pagination + ['event' => ['type' => 'integer', 'description' => 'Filter by event ID']]],
            ['name' => 'getEventCustomFieldAssignment',   'uri' => 'easyverein://event-custom-field-assignment/{id}',               'description' => 'Get an event custom field assignment by ID.', 'required' => ['id'], 'props' => $id],
            ['name' => 'listApplicationForms',    'uri' => 'easyverein://application-form/{?limit,page}',         'description' => 'List all application forms.',         'required' => [], 'props' => $pagination],
            ['name' => 'getApplicationForm',      'uri' => 'easyverein://application-form/{id}',                   'description' => 'Get an application form by ID.',      'required' => ['id'], 'props' => $id],
            ['name' => 'listApplicationFormElements', 'uri' => 'easyverein://application-form-element/{?limit,page,application_form}', 'description' => 'List application form elements.', 'required' => [], 'props' => $pagination + ['application_form' => ['type' => 'integer', 'description' => 'Filter by application form ID']]],
            ['name' => 'getApplicationFormElement',   'uri' => 'easyverein://application-form-element/{id}',       'description' => 'Get an application form element by ID.', 'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createEvent',        'description' => 'Create a new event.',           'required' => [],         'props' => $eventFields],
            ['name' => 'updateEvent',        'description' => 'Update an event.',              'required' => ['id'],     'props' => $id + $eventFields],
            ['name' => 'deleteEvent',        'description' => 'Delete an event.',              'required' => ['id'],     'props' => $id],
            ['name' => 'createParticipation','description' => 'Register a member for event.',  'required' => [],         'props' => $participationFields],
            ['name' => 'updateParticipation','description' => 'Update a participation.',       'required' => ['id'],     'props' => $id + $participationFields],
            ['name' => 'deleteParticipation','description' => 'Delete a participation.',       'required' => ['id'],     'props' => $id],
            ['name' => 'createEventCustomFieldAssignment', 'description' => 'Create an event custom field assignment.', 'required' => [],     'props' => $eventCustomFieldAssignmentFields],
            ['name' => 'updateEventCustomFieldAssignment', 'description' => 'Update an event custom field assignment.', 'required' => ['id'], 'props' => $id + $eventCustomFieldAssignmentFields],
            ['name' => 'deleteEventCustomFieldAssignment', 'description' => 'Delete an event custom field assignment.', 'required' => ['id'], 'props' => $id],
            ['name' => 'createApplicationForm',    'description' => 'Create a new application form.',    'required' => [],     'props' => $applicationFormFields],
            ['name' => 'updateApplicationForm',    'description' => 'Update an application form.',       'required' => ['id'], 'props' => $id + $applicationFormFields],
            ['name' => 'deleteApplicationForm',    'description' => 'Delete an application form.',       'required' => ['id'], 'props' => $id],
            ['name' => 'createApplicationFormElement', 'description' => 'Create an application form element.', 'required' => [],     'props' => $applicationFormElementFields],
            ['name' => 'updateApplicationFormElement', 'description' => 'Update an application form element.', 'required' => ['id'], 'props' => $id + $applicationFormElementFields],
            ['name' => 'deleteApplicationFormElement', 'description' => 'Delete an application form element.', 'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listEvents'         => $this->client->get($p['token'], '/event/', $this->pagination($p) + $this->optional($p, 'search')),
            'getEvent'           => $this->client->get($p['token'], '/event/' . $p['id'] . '/'),
            'createEvent'        => $this->client->post($p['token'], '/event/', $this->bodyFrom($p, ['location_object', 'parent', 'name', 'location_name', 'description', 'prologue', 'min_participators', 'max_participators', 'start_participation', 'end_participation', 'access', 'note', 'start', 'end', 'all_day', 'weekdays', 'confirmation_to_addresses', 'send_mail_check', 'show_memberarea', 'is_public', 'mass_participations', 'canceled', 'is_reservation', 'creator', 'reservation_parent_event'])),
            'updateEvent'        => $this->client->patch($p['token'], '/event/' . $p['id'] . '/', $this->bodyFrom($p, ['location_object', 'parent', 'name', 'location_name', 'description', 'prologue', 'min_participators', 'max_participators', 'start_participation', 'end_participation', 'access', 'note', 'start', 'end', 'all_day', 'weekdays', 'confirmation_to_addresses', 'send_mail_check', 'show_memberarea', 'is_public', 'mass_participations', 'canceled', 'is_reservation', 'creator', 'reservation_parent_event'])),
            'deleteEvent'        => $this->deleted($p['token'], '/event/' . $p['id'] . '/', 'Event'),
            'listParticipations' => $this->client->get($p['token'], '/participation/', $this->pagination($p) + $this->optional($p, 'event') + $this->optional($p, 'member')),
            'getParticipation'   => $this->client->get($p['token'], '/participation/' . $p['id'] . '/'),
            'createParticipation'=> $this->client->post($p['token'], '/participation/', $this->bodyFrom($p, ['participation_address', 'name', 'show_name', 'state', 'description', 'is_companion'])),
            'updateParticipation'=> $this->client->patch($p['token'], '/participation/' . $p['id'] . '/', $this->bodyFrom($p, ['participation_address', 'name', 'show_name', 'state', 'description', 'is_companion'])),
            'deleteParticipation'=> $this->deleted($p['token'], '/participation/' . $p['id'] . '/', 'Participation'),
            'listEventCustomFieldAssignments' => $this->client->get($p['token'], '/event-custom-field-assignment/', $this->pagination($p) + $this->optional($p, 'event')),
            'getEventCustomFieldAssignment'   => $this->client->get($p['token'], '/event-custom-field-assignment/' . $p['id'] . '/'),
            'createEventCustomFieldAssignment'=> $this->client->post($p['token'], '/event-custom-field-assignment/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'updateEventCustomFieldAssignment'=> $this->client->patch($p['token'], '/event-custom-field-assignment/' . $p['id'] . '/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'deleteEventCustomFieldAssignment'=> $this->deleted($p['token'], '/event-custom-field-assignment/' . $p['id'] . '/', 'EventCustomFieldAssignment'),
            'listApplicationForms'    => $this->client->get($p['token'], '/application-form/', $this->pagination($p)),
            'getApplicationForm'      => $this->client->get($p['token'], '/application-form/' . $p['id'] . '/'),
            'createApplicationForm'   => $this->client->post($p['token'], '/application-form/', $this->bodyFrom($p, ['title', 'public', 'hide_privacy_notice', 'show_in_select', 'language', 'formular_kind'])),
            'updateApplicationForm'   => $this->client->patch($p['token'], '/application-form/' . $p['id'] . '/', $this->bodyFrom($p, ['title', 'public', 'hide_privacy_notice', 'show_in_select', 'language', 'formular_kind'])),
            'deleteApplicationForm'   => $this->deleted($p['token'], '/application-form/' . $p['id'] . '/', 'ApplicationForm'),
            'listApplicationFormElements'    => $this->client->get($p['token'], '/application-form-element/', $this->pagination($p) + $this->optional($p, 'application_form')),
            'getApplicationFormElement'      => $this->client->get($p['token'], '/application-form-element/' . $p['id'] . '/'),
            'createApplicationFormElement'   => $this->client->post($p['token'], '/application-form-element/', $this->bodyFrom($p, ['application_form', 'allowed_member_groups', 'delete_after_date', 'position', 'kind', 'content', 'required', 'label', 'default_value', 'order', 'max_member_group_count'], ['application_form' => '/application-form/'])),
            'updateApplicationFormElement'   => $this->client->patch($p['token'], '/application-form-element/' . $p['id'] . '/', $this->bodyFrom($p, ['application_form', 'allowed_member_groups', 'delete_after_date', 'position', 'kind', 'content', 'required', 'label', 'default_value', 'order', 'max_member_group_count'], ['application_form' => '/application-form/'])),
            'deleteApplicationFormElement'   => $this->deleted($p['token'], '/application-form-element/' . $p['id'] . '/', 'ApplicationFormElement'),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }

    // $urlFields maps field name → API path (e.g. 'application_form' => '/application-form/').
    // Integer values for those fields are converted to hyperlinked URLs before serialisation.
    private function bodyFrom(array $p, array $fields, array $urlFields = []): string
    {
        $body = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $p)) {
                $value = $p[$field];
                if (isset($urlFields[$field]) && is_int($value)) {
                    $value = $this->client->urlRef($urlFields[$field], $value);
                }
                $body[$field] = $value;
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
