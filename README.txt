AVA Alzenau Theme
Installation: Design > Themes > Neu hinzufuegen > Theme hochladen > ZIP waehlen > Aktivieren.
Beim Aktivieren werden Demo-Inhalte angelegt (nur beim ersten Mal).
Pflege: Menuepunkte 'Vereinsdaten', 'Trainer', 'Training', 'Tabelle', 'Kampftage', 'Mitgliedschaften', 'Sponsoren'.
Einstellungen > Allgemein > Sprache: Deutsch (fuer deutsche Datumsangaben).

Zweisprachige Seite (Deutsch/Englisch):
1. Plugin "Polylang" installieren und aktivieren (Plugins > Installieren).
2. Einstellungen > Sprachen: Deutsch (Standard) und Englisch hinzufuegen. Der Sprachumschalter (Flaggen) erscheint automatisch in der Kopfzeile.
3. Feste Texte und die Texte unter "Vereinsdaten": Englische Standardtexte sind schon eingebaut. Eigene Aenderungen unter Sprachen > Uebersetzungen (Gruppen "AVA Theme" und "AVA Vereinsdaten").
4. Trainer, Training, Tabelle, Kampftage, Mitgliedschaften, Sponsoren: Eintrag oeffnen und in der Seitenleiste bei Englisch auf das Plus klicken. Zahlen, Zeiten und Wochentage werden automatisch uebernommen, Texte (Rolle, Zitat, Leistungen ...) werden uebersetzt.
5. Menues (Hauptmenue, Footer) und Seiten wie Impressum fuer Englisch separat anlegen (Design > Menues).
6. Unter Sprachen > Einstellungen "Reihenfolge der Seiten/Beitraege synchronisieren" aktivieren, damit die Sortierung in beiden Sprachen gleich bleibt.
Ohne Polylang bleibt die Seite einfach deutsch.

Unit-Tests (nur fuer die Entwicklung, nicht im Theme-ZIP noetig):
Voraussetzung: PHP 8.1 oder neuer und Composer.
1. Im Theme-Ordner: composer install
2. Tests starten: composer test   (oder: vendor/bin/phpunit)
Die Tests liegen in tests/ und laufen ohne WordPress (WordPress-Funktionen werden in tests/bootstrap.php nachgebildet).
Ein einzelner Test: vendor/bin/phpunit --filter testSeedRunsOnlyOnce
Hinweis: Die Ordner tests/ und vendor/ sowie composer.json und phpunit.xml koennen beim Erstellen des ZIPs weggelassen werden.
