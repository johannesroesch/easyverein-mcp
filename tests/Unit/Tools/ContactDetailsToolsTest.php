<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\ContactDetailsTools;

class ContactDetailsToolsTest extends AbstractToolsTest
{
    private ContactDetailsTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new ContactDetailsTools($this->apiClient);
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

    public function testListContactDetailsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetails', ['token' => 'tok']);
        $this->assertGetTo('/contact-details/');
    }

    public function testGetContactDetailsUsesCorrectPath(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('getContactDetails', ['token' => 'tok', 'id' => 7]);
        $this->assertGetTo('/contact-details/7/');
    }

    public function testCreateContactDetailsUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createContactDetails', ['token' => 'tok', 'name' => 'Mustermann']);
        $this->assertPostTo('/contact-details/');
    }

    public function testUpdateContactDetailsUsesPatch(): void
    {
        $this->addOk('{"id":7}');
        $this->tools->dispatch('updateContactDetails', ['token' => 'tok', 'id' => 7, 'name' => 'Updated']);
        $this->assertPatchTo('/contact-details/7/');
    }

    public function testDeleteContactDetailsReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteContactDetails', ['token' => 'tok', 'id' => 7]);
        $this->assertDeletedMessage($result);
    }

    public function testListContactDetailsGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listContactDetailGroups', ['token' => 'tok']);
        $this->assertGetTo('/contact-details-group/');
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistent', ['token' => 'tok']);
    }
}
