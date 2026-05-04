<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

class McpServerResourcesTest extends AbstractMcpTest
{
    public function testResourcesListReturnsResources(): void
    {
        $result    = $this->post($this->jsonRpc('resources/list'));
        $resources = $result['data']['result']['resources'];
        self::assertIsArray($resources);
        self::assertNotEmpty($resources);
    }

    public function testResourcesListAllHaveNameAndUri(): void
    {
        $result = $this->post($this->jsonRpc('resources/list'));
        foreach ($result['data']['result']['resources'] as $resource) {
            self::assertArrayHasKey('name', $resource);
            self::assertArrayHasKey('uri', $resource);
        }
    }

    public function testResourcesTemplatesListReturnsTemplates(): void
    {
        $result    = $this->post($this->jsonRpc('resources/templates/list'));
        $templates = $result['data']['result']['resourceTemplates'];
        self::assertIsArray($templates);
        self::assertNotEmpty($templates);
    }

    public function testResourcesReadCallsApiClient(): void
    {
        $this->http->addResponse('{"id":42,"profileName":"Max"}', 200);
        $result = $this->post(
            $this->jsonRpc('resources/read', ['uri' => 'easyverein://member/42']),
            'Bearer test-token',
        );
        self::assertArrayHasKey('result', $result['data']);
        self::assertArrayHasKey('contents', $result['data']['result']);
    }

    public function testResourcesReadUnknownUriReturnsError(): void
    {
        $result = $this->post(
            $this->jsonRpc('resources/read', ['uri' => 'easyverein://totally-unknown/123']),
            'Bearer test-token',
        );
        self::assertArrayHasKey('result', $result['data']);
        self::assertNotEmpty($result['data']['result']['contents'][0]['text'] ?? '');
    }
}
