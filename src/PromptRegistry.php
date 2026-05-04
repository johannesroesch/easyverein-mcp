<?php

declare(strict_types=1);

namespace EasyVerein\Mcp;

class PromptRegistry
{
    private array $prompts = [
        [
            'name'        => 'member-overview',
            'description' => 'Alle Vereinsmitglieder mit Gruppen und Kontaktdaten zusammenfassen.',
            'arguments'   => [],
        ],
        [
            'name'        => 'member-search',
            'description' => 'Mitglied nach Name oder E-Mail suchen.',
            'arguments'   => [
                ['name' => 'query', 'description' => 'Name oder E-Mail des gesuchten Mitglieds', 'required' => true],
            ],
        ],
        [
            'name'        => 'member-onboard',
            'description' => 'Neues Vereinsmitglied geführt anlegen.',
            'arguments'   => [],
        ],
        [
            'name'        => 'open-invoices',
            'description' => 'Alle offenen Rechnungen auflisten und Gesamtbetrag berechnen.',
            'arguments'   => [],
        ],
        [
            'name'        => 'monthly-bookings',
            'description' => 'Buchungsübersicht für einen bestimmten Monat erstellen.',
            'arguments'   => [
                ['name' => 'month', 'description' => 'Monat (1–12)', 'required' => true],
                ['name' => 'year',  'description' => 'Jahr (z. B. 2025)', 'required' => true],
            ],
        ],
        [
            'name'        => 'invoice-for-member',
            'description' => 'Rechnung für ein bestimmtes Mitglied erstellen.',
            'arguments'   => [
                ['name' => 'member', 'description' => 'Name oder E-Mail des Mitglieds', 'required' => true],
            ],
        ],
        [
            'name'        => 'upcoming-events',
            'description' => 'Bevorstehende Veranstaltungen mit Teilnehmerzahl anzeigen.',
            'arguments'   => [],
        ],
        [
            'name'        => 'event-participants',
            'description' => 'Vollständige Teilnehmerliste für eine Veranstaltung anzeigen.',
            'arguments'   => [
                ['name' => 'event', 'description' => 'Name oder ID der Veranstaltung', 'required' => true],
            ],
        ],
        [
            'name'        => 'club-summary',
            'description' => 'Kompaktes Vereins-Dashboard: Organisationsinfos, Mitgliederzahl und offene Aufgaben.',
            'arguments'   => [],
        ],
        [
            'name'        => 'pending-tasks',
            'description' => 'Alle offenen Vereinsaufgaben nach Dringlichkeit auflisten.',
            'arguments'   => [],
        ],
        [
            'name'        => 'finance-summary',
            'description' => 'Finanzüberblick: Bankkonten, Buchungsprojekte und Kontostand.',
            'arguments'   => [],
        ],
        [
            'name'        => 'member-birthday',
            'description' => 'Mitglieder mit Geburtstag in einem bestimmten Monat anzeigen.',
            'arguments'   => [
                ['name' => 'month', 'description' => 'Monat (1–12)', 'required' => true],
            ],
        ],
        [
            'name'        => 'event-create',
            'description' => 'Neue Vereinsveranstaltung geführt anlegen.',
            'arguments'   => [],
        ],
        [
            'name'        => 'inventory-overview',
            'description' => 'Vereinsinventar vollständig zusammenfassen.',
            'arguments'   => [],
        ],
        [
            'name'        => 'forum-overview',
            'description' => 'Aktuelle Foren-Themen und neueste Beiträge anzeigen.',
            'arguments'   => [],
        ],
    ];

    public function list(): array
    {
        return ['prompts' => $this->prompts];
    }

    public function get(string $name, array $arguments): array
    {
        return match ($name) {
            'member-overview'    => $this->memberOverview(),
            'member-search'      => $this->memberSearch($arguments['query'] ?? ''),
            'member-onboard'     => $this->memberOnboard(),
            'open-invoices'      => $this->openInvoices(),
            'monthly-bookings'   => $this->monthlyBookings($arguments['month'] ?? '', $arguments['year'] ?? ''),
            'invoice-for-member' => $this->invoiceForMember($arguments['member'] ?? ''),
            'upcoming-events'    => $this->upcomingEvents(),
            'event-participants' => $this->eventParticipants($arguments['event'] ?? ''),
            'club-summary'       => $this->clubSummary(),
            'pending-tasks'      => $this->pendingTasks(),
            'finance-summary'    => $this->financeSummary(),
            'member-birthday'    => $this->memberBirthday($arguments['month'] ?? ''),
            'event-create'       => $this->eventCreate(),
            'inventory-overview' => $this->inventoryOverview(),
            'forum-overview'     => $this->forumOverview(),
            default              => throw new \InvalidArgumentException("Unknown prompt: $name"),
        };
    }

    public function count(): int
    {
        return count($this->prompts);
    }

    // -------------------------------------------------------------------------

    private function prompt(string $description, string $text): array
    {
        return [
            'description' => $description,
            'messages'    => [
                ['role' => 'user', 'content' => ['type' => 'text', 'text' => $text]],
            ],
        ];
    }

    private function memberOverview(): array
    {
        return $this->prompt(
            'Alle Vereinsmitglieder mit Gruppen und Kontaktdaten zusammenfassen.',
            <<<TEXT
                Erstelle eine strukturierte Übersicht aller Vereinsmitglieder.
                Rufe dazu listMembers auf und fasse die Ergebnisse übersichtlich zusammen
                (Name, E-Mail, Mitgliedsgruppe, Status). Zeige auch die Gesamtanzahl an.
                TEXT
        );
    }

    private function memberSearch(string $query): array
    {
        return $this->prompt(
            'Mitglied "' . $query . '" suchen.',
            'Suche nach Vereinsmitgliedern mit dem Suchbegriff "' . $query . '".' . "\n" .
            "Rufe listMembers mit dem search-Parameter auf und zeige alle Treffer mit\nName, E-Mail und Mitgliedsgruppe an.",
        );
    }

    private function memberOnboard(): array
    {
        return $this->prompt(
            'Neues Vereinsmitglied anlegen.',
            <<<TEXT
                Ich möchte ein neues Vereinsmitglied anlegen.
                Frage mich bitte Schritt für Schritt nach den nötigen Daten:
                1. Vorname und Nachname
                2. E-Mail-Adresse
                3. Gewünschte Mitgliedsgruppe (rufe vorher listMemberGroups auf, damit ich wählen kann)

                Lege das Mitglied danach mit createMember an und bestätige die erfolgreiche Erstellung.
                TEXT
        );
    }

    private function openInvoices(): array
    {
        return $this->prompt(
            'Alle offenen Rechnungen auflisten.',
            <<<TEXT
                Zeige eine Übersicht aller offenen Rechnungen des Vereins.
                Rufe listInvoices auf und liste Rechnungsnummer, Empfänger, Betrag und
                Fälligkeitsdatum auf. Berechne die Gesamtsumme aller offenen Beträge.
                TEXT
        );
    }

    private function monthlyBookings(string $month, string $year): array
    {
        return $this->prompt(
            "Buchungsübersicht $month/$year.",
            <<<TEXT
                Erstelle eine Buchungsübersicht für $month/$year.
                Rufe listBookings auf und fasse die Ergebnisse zusammen:
                - Summe Einnahmen
                - Summe Ausgaben
                - Saldo

                Gruppiere nach Buchungstyp, wenn möglich.
                TEXT
        );
    }

    private function invoiceForMember(string $member): array
    {
        return $this->prompt(
            'Rechnung für "' . $member . '" erstellen.',
            'Ich möchte eine Rechnung für "' . $member . '" erstellen.' . "\n" .
            "1. Suche das Mitglied zuerst mit listMembers (search-Parameter)\n" .
            "2. Frage mich nach Rechnungsposition, Betrag und Fälligkeitsdatum\n" .
            '3. Erstelle die Rechnung mit createInvoice und bestätige die Erstellung',
        );
    }

    private function upcomingEvents(): array
    {
        return $this->prompt(
            'Bevorstehende Veranstaltungen anzeigen.',
            <<<TEXT
                Zeige alle bevorstehenden Vereinsveranstaltungen.
                Rufe listEvents auf und zeige für jedes Event: Name, Datum, Ort und
                aktuelle Teilnehmerzahl. Sortiere nach Datum aufsteigend.
                TEXT
        );
    }

    private function eventParticipants(string $event): array
    {
        return $this->prompt(
            'Teilnehmerliste für "' . $event . '".',
            'Zeige die vollständige Teilnehmerliste für die Veranstaltung "' . $event . '".' . "\n" .
            "1. Suche die Veranstaltung mit listEvents\n" .
            "2. Rufe listParticipations für die gefundene Event-ID auf\n" .
            '3. Zeige Name und Anmeldestatus jedes Teilnehmers an',
        );
    }

    private function clubSummary(): array
    {
        return $this->prompt(
            'Kompaktes Vereins-Dashboard.',
            <<<TEXT
                Erstelle eine kompakte Vereinszusammenfassung:
                1. Rufe getOrganization auf für Vereinsname und Kontaktdaten
                2. Rufe listMembers auf für die Gesamtmitgliederzahl
                3. Rufe listTasks auf für offene Aufgaben

                Fasse alles in einem übersichtlichen Dashboard zusammen.
                TEXT
        );
    }

    private function pendingTasks(): array
    {
        return $this->prompt(
            'Alle offenen Vereinsaufgaben anzeigen.',
            <<<TEXT
                Zeige alle offenen Aufgaben des Vereins.
                Rufe listTasks auf und liste Aufgabentitel, Beschreibung, Fälligkeit und
                zugewiesene Person auf. Sortiere nach Dringlichkeit.
                TEXT
        );
    }

    private function financeSummary(): array
    {
        return $this->prompt(
            'Finanzüberblick des Vereins.',
            <<<TEXT
                Erstelle einen kompakten Finanzüberblick des Vereins:
                1. Rufe listBankAccounts auf – zeige alle Konten mit Name und Kontonummer
                2. Rufe listBookingProjects auf – zeige aktive Buchungsprojekte
                3. Rufe listBookings auf (limit=50) – berechne Einnahmen- und Ausgabensumme

                Fasse alles übersichtlich zusammen: Konten, Projekte, aktueller Saldo.
                TEXT
        );
    }

    private function memberBirthday(string $month): array
    {
        return $this->prompt(
            "Mitglieder mit Geburtstag im Monat $month.",
            'Zeige alle Vereinsmitglieder, die im Monat ' . $month . ' Geburtstag haben.' . "\n" .
            "Rufe listMembers auf (ggf. mit mehreren Seiten) und filtere nach Mitgliedern,\n" .
            "deren Geburtsdatum in den Monat $month fällt.\n" .
            'Liste Name, Geburtsdatum und E-Mail-Adresse auf. Sortiere nach Tag aufsteigend.',
        );
    }

    private function eventCreate(): array
    {
        return $this->prompt(
            'Neue Vereinsveranstaltung anlegen.',
            <<<TEXT
                Ich möchte eine neue Vereinsveranstaltung anlegen.
                Frage mich bitte Schritt für Schritt nach den nötigen Daten:
                1. Name der Veranstaltung
                2. Datum und Uhrzeit (Beginn und Ende)
                3. Ort (rufe vorher listLocations auf, damit ich wählen oder einen neuen Ort angeben kann)
                4. Kurze Beschreibung (optional)
                5. Maximale Teilnehmerzahl (optional)

                Lege die Veranstaltung danach mit createEvent an und bestätige die Erstellung.
                TEXT
        );
    }

    private function inventoryOverview(): array
    {
        return $this->prompt(
            'Vereinsinventar zusammenfassen.',
            <<<TEXT
                Erstelle eine vollständige Inventarübersicht des Vereins.
                Rufe listInventoryObjects auf (ggf. mehrere Seiten) und liste alle Objekte mit
                Name, Beschreibung und Anzahl auf. Zeige die Gesamtanzahl aller Inventarposten.
                TEXT
        );
    }

    private function forumOverview(): array
    {
        return $this->prompt(
            'Forum-Übersicht anzeigen.',
            <<<TEXT
                Zeige eine Übersicht der aktuellen Vereinsdiskussionen im Forum.
                1. Rufe listForums auf – zeige alle verfügbaren Foren
                2. Rufe listTopics auf – zeige die neuesten Themen mit Titel und Erstellungsdatum
                3. Rufe listPosts auf (limit=5) – zeige die neuesten Beiträge

                Fasse zusammen, was gerade diskutiert wird.
                TEXT
        );
    }
}
