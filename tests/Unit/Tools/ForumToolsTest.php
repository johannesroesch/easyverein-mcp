<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\ForumTools;

class ForumToolsTest extends AbstractToolsTest
{
    private ForumTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new ForumTools($this->apiClient);
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

    public function testListForumsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listForums', ['token' => 'tok']);
        $this->assertGetTo('/forum/');
    }

    public function testGetForumUsesCorrectPath(): void
    {
        $this->addOk('{"id":5}');
        $this->tools->dispatch('getForum', ['token' => 'tok', 'id' => 5]);
        $this->assertGetTo('/forum/5/');
    }

    public function testCreateForumUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createForum', ['token' => 'tok', 'name' => 'General']);
        $this->assertPostTo('/forum/');
    }

    public function testDeleteForumReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteForum', ['token' => 'tok', 'id' => 1]);
        $this->assertDeletedMessage($result);
    }

    public function testListTopicsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listTopics', ['token' => 'tok']);
        $this->assertGetTo('/topic/');
    }

    public function testCreateTopicUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createTopic', ['token' => 'tok', 'title' => 'New Topic']);
        $this->assertPostTo('/topic/');
    }

    public function testDeleteTopicReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteTopic', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    public function testListPostsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listPosts', ['token' => 'tok']);
        $this->assertGetTo('/post/');
    }

    public function testDeletePostReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deletePost', ['token' => 'tok', 'id' => 3]);
        $this->assertDeletedMessage($result);
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('noSuchTool', ['token' => 'tok']);
    }
}
