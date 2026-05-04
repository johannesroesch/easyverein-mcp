<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

class TaskTools extends AbstractTools
{
    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id = ['id' => ['type' => 'integer', 'description' => 'Record ID']];

        $taskFields = [
            'name'         => ['type' => 'string',  'description' => 'Task title'],
            'description'  => ['type' => 'string',  'description' => 'Task description'],
            'due_date'     => ['type' => 'string',  'description' => 'Due date (YYYY-MM-DD)'],
            'completed'    => ['type' => 'boolean', 'description' => 'Whether completed'],
            'assigned_to'  => ['type' => 'integer', 'description' => 'Assigned member ID'],
            'task_group'   => ['type' => 'integer', 'description' => 'Task group ID'],
            'attachments'  => ['type' => 'array',   'description' => 'Attachment IDs (array)'],
        ];

        $taskGroupFields = [
            'name'  => ['type' => 'string', 'description' => 'Name'],
            'color' => ['type' => 'string', 'description' => 'Hex color (e.g. #FF0000)'],
            'short' => ['type' => 'string', 'description' => 'Short code (max 4 chars)'],
        ];

        $taskCommentFields = [
            'text'    => ['type' => 'string',  'description' => 'Comment text'],
            'task'    => ['type' => 'integer', 'description' => 'Task ID'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listTasks',         'uri' => 'easyverein://task/{?limit,page}',         'description' => 'List all tasks.',         'required' => [],     'props' => $pagination],
            ['name' => 'getTask',           'uri' => 'easyverein://task/{id}',                   'description' => 'Get a task by ID.',       'required' => ['id'], 'props' => $id],
            ['name' => 'listTaskGroups',    'uri' => 'easyverein://task-group/{?limit,page}',    'description' => 'List all task groups.',   'required' => [],     'props' => $pagination],
            ['name' => 'getTaskGroup',      'uri' => 'easyverein://task-group/{id}',              'description' => 'Get a task group by ID.','required' => ['id'], 'props' => $id],
            ['name' => 'listTaskComments',  'uri' => 'easyverein://task-comment/{?limit,page}',  'description' => 'List all task comments.', 'required' => [],     'props' => $pagination],
            ['name' => 'getTaskComment',    'uri' => 'easyverein://task-comment/{id}',            'description' => 'Get a task comment by ID.', 'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createTask',        'description' => 'Create a new task.',     'required' => [],     'props' => $taskFields],
            ['name' => 'updateTask',        'description' => 'Update a task.',         'required' => ['id'], 'props' => $id + $taskFields],
            ['name' => 'deleteTask',        'description' => 'Delete a task.',         'required' => ['id'], 'props' => $id],
            ['name' => 'createTaskGroup',   'description' => 'Create a new task group.', 'required' => [],   'props' => $taskGroupFields],
            ['name' => 'updateTaskGroup',   'description' => 'Update a task group.',   'required' => ['id'], 'props' => $id + $taskGroupFields],
            ['name' => 'deleteTaskGroup',   'description' => 'Delete a task group.',   'required' => ['id'], 'props' => $id],
            ['name' => 'createTaskComment', 'description' => 'Create a task comment.', 'required' => [],     'props' => $taskCommentFields],
            ['name' => 'updateTaskComment', 'description' => 'Update a task comment.', 'required' => ['id'], 'props' => $id + $taskCommentFields],
            ['name' => 'deleteTaskComment', 'description' => 'Delete a task comment.', 'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listTasks'         => $this->client->get($p['token'], '/task/', $this->pagination($p)),
            'getTask'           => $this->client->get($p['token'], '/task/' . $p['id'] . '/'),
            'createTask'        => $this->client->post($p['token'], '/task/', $this->bodyFrom($p, ['name', 'description', 'due_date', 'completed', 'assigned_to', 'task_group', 'attachments'])),
            'updateTask'        => $this->client->patch($p['token'], '/task/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'description', 'due_date', 'completed', 'assigned_to', 'task_group', 'attachments'])),
            'deleteTask'        => $this->deleted($p['token'], '/task/' . $p['id'] . '/', 'Task'),
            'listTaskGroups'    => $this->client->get($p['token'], '/task-group/', $this->pagination($p)),
            'getTaskGroup'      => $this->client->get($p['token'], '/task-group/' . $p['id'] . '/'),
            'createTaskGroup'   => $this->client->post($p['token'], '/task-group/', $this->bodyFrom($p, ['name', 'color', 'short'])),
            'updateTaskGroup'   => $this->client->patch($p['token'], '/task-group/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'color', 'short'])),
            'deleteTaskGroup'   => $this->deleted($p['token'], '/task-group/' . $p['id'] . '/', 'TaskGroup'),
            'listTaskComments'  => $this->client->get($p['token'], '/task-comment/', $this->pagination($p)),
            'getTaskComment'    => $this->client->get($p['token'], '/task-comment/' . $p['id'] . '/'),
            'createTaskComment' => $this->client->post($p['token'], '/task-comment/', $this->bodyFrom($p, ['text', 'task'])),
            'updateTaskComment' => $this->client->patch($p['token'], '/task-comment/' . $p['id'] . '/', $this->bodyFrom($p, ['text', 'task'])),
            'deleteTaskComment' => $this->deleted($p['token'], '/task-comment/' . $p['id'] . '/', 'TaskComment'),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }
}
