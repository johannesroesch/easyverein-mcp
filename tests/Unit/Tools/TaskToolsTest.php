<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Unit\Tools;

use EasyVerein\Mcp\Tools\TaskTools;

class TaskToolsTest extends AbstractToolsTest
{
    private TaskTools $tools;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tools = new TaskTools($this->apiClient);
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

    public function testListTasksUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listTasks', ['token' => 'tok']);
        $this->assertGetTo('/task/');
    }

    public function testGetTaskUsesCorrectPath(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('getTask', ['token' => 'tok', 'id' => 4]);
        $this->assertGetTo('/task/4/');
    }

    public function testCreateTaskUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createTask', ['token' => 'tok', 'name' => 'Important Task']);
        $this->assertPostTo('/task/');
    }

    public function testDeleteTaskReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteTask', ['token' => 'tok', 'id' => 4]);
        $this->assertDeletedMessage($result);
    }

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentTaskTool', ['token' => 'tok']);
    }
}
