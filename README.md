# EasyVerein MCP Server

Ein [Model Context Protocol (MCP)](https://modelcontextprotocol.io) Server, der die [EasyVerein API v3.0](https://easyverein.com/api/v3.0/documentation) als MCP-Tools und -Resources bereitstellt. Damit können KI-Assistenten wie Claude direkt auf eine EasyVerein-Vereinsverwaltung zugreifen — Mitglieder verwalten, Veranstaltungen anlegen, Finanzen abfragen und vieles mehr.

```
Claude / Cursor / VS Code
        │  MCP (JSON-RPC 2.0)
        ▼
 EasyVerein MCP Server  ──►  EasyVerein API v3.0
   (PHP, Slim 4)
```

## Überblick

| Merkmal | Details |
|---------|---------|
| **Protokoll** | MCP 2025-11-25 (+ Legacy 2024-11-05) |
| **Transporte** | Streamable HTTP · HTTP+SSE · stdio |
| **Tools** | ~176 (create / update / delete) |
| **Resources** | ~120 (read-only, cachebar) |
| **Prompts** | 15 geführte Workflows |
| **Sprache** | PHP 8.2+, Slim 4 |
| **Auth** | Bearer Token (pro Aufruf, kein Server-Secret) |

## Schnellstart

```bash
git clone <repo-url> easyverein-mcp
cd easyverein-mcp
composer install
cp .env.example .env          # EASYVEREIN_BASE_URL anpassen, falls nötig
php -S localhost:8080 -t public/
```

Danach ist der Server unter `http://localhost:8080` erreichbar. Die Startseite zeigt fertige Konfigurationsschnipsel für alle gängigen MCP-Clients.

## Dokumentation

| Zielgruppe | Dokument | Inhalt |
|------------|----------|--------|
| **Alle** | [Schnellstart & Konfiguration](docs/configuration.md) | Transporte, Env-Variablen, Client-Setup |
| **Nutzer** | [Nutzer-Leitfaden](docs/user-guide.md) | Was kann der Server? Prompts, Beispiele |
| **Admins** | [Administrator-Leitfaden](docs/admin-guide.md) | Installation, Deployment, Sicherheit |
| **Entwickler** | [Entwickler-Leitfaden](docs/developer-guide.md) | Architektur, neue Tools hinzufügen, Tests |
| **Referenz** | [Tool- & Resource-Referenz](docs/tools-reference.md) | Vollständige Liste aller Tools und Resources |

## Transporte auf einen Blick

### Streamable HTTP (empfohlen)

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

### stdio (für lokale Nutzung ohne Server)

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

## Token-Modell

Der Server speichert **keine** Credentials. Jede Anfrage erfordert einen EasyVerein API-Token:

- **HTTP-Transporte:** `Authorization: Bearer <token>` Header
- **stdio:** Env-Variable `EASYVEREIN_TOKEN`
- **Legacy SSE:** Token im `initialize`-Request oder Bearer Header

Der Token wird von EasyVerein unter *Einstellungen → API* generiert.

## Funktionsumfang

<details>
<summary>Mitglieder & Kontakte</summary>

- Mitglieder anlegen, bearbeiten, löschen
- Mitgliedsgruppen und Gruppenzuordnungen verwalten
- Kontaktdaten und Änderungsanfragen
- Benutzerdefinierte Felder (Custom Fields)
- Ehemalige Mitgliedsdaten (read-only)

</details>

<details>
<summary>Veranstaltungen</summary>

- Events erstellen und verwalten
- Teilnehmerlisten und Anmeldungen
- Anmeldeformulare und Formularelemente
- Benutzerdefinierte Event-Felder

</details>

<details>
<summary>Finanzen</summary>

- Buchungen, Rechnungen, Rechnungspositionen
- Bankkonten und Buchungsprojekte
- SEPA-Lastschriften und Zahlungsarten
- Abrechnungskonten, Steuersätze, Rabattcodes
- Stornierungen (`cancellation`)

</details>

<details>
<summary>Forum, Inventar & mehr</summary>

- Foren, Themen, Beiträge
- Passcreator-Integration (digitale Mitgliedsausweise)
- Aufgaben, Protokolle, Kalender
- Inventar, Abstimmungen, Dokumente
- Organisationsdaten, Mülleimer, Buchführungspläne

</details>

## Lösch-Bestätigung (Elicitation)

Alle `delete*`-Tools lösen bei Clients, die [MCP Elicitation](https://spec.modelcontextprotocol.io/specification/client/elicitation/) unterstützen, eine Bestätigungsabfrage aus, bevor die Aktion ausgeführt wird. Ohne Elicitation-Support wird direkt gelöscht.

## Lizenz

MIT
