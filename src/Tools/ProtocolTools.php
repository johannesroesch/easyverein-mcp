<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

class ProtocolTools extends AbstractTools
{
    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id = ['id' => ['type' => 'integer', 'description' => 'Record ID']];

        $protocolFields = [
            'location_object'           => ['type' => 'integer', 'description' => 'Location ID'],
            'allowed_groups'            => ['type' => 'array',   'description' => 'Allowed group IDs (array)'],
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
            'visible'                   => ['type' => 'boolean', 'description' => 'Visibility flag'],
            'meeting_leader'            => ['type' => 'string',  'description' => 'Meeting leader name'],
            'meeting_secretary'         => ['type' => 'string',  'description' => 'Meeting secretary name'],
            'is_locked'                 => ['type' => 'boolean', 'description' => 'Whether the protocol is locked'],
        ];

        $protocolElementFields = [
            'title'              => ['type' => 'string',  'description' => 'Title'],
            'text'               => ['type' => 'string',  'description' => 'Text content'],
            'state'              => ['type' => 'string',  'description' => 'State / status'],
            'order'              => ['type' => 'integer', 'description' => 'Sort order'],
            'visible_for_export' => ['type' => 'integer', 'description' => 'Zahlenwert'],
        ];

        $protocolElementCommentFields = [
            'title' => ['type' => 'string', 'description' => 'Title'],
            'text'  => ['type' => 'string', 'description' => 'Text content'],
        ];

        $protocolUploadFields = [
            'protocol'             => ['type' => 'integer', 'description' => 'Protocol ID'],
            'protocol_file'        => ['type' => 'string',  'description' => 'File (base64 or URL)'],
            'is_public_accessible' => ['type' => 'boolean', 'description' => 'Whether publicly accessible'],
            'name'                 => ['type' => 'string',  'description' => 'Name'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listProtocols',               'uri' => 'easyverein://protocol/{?limit,page}',                     'description' => 'List all protocols.',                    'required' => [],     'props' => $pagination],
            ['name' => 'getProtocol',                 'uri' => 'easyverein://protocol/{id}',                               'description' => 'Get a protocol by ID.',                  'required' => ['id'], 'props' => $id],
            ['name' => 'listProtocolElements',        'uri' => 'easyverein://protocol-element/{?limit,page}',             'description' => 'List all protocol elements.',            'required' => [],     'props' => $pagination],
            ['name' => 'getProtocolElement',          'uri' => 'easyverein://protocol-element/{id}',                       'description' => 'Get a protocol element by ID.',          'required' => ['id'], 'props' => $id],
            ['name' => 'listProtocolElementComments', 'uri' => 'easyverein://protocol-element-comment/{?limit,page}',     'description' => 'List all protocol element comments.',    'required' => [],     'props' => $pagination],
            ['name' => 'getProtocolElementComment',   'uri' => 'easyverein://protocol-element-comment/{id}',              'description' => 'Get a protocol element comment by ID.', 'required' => ['id'], 'props' => $id],
            ['name' => 'listProtocolUploads',         'uri' => 'easyverein://protocol-upload/{?limit,page}',              'description' => 'List all protocol uploads.',             'required' => [],     'props' => $pagination],
            ['name' => 'getProtocolUpload',           'uri' => 'easyverein://protocol-upload/{id}',                        'description' => 'Get a protocol upload by ID.',           'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createProtocol',               'description' => 'Create a new protocol.',           'required' => [],     'props' => $protocolFields],
            ['name' => 'updateProtocol',               'description' => 'Update a protocol.',               'required' => ['id'], 'props' => $id + $protocolFields],
            ['name' => 'deleteProtocol',               'description' => 'Delete a protocol.',               'required' => ['id'], 'props' => $id],
            ['name' => 'createProtocolElement',        'description' => 'Create a new protocol element.',   'required' => [],     'props' => $protocolElementFields],
            ['name' => 'updateProtocolElement',        'description' => 'Update a protocol element.',       'required' => ['id'], 'props' => $id + $protocolElementFields],
            ['name' => 'deleteProtocolElement',        'description' => 'Delete a protocol element.',       'required' => ['id'], 'props' => $id],
            ['name' => 'createProtocolElementComment', 'description' => 'Create a protocol element comment.', 'required' => [],   'props' => $protocolElementCommentFields],
            ['name' => 'updateProtocolElementComment', 'description' => 'Update a protocol element comment.', 'required' => ['id'], 'props' => $id + $protocolElementCommentFields],
            ['name' => 'deleteProtocolElementComment', 'description' => 'Delete a protocol element comment.', 'required' => ['id'], 'props' => $id],
            ['name' => 'createProtocolUpload',         'description' => 'Upload a file to a protocol.',     'required' => [],     'props' => $protocolUploadFields],
            ['name' => 'deleteProtocolUpload',         'description' => 'Delete a protocol upload.',        'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listProtocols'               => $this->client->get($p['token'], '/protocol/', $this->pagination($p)),
            'getProtocol'                 => $this->client->get($p['token'], '/protocol/' . $p['id'] . '/'),
            'createProtocol'              => $this->client->post($p['token'], '/protocol/', $this->bodyFrom($p, ['location_object', 'allowed_groups', 'name', 'location_name', 'description', 'prologue', 'min_participators', 'max_participators', 'start_participation', 'end_participation', 'access', 'note', 'start', 'end', 'all_day', 'weekdays', 'confirmation_to_addresses', 'send_mail_check', 'show_memberarea', 'is_public', 'mass_participations', 'visible', 'meeting_leader', 'meeting_secretary', 'is_locked'])),
            'updateProtocol'              => $this->client->patch($p['token'], '/protocol/' . $p['id'] . '/', $this->bodyFrom($p, ['location_object', 'allowed_groups', 'name', 'location_name', 'description', 'prologue', 'min_participators', 'max_participators', 'start_participation', 'end_participation', 'access', 'note', 'start', 'end', 'all_day', 'weekdays', 'confirmation_to_addresses', 'send_mail_check', 'show_memberarea', 'is_public', 'mass_participations', 'visible', 'meeting_leader', 'meeting_secretary', 'is_locked'])),
            'deleteProtocol'              => $this->deleted($p['token'], '/protocol/' . $p['id'] . '/', 'Protocol'),
            'listProtocolElements'        => $this->client->get($p['token'], '/protocol-element/', $this->pagination($p)),
            'getProtocolElement'          => $this->client->get($p['token'], '/protocol-element/' . $p['id'] . '/'),
            'createProtocolElement'       => $this->client->post($p['token'], '/protocol-element/', $this->bodyFrom($p, ['title', 'text', 'state', 'order', 'visible_for_export'])),
            'updateProtocolElement'       => $this->client->patch($p['token'], '/protocol-element/' . $p['id'] . '/', $this->bodyFrom($p, ['title', 'text', 'state', 'order', 'visible_for_export'])),
            'deleteProtocolElement'       => $this->deleted($p['token'], '/protocol-element/' . $p['id'] . '/', 'ProtocolElement'),
            'listProtocolElementComments' => $this->client->get($p['token'], '/protocol-element-comment/', $this->pagination($p)),
            'getProtocolElementComment'   => $this->client->get($p['token'], '/protocol-element-comment/' . $p['id'] . '/'),
            'createProtocolElementComment' => $this->client->post($p['token'], '/protocol-element-comment/', $this->bodyFrom($p, ['title', 'text'])),
            'updateProtocolElementComment' => $this->client->patch($p['token'], '/protocol-element-comment/' . $p['id'] . '/', $this->bodyFrom($p, ['title', 'text'])),
            'deleteProtocolElementComment' => $this->deleted($p['token'], '/protocol-element-comment/' . $p['id'] . '/', 'ProtocolElementComment'),
            'listProtocolUploads'         => $this->client->get($p['token'], '/protocol-upload/', $this->pagination($p)),
            'getProtocolUpload'           => $this->client->get($p['token'], '/protocol-upload/' . $p['id'] . '/'),
            'createProtocolUpload'        => $this->client->post($p['token'], '/protocol-upload/', $this->bodyFrom($p, ['protocol', 'protocol_file', 'is_public_accessible', 'name'])),
            'deleteProtocolUpload'        => $this->deleted($p['token'], '/protocol-upload/' . $p['id'] . '/', 'ProtocolUpload'),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }
}
