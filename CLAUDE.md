# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project does

MCP (Model Context Protocol) server that exposes the [EasyVerein API v3.0](https://easyverein.com/api/v3.0/documentation) as MCP tools. Claude or any MCP-compatible client can call these tools to manage a Vereinsverwaltung (club management system).

**Token model:** The server has no stored credentials. Every MCP tool call requires the caller to supply a `token` parameter containing the user's EasyVerein API token. The server forwards it as `Authorization: Token <token>`.

## Commands

```sh
composer install                      # Install dependencies
php -S localhost:8080 -t public/      # Start dev server (MCP on :8080)
cp .env.example .env                  # Copy env config (adjust base URL if needed)
```

## Architecture

```
public/index.php       – Slim entry point; defines GET /sse and POST /messages routes
  │
  ├── src/
  │     ├── ApiClient.php    – thin cURL proxy (get/post/patch/delete) that injects
  │     │                      the per-call token as Authorization header
  │     ├── McpServer.php    – MCP protocol: tool registry, SSE handshake,
  │     │                      JSON-RPC 2.0 dispatch (initialize / tools/list / tools/call)
  │     └── Tools/
  │           ├── MemberTools.php         – member, member-group, member-group-assignment
  │           ├── ContactDetailsTools.php – contact-details, contact-details-group
  │           ├── EventTools.php          – event, participation
  │           ├── FinanceTools.php        – booking, invoice, invoice-item, bank-account, booking-project
  │           ├── ForumTools.php          – forum, topic, post
  │           └── MiscTools.php           – custom-field, task, document-template, protocol,
  │                                         inventory-object, calendar, location, voting,
  │                                         organization, wastebasket, accounting-plan, notification-log
  └── .env.example           – EASYVEREIN_BASE_URL configuration
```

## Key conventions

- Each `*Tools` class exposes `getDefinitions(): array` (tool name + inputSchema) and `dispatch(string $name, array $params): string`.
- `McpServer` aggregates all tool classes and handles MCP routing.
- API responses are returned as raw JSON strings (the LLM parses them).
- All API calls go through `ApiClient`; never use cURL directly in a tool class.
- Optional parameters are only forwarded when present in `$params`.

## MCP transport

HTTP/SSE on port 8080 (configurable via your web server).
- `GET  /sse`      – opens the SSE stream and sends the `endpoint` event
- `POST /messages` – receives JSON-RPC 2.0 requests, returns JSON responses

## Adding new tools

1. Add entries in `getDefinitions()` and a `match` arm in `dispatch()` in the appropriate `*Tools` class (or create a new one).
2. If creating a new class, instantiate it in `McpServer::__construct()`.
3. No other wiring needed.

## EasyVerein API reference

Base URL: `https://easyverein.com/api/v3.0`  
All list endpoints support `limit` and `offset` query params for pagination.  
All mutating operations accept/return JSON. PATCH is used for partial updates.
