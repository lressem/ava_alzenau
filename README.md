# ava_alzenau
ava alzenau verein webpage

## Ligatabelle (liga-db.de API)

Die Tabelle im Bereich "Liga & Ergebnisse" der Startseite wird live geladen und 15 Minuten
zwischengespeichert (`ava_getLeagueTable()` in `inc/helpers.php`). Schlägt der Aufruf fehl, bleibt die Tabelle leer.

Die Parameter werden im WordPress-Admin unter **Tabelle** eingestellt (`inc/league-settings.php`, Option `ava_liga`).
API-Dokumentation: https://ringen.liga-db.de/Help?cultureName=en

**Aufruf:** `GET https://ringen.liga-db.de/api/ErgebnisTabelle?saisonLigaId={saisonLigaId}&kampfTagID={kampfTagID}`
(Antwort: JSON-Liste mit `Platzierung`, `TeamName`, `AnzahlKaempfe`, `PlusPunkte`, `MinusPunkte`, `Differenz`,
`WertungPlus`, `WertungMinus`, `OrgID`, `TeamID`, `SaisonLigaID`.)

| Parameter | Pflicht | Standard | Bedeutung |
|---|---|---|---|
| `saisonLigaId` | ja | `1257` | ID der Liga; entspricht der Zahl in der URL `https://www.liga-db.de/ligen/1257`. |
| `kampfTagID` | nein | leer | Tabellenstand zu einem bestimmten Kampftag. Leer = aktueller Stand. Gültige IDs: `GET api/TeamMatchDays?saisonliga={saisonLigaId}`. |

Zusätzliche Einstellung (kein API-Parameter): **Eigener Verein: OrgID** (Standard `2221` = RWG Alzenau/Kahl)
hebt die passende Zeile hervor; leer = keine Hervorhebung.

Spalten auf der Webseite: Pl. = `Platzierung`, Verein = `TeamName`, Diff. = `Differenz`, Punkte = `WertungPlus:WertungMinus`.
Der frühere Beitragstyp "Tabelle" (Dummy-Daten) wurde entfernt; bereits angelegte Einträge können gelöscht werden.
