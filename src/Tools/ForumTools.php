<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tools;

use EasyVerein\Mcp\ApiClient;

class ForumTools extends AbstractTools
{

    public function getDefinitions(): array
    {
        $pagination = [
            'limit'  => ['type' => 'integer', 'description' => 'Maximum number of results'],
            'page'   => ['type' => 'integer', 'description' => 'Page number (default: 1)'],
        ];
        $id = ['id' => ['type' => 'integer', 'description' => 'Record ID']];

        $forumFields = [
            'name'           => ['type' => 'string',  'description' => 'Forum name'],
            'description'    => ['type' => 'string',  'description' => 'Forum description'],
            'is_public'      => ['type' => 'boolean', 'description' => 'Whether publicly visible'],
            'allowed_groups' => ['type' => 'array',   'description' => 'Allowed group IDs (array)'],
            'order'          => ['type' => 'integer', 'description' => 'Sort order'],
        ];

        $topicFields = [
            'title'       => ['type' => 'string',  'description' => 'Topic title'],
            'text'        => ['type' => 'string',  'description' => 'Initial post text'],
            'forum'       => ['type' => 'integer', 'description' => 'Forum ID'],
            'is_public'   => ['type' => 'boolean', 'description' => 'Whether publicly visible'],
            'attachments' => ['type' => 'array',   'description' => 'Attachment IDs (array)'],
        ];

        $postFields = [
            'text'        => ['type' => 'string',  'description' => 'Post text'],
            'topic'       => ['type' => 'integer', 'description' => 'Topic ID'],
            'attachments' => ['type' => 'array',   'description' => 'Attachment IDs (array)'],
        ];

        return [
            // Resources (read-only)
            ['name' => 'listForums', 'uri' => 'easyverein://forum/{?limit,page}',              'description' => 'List all forums.',                    'required' => [],     'props' => $pagination],
            ['name' => 'getForum',   'uri' => 'easyverein://forum/{id}',                         'description' => 'Get a forum by ID.',                  'required' => ['id'], 'props' => $id],
            ['name' => 'listTopics', 'uri' => 'easyverein://topic/{?limit,page,forum}',        'description' => 'List forum topics.',                  'required' => [],     'props' => $pagination + ['forum' => ['type' => 'integer', 'description' => 'Filter by forum ID']]],
            ['name' => 'getTopic',   'uri' => 'easyverein://topic/{id}',                         'description' => 'Get a forum topic by ID.',            'required' => ['id'], 'props' => $id],
            ['name' => 'listPosts',  'uri' => 'easyverein://post/{?limit,page,topic}',         'description' => 'List forum posts.',                   'required' => [],     'props' => $pagination + ['topic' => ['type' => 'integer', 'description' => 'Filter by topic ID']]],
            ['name' => 'getPost',    'uri' => 'easyverein://post/{id}',                          'description' => 'Get a forum post by ID.',             'required' => ['id'], 'props' => $id],

            // Tools (mutating)
            ['name' => 'createForum', 'description' => 'Create a new forum.',       'required' => [],     'props' => $forumFields],
            ['name' => 'updateForum', 'description' => 'Update a forum.',           'required' => ['id'], 'props' => $id + $forumFields],
            ['name' => 'deleteForum', 'description' => 'Delete a forum.',           'required' => ['id'], 'props' => $id],
            ['name' => 'createTopic', 'description' => 'Create a new forum topic.', 'required' => [],     'props' => $topicFields],
            ['name' => 'updateTopic', 'description' => 'Update a forum topic.',     'required' => ['id'], 'props' => $id + $topicFields],
            ['name' => 'deleteTopic', 'description' => 'Delete a forum topic.',     'required' => ['id'], 'props' => $id],
            ['name' => 'createPost',  'description' => 'Create a new forum post.',  'required' => [],     'props' => $postFields],
            ['name' => 'updatePost',  'description' => 'Update a forum post.',      'required' => ['id'], 'props' => $id + $postFields],
            ['name' => 'deletePost',  'description' => 'Delete a forum post.',      'required' => ['id'], 'props' => $id],
        ];
    }

    public function dispatch(string $name, array $p): string
    {
        return match ($name) {
            'listForums'  => $this->client->get($p['token'], '/forum/', $this->pagination($p)),
            'getForum'    => $this->client->get($p['token'], '/forum/' . $p['id'] . '/'),
            'createForum' => $this->client->post($p['token'], '/forum/', $this->bodyFrom($p, ['name', 'description', 'is_public', 'allowed_groups', 'order'])),
            'updateForum' => $this->client->patch($p['token'], '/forum/' . $p['id'] . '/', $this->bodyFrom($p, ['name', 'description', 'is_public', 'allowed_groups', 'order'])),
            'deleteForum' => $this->deleted($p['token'], '/forum/' . $p['id'] . '/', 'Forum'),
            'listTopics'  => $this->client->get($p['token'], '/topic/', $this->pagination($p) + $this->optional($p, 'forum')),
            'getTopic'    => $this->client->get($p['token'], '/topic/' . $p['id'] . '/'),
            'createTopic' => $this->client->post($p['token'], '/topic/', $this->bodyFrom($p, ['title', 'text', 'forum', 'is_public', 'attachments'])),
            'updateTopic' => $this->client->patch($p['token'], '/topic/' . $p['id'] . '/', $this->bodyFrom($p, ['title', 'text', 'forum', 'is_public', 'attachments'])),
            'deleteTopic' => $this->deleted($p['token'], '/topic/' . $p['id'] . '/', 'Topic'),
            'listPosts'   => $this->client->get($p['token'], '/post/', $this->pagination($p) + $this->optional($p, 'topic')),
            'getPost'     => $this->client->get($p['token'], '/post/' . $p['id'] . '/'),
            'createPost'  => $this->client->post($p['token'], '/post/', $this->bodyFrom($p, ['text', 'topic', 'attachments'])),
            'updatePost'  => $this->client->patch($p['token'], '/post/' . $p['id'] . '/', $this->bodyFrom($p, ['text', 'topic', 'attachments'])),
            'deletePost'  => $this->deleted($p['token'], '/post/' . $p['id'] . '/', 'Post'),
            default => throw new \InvalidArgumentException("Unknown tool: $name"),
        };
    }

}
