<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

use EasyVerein\Mcp\ApiClient;

class PasscreatorTools extends AbstractTools
{

    public function getDefinitions(): array
    {
        $pagination = [
            'limit' => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'  => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id = ['id' => ['type' => 'integer', 'description' => 'Record ID']];

        $passFields = [
            'name'            => ['type' => 'string',  'description' => 'Pass name'],
            'serial_number'   => ['type' => 'string',  'description' => 'Serial number'],
            'barcode_value'   => ['type' => 'string',  'description' => 'Barcode value'],
            'expiration_date' => ['type' => 'string',  'description' => 'Expiration date (YYYY-MM-DD)'],
            'pass_template'   => ['type' => 'integer', 'description' => 'Pass template ID'],
            'member'          => ['type' => 'integer', 'description' => 'Member ID'],
        ];

        $passFieldFields = [
            'field_name'          => ['type' => 'string',  'description' => 'Field name'],
            'value'               => ['type' => 'string',  'description' => 'Field value'],
            'pass_template'       => ['type' => 'integer', 'description' => 'Pass template ID'],
            'member_custom_field' => ['type' => 'integer', 'description' => 'Member custom field ID'],
        ];

        $passTemplateFields = [
            'name'              => ['type' => 'string',  'description' => 'Template name'],
            'description'       => ['type' => 'string',  'description' => 'Description'],
            'pass_type'         => ['type' => 'string',  'description' => 'Pass type (generic/coupon/eventTicket/boardingPass/storeCard)'],
            'organization_name' => ['type' => 'string',  'description' => 'Organization name'],
            'background_color'  => ['type' => 'string',  'description' => 'Background color (hex)'],
            'foreground_color'  => ['type' => 'string',  'description' => 'Foreground color (hex)'],
            'label_color'       => ['type' => 'string',  'description' => 'Label color (hex)'],
            'logo_text'         => ['type' => 'string',  'description' => 'Logo text'],
            'logo'              => ['type' => 'string',  'description' => 'Logo image (base64 or URL)'],
        ];

        $passcreatorIntegrationFields = [
            'passphrase'           => ['type' => 'string',  'description' => 'Passphrase'],
            'certificate'          => ['type' => 'string',  'description' => 'Certificate (base64)'],
            'team_identifier'      => ['type' => 'string',  'description' => 'Apple Team Identifier'],
            'pass_type_identifier' => ['type' => 'string',  'description' => 'Apple Pass Type Identifier'],
            'is_active'            => ['type' => 'boolean', 'description' => 'Whether active'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listPasses',                  'uri' => 'easyverein://pass/{?limit,page}',                  'description' => 'List all Passcreator passes.',               'required' => [],     'props' => $pagination],
            ['name' => 'getPass',                     'uri' => 'easyverein://pass/{id}',                            'description' => 'Get a Passcreator pass by ID.',               'required' => ['id'], 'props' => $id],
            ['name' => 'listPassFields',              'uri' => 'easyverein://pass-field/{?limit,page}',            'description' => 'List all Passcreator pass fields.',           'required' => [],     'props' => $pagination],
            ['name' => 'getPassField',                'uri' => 'easyverein://pass-field/{id}',                      'description' => 'Get a Passcreator pass field by ID.',         'required' => ['id'], 'props' => $id],
            ['name' => 'listPassTemplates',           'uri' => 'easyverein://pass-template/{?limit,page}',         'description' => 'List all Passcreator pass templates.',        'required' => [],     'props' => $pagination],
            ['name' => 'getPassTemplate',             'uri' => 'easyverein://pass-template/{id}',                   'description' => 'Get a Passcreator pass template by ID.',      'required' => ['id'], 'props' => $id],
            ['name' => 'getPasscreatorIntegration',   'uri' => 'easyverein://passcreator-integration',              'description' => 'Get Passcreator integration settings.',       'required' => [],     'props' => []],

            // Tools (mutating)
            ['name' => 'createPass',                  'description' => 'Create a new Passcreator pass.',           'required' => [],     'props' => $passFields],
            ['name' => 'updatePass',                  'description' => 'Update a Passcreator pass.',               'required' => ['id'], 'props' => $id + $passFields],
            ['name' => 'deletePass',                  'description' => 'Delete a Passcreator pass.',               'required' => ['id'], 'props' => $id],
            ['name' => 'createPassField',             'description' => 'Create a new Passcreator pass field.',     'required' => [],     'props' => $passFieldFields],
            ['name' => 'updatePassField',             'description' => 'Update a Passcreator pass field.',         'required' => ['id'], 'props' => $id + $passFieldFields],
            ['name' => 'deletePassField',             'description' => 'Delete a Passcreator pass field.',         'required' => ['id'], 'props' => $id],
            ['name' => 'createPassTemplate',          'description' => 'Create a new Passcreator pass template.',  'required' => [],     'props' => $passTemplateFields],
            ['name' => 'updatePassTemplate',          'description' => 'Update a Passcreator pass template.',      'required' => ['id'], 'props' => $id + $passTemplateFields],
            ['name' => 'deletePassTemplate',          'description' => 'Delete a Passcreator pass template.',      'required' => ['id'], 'props' => $id],
            ['name' => 'updatePasscreatorIntegration','description' => 'Update Passcreator integration settings.', 'required' => [],     'props' => $passcreatorIntegrationFields],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listPasses'                   => $this->client->get($p['token'], '/pass/', $this->pagination($p)),
            'getPass'                      => $this->client->get($p['token'], '/pass/' . $p['id'] . '/'),
            'createPass'                   => $this->client->post($p['token'], '/pass/', $this->bodyFrom($p, ['name', 'serial_number', 'barcode_value', 'expiration_date', 'pass_template', 'member'])),
            'updatePass'                   => $this->client->patch($p['token'], '/pass/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'serial_number', 'barcode_value', 'expiration_date', 'pass_template', 'member'])),
            'deletePass'                   => $this->deleted($p['token'], '/pass/' . $p['id'] . '/', 'Pass'),
            'listPassFields'               => $this->client->get($p['token'], '/pass-field/', $this->pagination($p)),
            'getPassField'                 => $this->client->get($p['token'], '/pass-field/' . $p['id'] . '/'),
            'createPassField'              => $this->client->post($p['token'], '/pass-field/', $this->bodyFrom($p, ['field_name', 'value', 'pass_template', 'member_custom_field'])),
            'updatePassField'              => $this->client->patch($p['token'], '/pass-field/' . $p['id'] . '/', $this->bodyFrom($p, ['field_name', 'value', 'pass_template', 'member_custom_field'])),
            'deletePassField'              => $this->deleted($p['token'], '/pass-field/' . $p['id'] . '/', 'PassField'),
            'listPassTemplates'            => $this->client->get($p['token'], '/pass-template/', $this->pagination($p)),
            'getPassTemplate'              => $this->client->get($p['token'], '/pass-template/' . $p['id'] . '/'),
            'createPassTemplate'           => $this->client->post($p['token'], '/pass-template/', $this->bodyFrom($p, ['name', 'description', 'pass_type', 'organization_name', 'background_color', 'foreground_color', 'label_color', 'logo_text', 'logo'])),
            'updatePassTemplate'           => $this->client->patch($p['token'], '/pass-template/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'description', 'pass_type', 'organization_name', 'background_color', 'foreground_color', 'label_color', 'logo_text', 'logo'])),
            'deletePassTemplate'           => $this->deleted($p['token'], '/pass-template/' . $p['id'] . '/', 'PassTemplate'),
            'getPasscreatorIntegration'    => $this->client->get($p['token'], '/passcreator-integration/'),
            'updatePasscreatorIntegration' => $this->client->patch($p['token'], '/passcreator-integration/', $this->bodyFrom($p, ['passphrase', 'certificate', 'team_identifier', 'pass_type_identifier', 'is_active'])),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }

}
