# Konfiguration

Dieses Dokument beschreibt alle Transportoptionen, Umgebungsvariablen und Client-Konfigurationen.

---

## Umgebungsvariablen

| Variable | Standard | Beschreibung |
|----------|----------|--------------|
| `EASYVEREIN_BASE_URL` | `https://easyverein.com/api/v3.0` | Basis-URL der EasyVerein API |
| `LOG_LEVEL` | `INFO` | Logging-Level: `DEBUG`, `INFO`, `WARNING`, `ERROR` |

Die Variablen werden aus einer `.env`-Datei im Projektwurzelverzeichnis geladen (via `vlucas/phpdotenv`).

```bash
cp .env.example .env
# dann .env anpassen
```

---

## Transporte

### Streamable HTTP (empfohlen)

Protokollversion: `2025-11-25`

| Endpoint | Methode | Beschreibung |
|----------|---------|--------------|
| `/mcp` | POST | JSON-RPC 2.0 (alle MCP-Methoden) |
| `/mcp` | DELETE | Session beenden |

Token wird als HTTP-Header übergeben:
```
Authorization: Bearer DEIN_TOKEN
```

### HTTP + SSE (Legacy)

Protokollversion: `2024-11-05`

| Endpoint | Methode | Beschreibung |
|----------|---------|--------------|
| `/sse` | GET | SSE-Stream öffnen, empfängt `endpoint`-Event |
| `/messages` | POST | JSON-RPC 2.0 Anfragen senden |

Token wird im `initialize`-Request oder als Bearer-Header übergeben.

### stdio

Kein HTTP-Server nötig. Kommunikation über STDIN/STDOUT (Newline-delimited JSON-RPC).

Token aus Umgebungsvariable:
```
EASYVEREIN_TOKEN=DEIN_TOKEN
```

---

## Discovery-Endpunkte

| Endpoint | Beschreibung |
|----------|--------------|
| `GET /` | HTML-Startseite mit Konfigurationssnippets |
| `GET /.well-known/mcp` | Server-Metadaten als JSON |
| `GET /manifest` | Alias für `/.well-known/mcp` |

---

## Client-Konfiguration

### Claude Desktop

Datei: `~/Library/Application Support/Claude/claude_desktop_config.json` (macOS)  
Datei: `%APPDATA%\Claude\claude_desktop_config.json` (Windows)

```json
{
  "mcpServers": {
    "easyverein": {
      "type": "http",
      "url": "http://localhost:8080/mcp",
      "headers": { "Authorization": "Bearer DEIN_TOKEN" }
    }
  }
}
```

### Claude Code

CLI-Befehl (empfohlen):
```bash
claude mcp add --transport http easyverein http://localhost:8080/mcp \
  --header "Authorization: Bearer DEIN_TOKEN"
```

Oder manuell in `.mcp.json` (Projekt) bzw. `~/.claude.json` (global):
```json
{
  "mcpServers": {
    "easyverein": {
      "type": "http",
      "url": "http://localhost:8080/mcp",
      "headers": { "Authorization": "Bearer DEIN_TOKEN" }
    }
  }
}
```

### Cursor

Datei: `.cursor/mcp.json` (Projekt) oder `~/.cursor/mcp.json` (global)

```json
{
  "mcpServers": {
    "easyverein": {
      "url": "http://localhost:8080/mcp",
      "headers": { "Authorization": "Bearer DEIN_TOKEN" }
    }
  }
}
```

### VS Code

Datei: `.vscode/mcp.json` (Projekt) oder User Settings JSON

```json
{
  "mcp": {
    "servers": {
      "easyverein": {
        "type": "http",
        "url": "http://localhost:8080/mcp",
        "headers": { "Authorization": "Bearer DEIN_TOKEN" }
      }
    }
  }
}
```

### Cline / Roo Code

Einstellungen → „Edit MCP Settings"

```json
{
  "easyverein": {
    "transportType": "streamable-http",
    "url": "http://localhost:8080/mcp",
    "headers": { "Authorization": "Bearer DEIN_TOKEN" }
  }
}
```

### stdio (lokal, kein HTTP-Server)

```json
{
  "mcpServers": {
    "easyverein": {
      "type": "stdio",
      "command": "php",
      "args": ["/pfad/zum/projekt/bin/mcp-stdio.php"],
      "env": { "EASYVEREIN_TOKEN": "DEIN_TOKEN" }
    }
  }
}
```

---

## Token beschaffen

EasyVerein API-Token generieren unter: **Einstellungen → API → Token erstellen**

Der Token wird **nicht** vom Server gespeichert — er wird bei jeder Anfrage als `Authorization: Bearer`-Header an die EasyVerein API weitergeleitet.
