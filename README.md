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
„OrgID übernehmen“ trägt die `OrgID` in das Feld „Eigener Verein“ ein, „TeamID übernehmen“ die `TeamID` in `mannschaftenID`; danach speichern.
Die Anfrage läuft serverseitig (AJAX-Aktion `ava_searchTeams`, nur für Administratoren).

## Nächster Kampf und letztes Ergebnis (liga-db.de API)

Die Karte neben der Tabelle ("Nächster Kampftag" / "Letztes Ergebnis") wird aus `ava_getLeagueMatches()` (`inc/helpers.php`)
befüllt, 15 Minuten zwischengespeichert. Die Auswahl übernimmt `ava_findNextAndLastMatch()`:
**nächster Kampf** = erste Begegnung ohne Ergebnis, deren Datum heute oder später ist; **letztes Ergebnis** = letzte Begegnung mit Ergebnis.
Parameter: Admin-Menü **Tabelle**, Abschnitt "Kampftage".

**Aufruf:** `GET https://ringen.liga-db.de/api/TeamMatches?saisonLigaID={saisonLigaID}&kampftag={kampftag}&logischerKampftag={logischerKampftag}&mannschaftenID={mannschaftenID}`
(Antwort: `BegegnungsID`, `KampftagIst`, `Beginn`, `HeimMannschaft`, `GastMannschaft`, `PunkteHeimWertung`, `PunkteGastWertung` (leer bis zum Kampf),
`HeimOrganisationsID`, `GastOrganisationsID`, `LogischerKampftag`, ...)

| Parameter | Pflicht | Standard | Bedeutung |
|---|---|---|---|
| `saisonLigaID` | ja | `1257` | Dieselbe Liga-ID wie bei der Tabelle (ein gemeinsames Feld `saisonLigaId`). |
| `mannschaftenID` | nein | `13503` | `TeamID` der eigenen Mannschaft (RWG Alzenau/Kahl). Leer = Begegnungen der ganzen Liga, dann passen nächster Kampf und letztes Ergebnis nicht zum eigenen Verein. Die ID liefert `api/Teams`. |
| `kampftag` | nein | leer | Nur Begegnungen an diesem Datum (`YYYY-MM-DD`). |
| `logischerKampftag` | nein | leer | Nur Begegnungen dieser Spieltag-Nummer. |

`kampftag` und `logischerKampftag` schränken die geladenen Begegnungen ein und damit auch, was die Startseite als nächsten/letzten Kampf findet; für den Normalbetrieb leer lassen.
Das Werkzeug "Begegnungen" im Admin ruft denselben Endpunkt mit allen vier Parametern auf (AJAX-Aktion `ava_searchMatches`) und listet die Ergebnisse.

Die API liefert keinen Austragungsort und keinen Ticket-Link. Der Ort (Einstellung "Name der Halle" unter Vereinsdaten) wird deshalb nur bei Heimkämpfen
angezeigt (Heimverein = Einstellung "Eigener Verein: OrgID"); der Ticket-Button entfällt. Der frühere Beitragstyp "Kampftag" (Dummy-Daten) wurde entfernt.

## Weitere Admin-Werkzeuge und Hinweise (Tabelle)

**Regionen auflisten:** Ein zweites Werkzeug ruft `GET api/Regions?season={season}` auf (`season` optional, leer = alle Regionen)
und zeigt `RegionID`, `RegionName`, `RegionNameShort` und `RegionCode` (AJAX-Aktion `ava_searchRegions`).
Hinweis: `RegionID` entspricht nicht dem `regionalKlassenID`-Parameter von `api/Ligen` (z. B. Hessen = 5 liefert dort einen Fehler).

Zusätzliche Einstellung (kein API-Parameter): **Eigener Verein: OrgID** (Standard `2221` = RWG Alzenau/Kahl)
hebt die passende Tabellenzeile hervor und bestimmt, ob ein Kampf ein Heimkampf ist; leer = keine Hervorhebung.

Spalten auf der Webseite: Pl. = `Platzierung`, Verein = `TeamName`, Diff. = `Differenz`, Punkte = `WertungPlus:WertungMinus`.
Der frühere Beitragstyp "Tabelle" (Dummy-Daten) wurde entfernt; bereits angelegte Einträge können gelöscht werden.
