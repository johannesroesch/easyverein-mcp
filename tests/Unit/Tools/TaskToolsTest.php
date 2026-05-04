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

    // ── updateTask ────────────────────────────────────────────────────────────

    public function testUpdateTaskUsesPatch(): void
    {
        $this->addOk('{"id":4}');
        $this->tools->dispatch('updateTask', ['token' => 'tok', 'id' => 4, 'completed' => true]);
        $this->assertPatchTo('/task/4/');
        $this->assertBodyContains('completed', true);
    }

    // ── listTaskGroups ────────────────────────────────────────────────────────

    public function testListTaskGroupsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listTaskGroups', ['token' => 'tok']);
        $this->assertGetTo('/task-group/');
    }

    // ── getTaskGroup ──────────────────────────────────────────────────────────

    public function testGetTaskGroupUsesCorrectPath(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('getTaskGroup', ['token' => 'tok', 'id' => 2]);
        $this->assertGetTo('/task-group/2/');
    }

    // ── createTaskGroup ───────────────────────────────────────────────────────

    public function testCreateTaskGroupUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createTaskGroup', ['token' => 'tok', 'name' => 'Backlog']);
        $this->assertPostTo('/task-group/');
        $this->assertBodyContains('name', 'Backlog');
    }

    // ── updateTaskGroup ───────────────────────────────────────────────────────

    public function testUpdateTaskGroupUsesPatch(): void
    {
        $this->addOk('{"id":2}');
        $this->tools->dispatch('updateTaskGroup', ['token' => 'tok', 'id' => 2, 'color' => '#FF0000']);
        $this->assertPatchTo('/task-group/2/');
        $this->assertBodyContains('color', '#FF0000');
    }

    // ── deleteTaskGroup ───────────────────────────────────────────────────────

    public function testDeleteTaskGroupReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteTaskGroup', ['token' => 'tok', 'id' => 2]);
        $this->assertDeletedMessage($result);
    }

    // ── listTaskComments ──────────────────────────────────────────────────────

    public function testListTaskCommentsUsesGet(): void
    {
        $this->addOk('{"results":[]}');
        $this->tools->dispatch('listTaskComments', ['token' => 'tok']);
        $this->assertGetTo('/task-comment/');
    }

    // ── getTaskComment ────────────────────────────────────────────────────────

    public function testGetTaskCommentUsesCorrectPath(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('getTaskComment', ['token' => 'tok', 'id' => 6]);
        $this->assertGetTo('/task-comment/6/');
    }

    // ── createTaskComment ─────────────────────────────────────────────────────

    public function testCreateTaskCommentUsesPost(): void
    {
        $this->addOk('{"id":1}', 201);
        $this->tools->dispatch('createTaskComment', ['token' => 'tok', 'text' => 'Done!', 'task' => 4]);
        $this->assertPostTo('/task-comment/');
        $this->assertBodyContains('text', 'Done!');
        $this->assertBodyContains('task', 4);
    }

    // ── updateTaskComment ─────────────────────────────────────────────────────

    public function testUpdateTaskCommentUsesPatch(): void
    {
        $this->addOk('{"id":6}');
        $this->tools->dispatch('updateTaskComment', ['token' => 'tok', 'id' => 6, 'text' => 'Updated comment']);
        $this->assertPatchTo('/task-comment/6/');
        $this->assertBodyContains('text', 'Updated comment');
    }

    // ── deleteTaskComment ─────────────────────────────────────────────────────

    public function testDeleteTaskCommentReturnsDeletedMessage(): void
    {
        $this->addDeleted();
        $result = $this->tools->dispatch('deleteTaskComment', ['token' => 'tok', 'id' => 6]);
        $this->assertDeletedMessage($result);
    }

    // ── Unknown tool ──────────────────────────────────────────────────────────

    public function testUnknownToolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tools->dispatch('nonExistentTaskTool', ['token' => 'tok']);
    }
}
