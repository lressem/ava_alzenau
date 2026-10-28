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

**Liga automatisch ermitteln:** Das Werkzeug ruft `GET api/Ligen?saison={saison}` (leer = aktuelle Saison) auf und prüft pro Liga
per `GET api/Teams?saisonLigaId=…`, ob die angegebene OrgID dort eine Mannschaft hat. Treffer (Standard: OrgID `2221`,
Regionsfilter `Hessen`) werden mit `SaisonLigaID`, Liga und Mannschaft gelistet; „Übernehmen“ trägt die ID in `saisonLigaId` ein.
Da sich die `saisonLigaId` jede Saison ändert, nutzt man das zu Saisonbeginn (AJAX-Aktion `ava_detectLeague`).
Der Regionsfilter (Teil von `VerbandsregionName`) begrenzt die Zahl der API-Aufrufe.

**Mannschaften einer Liga:** Unter den Einstellungen ruft ein Werkzeug `GET api/Teams?saisonLigaId={saisonLigaId}` auf
(`saisonLigaId` Pflicht, vorbelegt mit dem gespeicherten Wert) und listet `TeamID`, `TeamName`, `OrgID` und `TeamIndex`.
„Übernehmen“ trägt die `OrgID` in das Feld „Eigener Verein“ ein; danach speichern.
Die Anfrage läuft serverseitig (AJAX-Aktion `ava_searchTeams`, nur für Administratoren).

**Regionen auflisten:** Ein zweites Werkzeug ruft `GET api/Regions?season={season}` auf (`season` optional, leer = alle Regionen)
und zeigt `RegionID`, `RegionName`, `RegionNameShort` und `RegionCode` (AJAX-Aktion `ava_searchRegions`).
Hinweis: `RegionID` entspricht nicht dem `regionalKlassenID`-Parameter von `api/Ligen` (z. B. Hessen = 5 liefert dort einen Fehler).

Zusätzliche Einstellung (kein API-Parameter): **Eigener Verein: OrgID** (Standard `2221` = RWG Alzenau/Kahl)
hebt die passende Zeile hervor; leer = keine Hervorhebung.

Spalten auf der Webseite: Pl. = `Platzierung`, Verein = `TeamName`, Diff. = `Differenz`, Punkte = `WertungPlus:WertungMinus`.
Der frühere Beitragstyp "Tabelle" (Dummy-Daten) wurde entfernt; bereits angelegte Einträge können gelöscht werden.
