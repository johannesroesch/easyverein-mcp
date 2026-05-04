<?php

declare(strict_types=1);

namespace EasyVerein\Mcp\Tests\Integration;

class McpServerPaginationTest extends AbstractMcpTest
{
    private function readMembersResource(string $apiResponse): array
    {
        $this->http->addResponse($apiResponse, 200);
        $result = $this->post(
            $this->jsonRpc('resources/read', ['uri' => 'easyverein://member/']),
            'Bearer test-token'
        );
        $content = $result['data']['result']['contents'][0]['text'] ?? '{}';
        return json_decode($content, true) ?? [];
    }

    public function testPaginatedResponseIsTransformed(): void
    {
        $paginated = json_encode([
            'count'    => 25,
            'next'     => 'https://easyverein.com/api/v3.0/member/?limit=10&offset=10&page=2',
            'previous' => null,
            'current'  => 'https://easyverein.com/api/v3.0/member/?limit=10&offset=0',
            'results'  => [['id' => 1], ['id' => 2]],
        ]);

        $data = $this->readMembersResource($paginated);
        self::assertArrayHasKey('results', $data);
        self::assertArrayHasKey('pagination', $data);
    }

    public function testPaginationNextPageExtracted(): void
    {
        $paginated = json_encode([
            'count'    => 25,
            'next'     => 'https://easyverein.com/api/v3.0/member/?limit=10&offset=10&page=2',
            'previous' => null,
            'current'  => 'https://easyverein.com/api/v3.0/member/?limit=10&offset=0',
            'results'  => [],
        ]);

        $data       = $this->readMembersResource($paginated);
        $pagination = $data['pagination'] ?? [];
        self::assertArrayHasKey('nextPage', $pagination);
        self::assertSame(2, $pagination['nextPage']);
    }

    public function testNoPreviousPageWhenFirstPage(): void
    {
        $paginated = json_encode([
            'count'    => 10,
            'next'     => null,
            'previous' => null,
            'current'  => 'https://easyverein.com/api/v3.0/member/?limit=10&offset=0',
            'results'  => [['id' => 1]],
        ]);

        $data       = $this->readMembersResource($paginated);
        $pagination = $data['pagination'] ?? [];
        self::assertArrayNotHasKey('previousPage', $pagination);
    }

    public function testNonPaginatedResponsePassedThrough(): void
    {
        $simple = json_encode(['id' => 42, 'name' => 'Max']);
        $this->http->addResponse($simple, 200);
        $result = $this->post(
            $this->jsonRpc('resources/read', ['uri' => 'easyverein://member/42']),
            'Bearer test-token'
        );
        $content = $result['data']['result']['contents'][0]['text'] ?? '{}';
        $data    = json_decode($content, true);
        self::assertSame(42, $data['id'] ?? null);
        self::assertArrayNotHasKey('pagination', $data);
    }

    public function testEmptyResultsStayEmpty(): void
    {
        $paginated = json_encode([
            'count'    => 0,
            'next'     => null,
            'previous' => null,
            'current'  => null,
            'results'  => [],
        ]);

        $data = $this->readMembersResource($paginated);
        self::assertSame([], $data['results'] ?? null);
    }

    public function testPaginationLimitExtracted(): void
    {
        $paginated = json_encode([
            'count'    => 50,
            'next'     => 'https://easyverein.com/api/v3.0/member/?limit=20&offset=20&page=2',
            'previous' => null,
            'current'  => null,
            'results'  => [],
        ]);

        $data       = $this->readMembersResource($paginated);
        $pagination = $data['pagination'] ?? [];
        self::assertArrayHasKey('limit', $pagination);
        self::assertSame(20, $pagination['limit']);
    }
}
