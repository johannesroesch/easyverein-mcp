# Nutzer-Leitfaden

Dieser Leitfaden richtet sich an Vereinsmitglieder und -vorstände, die den EasyVerein MCP Server nutzen möchten, um über einen KI-Assistenten (Claude, Cursor, VS Code) mit ihrer Vereinsverwaltung zu interagieren.

---

## Was kann der Server?

Der Server übersetzt natürlichsprachliche Anfragen in EasyVerein-API-Aufrufe. Du kannst mit Claude sprechen wie mit einem Vereinsassistenten — er führt die Aktionen direkt in EasyVerein aus.

### Typische Anfragen

| Bereich | Beispielanfrage |
|---------|----------------|
| Mitglieder | „Zeige mir alle Mitglieder mit offenen Rechnungen" |
| Mitglieder | „Lege ein neues Mitglied für Max Mustermann an" |
| Veranstaltungen | „Welche Events finden nächsten Monat statt?" |
| Veranstaltungen | „Zeige die Teilnehmerliste für das Sommerfest" |
| Finanzen | „Wie viele offene Rechnungen gibt es gerade?" |
| Finanzen | „Erstelle eine Buchungsübersicht für Oktober 2025" |
| Aufgaben | „Welche Aufgaben sind noch offen?" |
| Inventar | „Zeige das gesamte Vereinsinventar" |
| Forum | „Was wird gerade im Forum diskutiert?" |

---

## Geführte Workflows (Prompts)

Der Server stellt 15 vorgefertigte Prompts bereit, die Claude Schritt für Schritt durch häufige Aufgaben führen. In Claude Desktop findest du sie über das Prompt-Menü (🔖).

| Prompt | Beschreibung | Parameter |
|--------|-------------|-----------|
| `member-overview` | Alle Mitglieder mit Gruppen und Kontaktdaten | — |
| `member-search` | Mitglied nach Name oder E-Mail suchen | `query` |
| `member-onboard` | Neues Mitglied geführt anlegen | — |
| `open-invoices` | Alle offenen Rechnungen mit Gesamtbetrag | — |
| `monthly-bookings` | Buchungsübersicht für einen Monat | `month`, `year` |
| `invoice-for-member` | Rechnung für ein bestimmtes Mitglied erstellen | `member` |
| `upcoming-events` | Bevorstehende Veranstaltungen anzeigen | — |
| `event-participants` | Vollständige Teilnehmerliste | `event` |
| `club-summary` | Kompaktes Vereins-Dashboard | — |
| `pending-tasks` | Offene Aufgaben nach Dringlichkeit | — |
| `finance-summary` | Finanzüberblick: Konten, Projekte, Saldo | — |
| `member-birthday` | Mitglieder mit Geburtstag in Monat X | `month` |
| `event-create` | Neue Veranstaltung geführt anlegen | — |
| `inventory-overview` | Gesamtes Vereinsinventar zusammenfassen | — |
| `forum-overview` | Aktuelle Foren-Themen und Beiträge | — |

---

## Lösch-Bestätigung

Wenn du eine Löschaktion anforderst (z. B. „Lösche das Mitglied"), fragt der Server — bei Clients mit Elicitation-Unterstützung — vor der Ausführung noch einmal nach. Du siehst einen Dialog mit **Bestätigen / Abbrechen**. Dies schützt vor versehentlichen Löschungen.

---

## Token

Du benötigst deinen persönlichen EasyVerein API-Token. Diesen findest du in EasyVerein unter:

**Einstellungen → API → Token erstellen**

Der Token wird **niemals** vom Server gespeichert. Er wird nur für die Dauer deiner Anfrage weitergeleitet.

---

## Paginierung

EasyVerein-Listenergebnisse sind seitenweise. Claude fragt automatisch nach weiteren Seiten, wenn du nach allen Einträgen fragst. Du kannst auch explizit steuern:

> „Zeige die ersten 20 Mitglieder"  
> „Zeige Seite 2 der Mitgliederliste"

---

## Hinweise

- Der Server verändert **keine** Daten ohne deine explizite Anweisung.
- Fehler aus der EasyVerein API (z. B. Pflichtfeld fehlt) werden als Fehlermeldung im Chat angezeigt.
- Bei Rate-Limiting (HTTP 429) wartet der Server automatisch und wiederholt die Anfrage einmal.
