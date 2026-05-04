<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

class InventoryTools extends AbstractTools
{
    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id = ['id' => ['type' => 'integer', 'description' => 'Record ID']];

        $inventoryObjectFields = [
            'name'                => ['type' => 'string',  'description' => 'Name'],
            'identifier'          => ['type' => 'string',  'description' => 'Article/SKU number'],
            'picture'             => ['type' => 'string',  'description' => 'Pfadwert'],
            'description'         => ['type' => 'string',  'description' => 'Description'],
            'pieces'              => ['type' => 'integer', 'description' => 'Quantity in stock'],
            'price'               => ['type' => 'number',  'description' => 'Zahlenwert'],
            'purchase_date'       => ['type' => 'string',  'description' => 'Datumswert'],
            'location_name'       => ['type' => 'string',  'description' => 'Location name (if no location object)'],
            'lending_available'   => ['type' => 'boolean', 'description' => 'Boolscher Wert'],
            'lending_responsible' => ['type' => 'integer', 'description' => 'Referenzwert'],
        ];

        $inventoryObjectGroupFields = [
            'name'  => ['type' => 'string', 'description' => 'Name'],
            'color' => ['type' => 'string', 'description' => 'Hex color (e.g. #FF0000)'],
            'short' => ['type' => 'string', 'description' => 'Short code (max 4 chars)'],
        ];

        $inventoryObjectCustomFieldAssignmentFields = [
            'custom_field'     => ['type' => 'integer', 'description' => 'Custom field ID'],
            'value'            => ['type' => 'string',  'description' => 'Field value'],
            'selected_options' => ['type' => 'array',   'description' => 'Selected option IDs (array)'],
        ];

        $lendingFields = [
            'parent_inventory_object' => ['type' => 'integer', 'description' => 'Inventory object ID'],
            'borrow_address'          => ['type' => 'integer', 'description' => 'Borrower address ID'],
            'borrowing_date'          => ['type' => 'string',  'description' => 'Borrowing date'],
            'return_date'             => ['type' => 'string',  'description' => 'Return date'],
            'quantity'                => ['type' => 'integer', 'description' => 'Quantity'],
            'borrow_time'             => ['type' => 'string',  'description' => 'Zeitfeld'],
            'return_time'             => ['type' => 'string',  'description' => 'Zeitfeld'],
            'state'                   => ['type' => 'string',  'description' => 'State / status'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listInventoryObjects',                    'uri' => 'easyverein://inventory-object/{?limit,page}',                                                   'description' => 'List all inventory objects.',                            'required' => [],     'props' => $pagination],
            ['name' => 'getInventoryObject',                      'uri' => 'easyverein://inventory-object/{id}',                                                              'description' => 'Get an inventory object by ID.',                         'required' => ['id'], 'props' => $id],
            ['name' => 'listInventoryObjectGroups',               'uri' => 'easyverein://inventory-object-group/{?limit,page}',                                              'description' => 'List all inventory object groups.',                      'required' => [],     'props' => $pagination],
            ['name' => 'getInventoryObjectGroup',                 'uri' => 'easyverein://inventory-object-group/{id}',                                                        'description' => 'Get an inventory object group by ID.',                   'required' => ['id'], 'props' => $id],
            ['name' => 'listInventoryObjectCustomFieldAssignments', 'uri' => 'easyverein://inventory-object-custom-field-assignment/{?limit,page,inventory_object}',          'description' => 'List inventory object custom field assignments.',        'required' => [],     'props' => $pagination + ['inventory_object' => ['type' => 'integer', 'description' => 'Filter by inventory object ID']]],
            ['name' => 'getInventoryObjectCustomFieldAssignment',  'uri' => 'easyverein://inventory-object-custom-field-assignment/{id}',                                    'description' => 'Get an inventory object custom field assignment by ID.', 'required' => ['id'], 'props' => $id],
            ['name' => 'listLendings',                            'uri' => 'easyverein://lending/{?limit,page}',                                                             'description' => 'List all lendings.',                                     'required' => [],     'props' => $pagination],
            ['name' => 'getLending',                              'uri' => 'easyverein://lending/{id}',                                                                       'description' => 'Get a lending by ID.',                                   'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createInventoryObject',                    'description' => 'Create an inventory object.',                            'required' => [],     'props' => $inventoryObjectFields],
            ['name' => 'updateInventoryObject',                    'description' => 'Update an inventory object.',                            'required' => ['id'], 'props' => $id + $inventoryObjectFields],
            ['name' => 'deleteInventoryObject',                    'description' => 'Delete an inventory object.',                            'required' => ['id'], 'props' => $id],
            ['name' => 'createInventoryObjectGroup',               'description' => 'Create an inventory object group.',                      'required' => [],     'props' => $inventoryObjectGroupFields],
            ['name' => 'updateInventoryObjectGroup',               'description' => 'Update an inventory object group.',                      'required' => ['id'], 'props' => $id + $inventoryObjectGroupFields],
            ['name' => 'deleteInventoryObjectGroup',               'description' => 'Delete an inventory object group.',                      'required' => ['id'], 'props' => $id],
            ['name' => 'createInventoryObjectCustomFieldAssignment', 'description' => 'Create an inventory object custom field assignment.',   'required' => [],     'props' => $inventoryObjectCustomFieldAssignmentFields],
            ['name' => 'updateInventoryObjectCustomFieldAssignment', 'description' => 'Update an inventory object custom field assignment.',   'required' => ['id'], 'props' => $id + $inventoryObjectCustomFieldAssignmentFields],
            ['name' => 'deleteInventoryObjectCustomFieldAssignment', 'description' => 'Delete an inventory object custom field assignment.',   'required' => ['id'], 'props' => $id],
            ['name' => 'createLending',                            'description' => 'Create a lending.',                                      'required' => [],     'props' => $lendingFields],
            ['name' => 'updateLending',                            'description' => 'Update a lending.',                                      'required' => ['id'], 'props' => $id + $lendingFields],
            ['name' => 'deleteLending',                            'description' => 'Delete a lending.',                                      'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listInventoryObjects'                       => $this->client->get($p['token'], '/inventory-object/', $this->pagination($p)),
            'getInventoryObject'                         => $this->client->get($p['token'], '/inventory-object/' . $p['id'] . '/'),
            'createInventoryObject'                      => $this->client->post($p['token'], '/inventory-object/', $this->bodyFrom($p, ['name', 'identifier', 'picture', 'description', 'pieces', 'price', 'purchase_date', 'location_name', 'lending_available', 'lending_responsible'])),
            'updateInventoryObject'                      => $this->client->patch($p['token'], '/inventory-object/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'identifier', 'picture', 'description', 'pieces', 'price', 'purchase_date', 'location_name', 'lending_available', 'lending_responsible'])),
            'deleteInventoryObject'                      => $this->deleted($p['token'], '/inventory-object/' . $p['id'] . '/', 'InventoryObject'),
            'listInventoryObjectGroups'                  => $this->client->get($p['token'], '/inventory-object-group/', $this->pagination($p)),
            'getInventoryObjectGroup'                    => $this->client->get($p['token'], '/inventory-object-group/' . $p['id'] . '/'),
            'createInventoryObjectGroup'                 => $this->client->post($p['token'], '/inventory-object-group/', $this->bodyFrom($p, ['name', 'color', 'short'])),
            'updateInventoryObjectGroup'                 => $this->client->patch($p['token'], '/inventory-object-group/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'color', 'short'])),
            'deleteInventoryObjectGroup'                 => $this->deleted($p['token'], '/inventory-object-group/' . $p['id'] . '/', 'InventoryObjectGroup'),
            'listInventoryObjectCustomFieldAssignments'  => $this->client->get($p['token'], '/inventory-object-custom-field-assignment/', $this->pagination($p) + $this->optional($p, 'inventory_object')),
            'getInventoryObjectCustomFieldAssignment'    => $this->client->get($p['token'], '/inventory-object-custom-field-assignment/' . $p['id'] . '/'),
            'createInventoryObjectCustomFieldAssignment' => $this->client->post($p['token'], '/inventory-object-custom-field-assignment/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'updateInventoryObjectCustomFieldAssignment' => $this->client->patch($p['token'], '/inventory-object-custom-field-assignment/' . $p['id'] . '/', $this->bodyFrom($p, ['custom_field', 'value', 'selected_options'])),
            'deleteInventoryObjectCustomFieldAssignment' => $this->deleted($p['token'], '/inventory-object-custom-field-assignment/' . $p['id'] . '/', 'InventoryObjectCustomFieldAssignment'),
            'listLendings'                               => $this->client->get($p['token'], '/lending/', $this->pagination($p)),
            'getLending'                                 => $this->client->get($p['token'], '/lending/' . $p['id'] . '/'),
            'createLending'                              => $this->client->post($p['token'], '/lending/', $this->bodyFrom($p, ['parent_inventory_object', 'borrow_address', 'borrowing_date', 'return_date', 'quantity', 'borrow_time', 'return_time', 'state'])),
            'updateLending'                              => $this->client->patch($p['token'], '/lending/' . $p['id'] . '/', $this->bodyFrom($p, ['parent_inventory_object', 'borrow_address', 'borrowing_date', 'return_date', 'quantity', 'borrow_time', 'return_time', 'state'])),
            'deleteLending'                              => $this->deleted($p['token'], '/lending/' . $p['id'] . '/', 'Lending'),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }
}
