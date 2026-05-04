<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\InventoryTools;

class InventoryToolsTest extends AbstractToolsTest
{
    private InventoryTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new InventoryTools($this->apiClient);
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

    public function testListInventoryObjectsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInventoryObjects', ['token' => 'tok']);
        $this->assertGetTo('/inventory-object/');
    }

    public function testGetInventoryObjectUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getInventoryObject', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/inventory-object/7/');
    }

    public function testDeleteInventoryObjectReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteInventoryObject', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    // ── createInventoryObject ─────────────────────────────────────────────────

    public function testCreateInventoryObjectUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createInventoryObject', ['token' => 'tok', 'name' => 'Beamer']);
        $this->assertPostTo('/inventory-object/');
        $this->assertBodyContains('name', 'Beamer');
    }

    // ── updateInventoryObject ─────────────────────────────────────────────────

    public function testUpdateInventoryObjectUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateInventoryObject', ['token' => 'tok', 'id' => 7, 'pieces' => 2]);
        $this->assertPatchTo('/inventory-object/7/');
        $this->assertBodyContains('pieces', 2);
    }

    // ── listInventoryObjectGroups ─────────────────────────────────────────────

    public function testListInventoryObjectGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInventoryObjectGroups', ['token' => 'tok']);
        $this->assertGetTo('/inventory-object-group/');
    }

    // ── getInventoryObjectGroup ───────────────────────────────────────────────

    public function testGetInventoryObjectGroupUsesCorrectPath(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('getInventoryObjectGroup', ['token' => 'tok', 'id' => 3]);
        $this->assertGetTo('/inventory-object-group/3/');
    }

    // ── createInventoryObjectGroup ────────────────────────────────────────────

    public function testCreateInventoryObjectGroupUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createInventoryObjectGroup', ['token' => 'tok', 'name' => 'Electronics']);
        $this->assertPostTo('/inventory-object-group/');
        $this->assertBodyContains('name', 'Electronics');
    }

    // ── updateInventoryObjectGroup ────────────────────────────────────────────

    public function testUpdateInventoryObjectGroupUsesPatch(): void
    {
        $this->addOk('{"id":3}');
        $this->tools->dispatch('updateInventoryObjectGroup', ['token' => 'tok', 'id' => 3, 'color' => '#0000FF']);
        $this->assertPatchTo('/inventory-object-group/3/');
        $this->assertBodyContains('color', '#0000FF');
    }

    // ── deleteInventoryObjectGroup ────────────────────────────────────────────

    public function testDeleteInventoryObjectGroupReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteInventoryObjectGroup', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    // ── listInventoryObjectCustomFieldAssignments ─────────────────────────────

    public function testListInventoryObjectCustomFieldAssignmentsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInventoryObjectCustomFieldAssignments', ['token' => 'tok']);
        $this->assertGetTo('/inventory-object-custom-field-assignment/');
    }

    public function testListInventoryObjectCustomFieldAssignmentsWithFilter(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listInventoryObjectCustomFieldAssignments', ['token' => 'tok', 'inventory_object' => 7]);
        self::assertStringContainsString('inventory_object=7', $this->lastCall()['url']);
    }

    // ── getInventoryObjectCustomFieldAssignment ───────────────────────────────

    public function testGetInventoryObjectCustomFieldAssignmentUsesCorrectPath(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('getInventoryObjectCustomFieldAssignment', ['token' => 'tok', 'id' => 9]);
        $this->assertGetTo('/inventory-object-custom-field-assignment/9/');
    }

    // ── createInventoryObjectCustomFieldAssignment ────────────────────────────

    public function testCreateInventoryObjectCustomFieldAssignmentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createInventoryObjectCustomFieldAssignment', ['token' => 'tok', 'custom_field' => 5, 'value' => 'yes']);
        $this->assertPostTo('/inventory-object-custom-field-assignment/');
        $this->assertBodyContains('custom_field', 5);
    }

    // ── updateInventoryObjectCustomFieldAssignment ────────────────────────────

    public function testUpdateInventoryObjectCustomFieldAssignmentUsesPatch(): void
    {
        $this->addOk('{"id":9}');
        $this->tools->dispatch('updateInventoryObjectCustomFieldAssignment', ['token' => 'tok', 'id' => 9, 'value' => 'no']);
        $this->assertPatchTo('/inventory-object-custom-field-assignment/9/');
        $this->assertBodyContains('value', 'no');
    }

    // ── deleteInventoryObjectCustomFieldAssignment ────────────────────────────

    public function testDeleteInventoryObjectCustomFieldAssignmentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteInventoryObjectCustomFieldAssignment', ['token' => 'tok', 'id' => 9]);
        $this->assertDeletedMessage($result);
    }

    // ── listLendings ─────────────────────────────────────────────────────────

    public function testListLendingsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listLendings', ['token' => 'tok']);
        $this->assertGetTo('/lending/');
    }

    // ── getLending ────────────────────────────────────────────────────────────

    public function testGetLendingUsesCorrectPath(): void
    {
        $this->addOk('{"id":12}');
        $this->tools->dispatch('getLending', ['token' => 'tok', 'id' => 12]);
        $this->assertGetTo('/lending/12/');
    }

    // ── createLending ────────────────────────────────────────────────────────

    public function testCreateLendingUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createLending', ['token' => 'tok', 'parent_inventory_object' => 7, 'borrow_address' => 3]);
        $this->assertPostTo('/lending/');
        $this->assertBodyContains('parent_inventory_object', 7);
    }

    // ── updateLending ─────────────────────────────────────────────────────────

    public function testUpdateLendingUsesPatch(): void
    {
        $this->addOk('{"id":12}');
        $this->tools->dispatch('updateLending', ['token' => 'tok', 'id' => 12, 'state' => 'returned']);
        $this->assertPatchTo('/lending/12/');
        $this->assertBodyContains('state', 'returned');
    }

    // ── deleteLending ─────────────────────────────────────────────────────────

    public function testDeleteLendingReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteLending', ['token' => 'tok', 'id' => 12]);
        $this->assertDeletedMessage($result);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentInventoryTool', ['token' => 'tok']);
    }
}
