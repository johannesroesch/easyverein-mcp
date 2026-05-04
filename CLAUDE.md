# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project does

MCP (Model Context Protocol) server that exposes the [EasyVerein API v3.0](https://easyverein.com/api/v3.0/documentation) as MCP tools, resources, and prompts. Claude or any MCP-compatible client can manage a Vereinsverwaltung (club management system) via natural language.

**Token model:** The server stores no credentials. Every request requires a valid EasyVerein API token:
- HTTP transports: `Authorization: Bearer <token>` header
- stdio: `EASYVEREIN_TOKEN` environment variable

The token is forwarded as `Authorization: Bearer <token>` to the EasyVerein API and discarded after the request.

## Commands

```sh
composer install                                          # Install dependencies
cp .env.example .env                                      # Copy env config
PHP_CLI_SERVER_WORKERS=4 php -S localhost:8080 -t public/ # Start dev server
php vendor/bin/phpunit                                    # Run all tests
php vendor/bin/phpunit --testsuite Unit                   # Unit tests only
php vendor/bin/phpunit --testsuite Integration            # Integration tests only
```

> `PHP_CLI_SERVER_WORKERS=4` is required for the elicitation (delete confirmation) flow to work correctly — the polling loop needs a second worker to receive the user's response.

## Architecture

```
public/index.php          – Slim 4 entry point; HTTP routing for all three transports
bin/mcp-stdio.php         – stdio entry point

src/
├── McpServer.php         – MCP protocol: JSON-RPC dispatch, session management,
│                           tool/resource/prompt registry, elicitation flow
├── ApiClient.php         – cURL proxy (get/post/patch/delete); injects Bearer token;
│                           handles rate limiting (HTTP 429 with auto-retry)
├── HttpClientInterface.php  – Abstraction over HTTP calls (injectable for testing)
├── CurlHttpClient.php    – Production implementation via cURL
├── Logger.php            – Structured JSON logger (NDJSON → stderr)
│                           Fields: ts, level, msg, req, transport, sess
├── PromptRegistry.php    – 15 guided workflows (prompts/list, prompts/get)
├── AuthException.php     – Thrown on HTTP 401/403
├── ExitException.php     – Replaces exit() for testability (elicitation flow)
├── RateLimitException.php – Thrown on second consecutive HTTP 429
└── Tools/
    ├── MemberTools.php         – member, member-group, member-group-assignment,
    │                             member custom fields & change requests
    ├── ContactDetailsTools.php – contact-details, contact-details-groups,
    │                             change requests, logs, custom fields, former member data
    ├── EventTools.php          – event, participation, event custom fields,
    │                             application-form, application-form-element
    ├── FinanceTools.php        – booking, invoice, invoice-item, bank-account,
    │                             booking-project, billing-account, tax-rate, debit-order,
    │                             payment-method, participation-price-group,
    │                             cancellation, discount-code
    ├── ForumTools.php          – forum, topic, post
    ├── PasscreatorTools.php    – pass, pass-field, pass-template, passcreator-integration
    ├── TaskTools.php           – task, task-group, task-comment
    ├── ProtocolTools.php       – protocol, protocol-element, protocol-element-comment,
    │                             protocol-upload
    ├── InventoryTools.php      – inventory-object, inventory-object-group,
    │                             inventory-object-custom-field-assignment, lending
    └── MiscTools.php           – custom-field, document-template, calendar, location,
                                  voting, organization, wastebasket, accounting-plan,
                                  price-group, notification-log, select-option,
                                  oauth2, smtp, chairman, and more (~110 tools)

tests/
├── bootstrap.php
├── MockHttpClient.php          – HttpClientInterface implementation for tests
├── Fixtures/                   – JSON fixtures for tests
├── Unit/                       – Unit tests (ApiClient, Logger, Tools, etc.)
│   └── Tools/                  – One test class per *Tools class
└── Integration/                – Integration tests (McpServer JSON-RPC flows)
```

## Transports

| Transport | Protocol | Entry point |
|-----------|----------|-------------|
| Streamable HTTP (recommended) | 2025-11-25 | `POST /mcp` |
| HTTP + SSE (legacy) | 2024-11-05 | `GET /sse` + `POST /messages` |
| stdio | — | `bin/mcp-stdio.php` |

## Key conventions

- Each `*Tools` class exposes `getDefinitions(): array` (tool name + inputSchema + optional `uri` for resources) and `dispatch(string $name, array $params): string`.
- `McpServer` aggregates all tool classes and handles MCP routing.
- API responses are returned as raw JSON strings (the LLM parses them).
- All API calls go through `ApiClient`; never use cURL directly in a tool class.
- Optional parameters are only included in the request body when present in `$params` — never send `null`.
- Reference fields (hyperlinked relations) must be full URLs, not integer IDs. Use `ApiClient::urlRef(string $path, int $id): string` and the `$urlFields` parameter in `bodyFrom()`.

## Adding new tools

1. Add entries in `getDefinitions()` and a `match` arm in `dispatch()` in the appropriate `*Tools` class (or create a new one).
2. If creating a new class, instantiate it in `McpServer::__construct()` and add it to `$toolClasses`.
3. No other wiring needed.

To also expose a tool as a read-only **resource**, add a `uri` key to the definition:
```php
'uri' => 'easyverein://my-entity/{id}',        // single object
'uri' => 'easyverein://my-entity{?limit,page}', // list (URI template)
```

## EasyVerein API reference

Base URL: `https://easyverein.com/api/v3.0`  
All list endpoints support `limit` and `offset` query params for pagination.  
Mutating operations use POST (create) and PATCH (partial update).  
Reference fields expect full hyperlinked URLs (DRF style), not integer IDs.
