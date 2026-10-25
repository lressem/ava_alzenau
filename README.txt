AVA Alzenau Theme
Installation: Design > Themes > Neu hinzufuegen > Theme hochladen > ZIP waehlen > Aktivieren.
Beim Aktivieren werden Demo-Inhalte angelegt (nur beim ersten Mal).
Pflege: Menuepunkte 'Vereinsdaten', 'Trainer', 'Training', 'Tabelle', 'Kampftage', 'Mitgliedschaften', 'Sponsoren'.
Einstellungen > Allgemein > Sprache: Deutsch (fuer deutsche Datumsangaben).

Unit-Tests (nur fuer die Entwicklung, nicht im Theme-ZIP noetig):
Voraussetzung: PHP 8.1 oder neuer und Composer.
1. Im Theme-Ordner: composer install
2. Tests starten: composer test   (oder: vendor/bin/phpunit)
Die Tests liegen in tests/ und laufen ohne WordPress (WordPress-Funktionen werden in tests/bootstrap.php nachgebildet).
Ein einzelner Test: vendor/bin/phpunit --filter testSeedRunsOnlyOnce
Hinweis: Die Ordner tests/ und vendor/ sowie composer.json und phpunit.xml koennen beim Erstellen des ZIPs weggelassen werden.
