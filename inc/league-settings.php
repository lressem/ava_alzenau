<?php
/**
 * Admin page "Tabelle": parameters of the liga-db.de calls that provide the
 * league standings (api/ErgebnisTabelle) and the match days (api/TeamMatches).
 */
if (!defined('ABSPATH')) exit;

/**
 * Definition of the league settings.
 *
 * Per setting: key => [label, input type, default value, help text, section heading].
 * Most settings are query parameters of an API endpoint (the heading names it);
 * 'eigene_org_id' only controls what is highlighted/shown on the website.
 *
 * @return array
 */
function ava_getLeagueSettingFields() {
    $tableSection = 'Tabelle: GET api/ErgebnisTabelle';
    $matchSection = 'Kampftage: GET api/TeamMatches';

    return [
        'saison_liga_id' => ['saisonLigaId (Pflicht)', 'number', '1257', 'ID der Liga. Steht in der Adresse bei liga-db.de, z. B. https://www.liga-db.de/ligen/1257. Gilt auch für die Kampftage.', $tableSection],
        'kampf_tag_id'   => ['kampfTagID (optional)', 'number', '', 'Tabellenstand zu diesem Kampftag. Leer = aktueller Tabellenstand. Die IDs liefert api/TeamMatchDays?saisonliga={saisonLigaId}.', $tableSection],

        'mannschaften_id'    => ['mannschaftenID (optional, empfohlen)', 'number', '13503', 'TeamID der eigenen Mannschaft (RWG Alzenau/Kahl = 13503). Leer = Begegnungen der ganzen Liga, dann passen „nächster Kampf“ und „letztes Ergebnis“ nicht mehr zum eigenen Verein.', $matchSection],
        'kampftag'           => ['kampftag (optional)', 'date', '', 'Nur Begegnungen an diesem Datum. Leer = alle. Achtung: Auf der Startseite werden nächster Kampf und letztes Ergebnis nur aus den geladenen Begegnungen bestimmt, ein Datum schränkt sie ein.', $matchSection],
        'logischer_kampftag' => ['logischerKampftag (optional)', 'number', '', 'Nur Begegnungen dieses logischen Kampftags (Spieltag-Nummer 1, 2, ...). Leer = alle. Gleicher Hinweis wie bei kampftag.', $matchSection],

        'eigene_org_id'  => ['Eigener Verein: OrgID (nur Anzeige)', 'number', '2221', 'Die Tabellenzeile mit dieser OrgID wird hervorgehoben; bei Heimkämpfen wird der Ort angezeigt (RWG Alzenau/Kahl = 2221). Leer = keine Hervorhebung.', 'Darstellung auf der Webseite'],

        'manual_table' => [
            'Manuelle Tabelle',
            'manual_table',
            [],
            'Ersatzdaten, falls liga-db.de nicht erreichbar ist. Trage eine Mannschaft pro Zeile ein.',
            'Manuelle Ersatzdaten',
        ],
        'manual_matches' => [
            'Manuelle Kampftage',
            'manual_matches',
            [],
            'Ersatzdaten, falls liga-db.de nicht erreichbar ist. Trage eine Begegnung pro Zeile ein; kommende Kämpfe können ohne Ergebnis gespeichert werden.',
            'Manuelle Ersatzdaten',
        ],
        'test_api_unavailable' => [
            'Liga-DB-Ausfall simulieren',
            'check',
            '',
            'Nur zum Testen: Tabellen- und Kampftag-API-Aufrufe werden als fehlgeschlagen behandelt, damit die manuellen Ersatzdaten angezeigt werden. Danach wieder deaktivieren.',
            'Testmodus',
        ],
    ];
}

/**
 * Returns one league setting (saved value, or the default if nothing is saved).
 *
 * @param string $key Key from ava_getLeagueSettingFields().
 * @return mixed
 */
function ava_getLeagueOption($key) {
    $fields = ava_getLeagueSettingFields();
    $saved = get_option('ava_liga', []);
    if (is_array($saved) && array_key_exists($key, $saved)) return $saved[$key];
    return $fields[$key][2] ?? '';
}

/**
 * Sanitizes the submitted league settings. 'number' fields are positive
 * integers or empty, 'date' fields are YYYY-MM-DD or empty; an empty
 * saisonLigaId falls back to its default.
 *
 * @param array $input Raw submitted values (key => value).
 * @return array
 */
function ava_sanitizeLeagueSettings($input) {
    $clean = [];
    foreach (ava_getLeagueSettingFields() as $key => $field) {
        $value = isset($input[$key]) ? $input[$key] : '';
        if ($field[1] === 'manual_table') {
            $clean[$key] = ava_parseManualLeagueTable($value);
            $invalidRows = ava_countInvalidManualLeagueRows($value, $key);
            if ($invalidRows > 0 && function_exists('add_settings_error')) {
                add_settings_error('ava_liga', 'ava_invalid_' . $key, sprintf('%s: %d unvollständige oder ungültige Zeile(n) wurden ignoriert.', $field[0], $invalidRows), 'warning');
            }
        } elseif ($field[1] === 'manual_matches') {
            $clean[$key] = ava_parseManualLeagueMatches($value);
            $invalidRows = ava_countInvalidManualLeagueRows($value, $key);
            if ($invalidRows > 0 && function_exists('add_settings_error')) {
                add_settings_error('ava_liga', 'ava_invalid_' . $key, sprintf('%s: %d unvollständige oder ungültige Zeile(n) wurden ignoriert.', $field[0], $invalidRows), 'warning');
            }
        } elseif ($field[1] === 'textarea') {
            $clean[$key] = sanitize_textarea_field(is_scalar($value) ? (string) $value : '');
            $invalidRows = ava_countInvalidManualLeagueRows($clean[$key], $key);
            if ($invalidRows > 0 && function_exists('add_settings_error')) {
                add_settings_error(
                    'ava_liga',
                    'ava_invalid_' . $key,
                    sprintf('%s: %d ungültige Zeile(n) werden auf der Website ignoriert.', $field[0], $invalidRows),
                    'warning'
                );
            }
        } elseif ($field[1] === 'date') {
            $clean[$key] = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value) ? (string) $value : '';
        } elseif ($field[1] === 'check') {
            $clean[$key] = $value === '1' ? '1' : '';
        } else {
            $number = absint($value);
            $clean[$key] = $number > 0 ? (string) $number : ($key === 'saison_liga_id' ? $field[2] : '');
        }
    }
    return $clean;
}

/** Whether league API failures are being simulated for fallback testing. */
function ava_isLeagueApiTestMode() {
    $settings = get_option('ava_liga', []);
    return is_array($settings) && ($settings['test_api_unavailable'] ?? '') === '1';
}

/**
 * Parses the manually entered standings used when the league API is unavailable.
 *
 * @param mixed $data Structured rows or legacy pipe-separated text.
 * @return array[]
 */
function ava_parseManualLeagueTable($data) {
    $rows = [];
    if (is_array($data)) {
        $inputRows = $data;
    } else {
        $inputRows = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $data) as $line) {
            if (trim($line) === '') continue;
            $columns = array_map('trim', explode('|', $line));
            if (count($columns) !== 6) {
                $inputRows[] = [];
                continue;
            }
            $inputRows[] = [
                'Platzierung' => $columns[0],
                'TeamName' => $columns[1],
                'Differenz' => $columns[2],
                'WertungPlus' => $columns[3],
                'WertungMinus' => $columns[4],
                'OrgID' => $columns[5],
            ];
        }
    }

    foreach ($inputRows as $inputRow) {
        if (!is_array($inputRow)) continue;
        $row = [];
        foreach (['Platzierung', 'TeamName', 'Differenz', 'WertungPlus', 'WertungMinus', 'OrgID'] as $key) {
            $value = $inputRow[$key] ?? '';
            $row[$key] = is_scalar($value) ? trim((string) $value) : '';
        }
        if (implode('', $row) === '') continue;
        if ($row['TeamName'] === '' || !preg_match('/^\d+$/', $row['Platzierung'])) continue;
        if (!is_numeric($row['Differenz']) || !is_numeric($row['WertungPlus']) || !is_numeric($row['WertungMinus'])) continue;
        if ($row['OrgID'] !== '' && !preg_match('/^\d+$/', $row['OrgID'])) continue;

        $rows[] = [
            'Platzierung' => (int) $row['Platzierung'],
            'TeamName' => sanitize_text_field($row['TeamName']),
            'Differenz' => 0 + $row['Differenz'],
            'WertungPlus' => 0 + $row['WertungPlus'],
            'WertungMinus' => 0 + $row['WertungMinus'],
            'OrgID' => (int) $row['OrgID'],
        ];
    }
    return $rows;
}

/**
 * Parses manually entered matches used when the league API is unavailable.
 *
 * @param mixed $data Structured rows or legacy pipe-separated text.
 * @return array[]
 */
function ava_parseManualLeagueMatches($data) {
    $matches = [];
    if (is_array($data)) {
        $inputRows = $data;
    } else {
        $inputRows = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $data) as $line) {
            if (trim($line) === '') continue;
            $columns = array_map('trim', explode('|', $line));
            if (count($columns) !== 8) {
                $inputRows[] = [];
                continue;
            }
            $inputRows[] = [
                'KampftagIst' => $columns[0],
                'Beginn' => $columns[1],
                'HeimMannschaft' => $columns[2],
                'GastMannschaft' => $columns[3],
                'PunkteHeimWertung' => $columns[4],
                'PunkteGastWertung' => $columns[5],
                'HeimOrganisationsID' => $columns[6],
                'GastOrganisationsID' => $columns[7],
            ];
        }
    }

    foreach ($inputRows as $inputRow) {
        if (!is_array($inputRow)) continue;
        $row = [];
        foreach (['KampftagIst', 'Beginn', 'HeimMannschaft', 'GastMannschaft', 'PunkteHeimWertung', 'PunkteGastWertung', 'HeimOrganisationsID', 'GastOrganisationsID'] as $key) {
            $value = $inputRow[$key] ?? '';
            $row[$key] = is_scalar($value) ? trim((string) $value) : '';
        }
        if (implode('', $row) === '') continue;
        if ($row['HeimMannschaft'] === '' || $row['GastMannschaft'] === '') continue;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['KampftagIst'])) continue;
        $dateParts = explode('-', $row['KampftagIst']);
        if (!checkdate((int) $dateParts[1], (int) $dateParts[2], (int) $dateParts[0])) continue;
        if ($row['Beginn'] !== '') {
            if (!preg_match('/^(\d{1,2}):(\d{2})$/', $row['Beginn'], $timeParts)) continue;
            if ((int) $timeParts[1] > 23 || (int) $timeParts[2] > 59) continue;
        }
        if (($row['PunkteHeimWertung'] !== '' && !is_numeric($row['PunkteHeimWertung'])) || ($row['PunkteGastWertung'] !== '' && !is_numeric($row['PunkteGastWertung']))) continue;
        if (($row['PunkteHeimWertung'] === '') !== ($row['PunkteGastWertung'] === '')) continue;
        if ($row['HeimOrganisationsID'] !== '' && !preg_match('/^\d+$/', $row['HeimOrganisationsID'])) continue;
        if ($row['GastOrganisationsID'] !== '' && !preg_match('/^\d+$/', $row['GastOrganisationsID'])) continue;

        $matches[] = [
            'KampftagIst' => $row['KampftagIst'],
            'Beginn' => $row['Beginn'],
            'HeimMannschaft' => sanitize_text_field($row['HeimMannschaft']),
            'GastMannschaft' => sanitize_text_field($row['GastMannschaft']),
            'PunkteHeimWertung' => $row['PunkteHeimWertung'] === '' ? null : 0 + $row['PunkteHeimWertung'],
            'PunkteGastWertung' => $row['PunkteGastWertung'] === '' ? null : 0 + $row['PunkteGastWertung'],
            'HeimOrganisationsID' => (int) $row['HeimOrganisationsID'],
            'GastOrganisationsID' => (int) $row['GastOrganisationsID'],
        ];
    }
    return $matches;
}

/** Counts non-empty manual data rows rejected by the corresponding parser. */
function ava_countInvalidManualLeagueRows($data, $key) {
    if (is_array($data)) {
        $rows = array_filter($data, function ($row) {
            return is_array($row) && count(array_filter($row, function ($value) {
                return is_scalar($value) && trim((string) $value) !== '';
            })) > 0;
        });
    } else {
        $rows = array_filter(preg_split('/\r\n|\r|\n/', (string) $data), function ($line) {
            return trim($line) !== '';
        });
    }
    $valid = $key === 'manual_table'
        ? ava_parseManualLeagueTable($data)
        : ava_parseManualLeagueMatches($data);
    return count($rows) - count($valid);
}

/** Prints the repeatable row editor for manual standings or matches. */
function ava_renderManualLeagueRows($inputName, $key, $value) {
    if ($key === 'manual_table') {
        $rows = ava_parseManualLeagueTable($value);
        $columns = [
            'Platzierung' => ['Platz', 'number'],
            'TeamName' => ['Mannschaft', 'text'],
            'Differenz' => ['Differenz', 'number'],
            'WertungPlus' => ['Punkte Plus', 'number'],
            'WertungMinus' => ['Punkte Minus', 'number'],
            'OrgID' => ['OrgID', 'number'],
        ];
    } else {
        $rows = ava_parseManualLeagueMatches($value);
        $columns = [
            'KampftagIst' => ['Datum', 'date'],
            'Beginn' => ['Beginn', 'time'],
            'HeimMannschaft' => ['Heim', 'text'],
            'GastMannschaft' => ['Gast', 'text'],
            'PunkteHeimWertung' => ['Punkte Heim', 'number'],
            'PunkteGastWertung' => ['Punkte Gast', 'number'],
            'HeimOrganisationsID' => ['Heim-OrgID', 'number'],
            'GastOrganisationsID' => ['Gast-OrgID', 'number'],
        ];
    }

    $wrapper = 'ava-' . $key;
    printf('<div class="ava-manual-rows" id="%s" data-next-index="%d">', esc_attr($wrapper), count($rows));
    echo '<div style="overflow-x:auto"><table class="widefat striped"><thead><tr>';
    foreach ($columns as $column) {
        printf('<th scope="col">%s</th>', esc_html($column[0]));
    }
    echo '<th scope="col">Aktion</th></tr></thead><tbody>';
    foreach ($rows as $index => $row) {
        ava_renderManualLeagueRow($inputName, $index, $row, $columns);
    }
    echo '</tbody></table></div>';
    printf('<p><button type="button" class="button ava-add-manual-row" data-target="%s">Zeile hinzufügen</button></p>', esc_attr($wrapper));
    echo '<template class="ava-manual-row-template">';
    ava_renderManualLeagueRow($inputName, '__index__', [], $columns);
    echo '</template></div>';
}

/** Prints one row in a repeatable manual league-data editor. */
function ava_renderManualLeagueRow($inputName, $index, $row, $columns) {
    echo '<tr>';
    foreach ($columns as $key => $column) {
        $name = $inputName . '[' . $index . '][' . $key . ']';
        $value = isset($row[$key]) && $row[$key] !== null ? $row[$key] : '';
        printf(
            '<td><input type="%s" name="%s" value="%s" class="regular-text" aria-label="%s"></td>',
            esc_attr($column[1]),
            esc_attr($name),
            esc_attr($value),
            esc_attr($column[0])
        );
    }
    echo '<td><button type="button" class="button-link-delete ava-remove-manual-row">Entfernen</button></td></tr>';
}

add_action('admin_init', function () {
    register_setting('ava_liga', 'ava_liga', ['sanitize_callback' => 'ava_sanitizeLeagueSettings']);
});

add_action('admin_menu', function () {
    add_menu_page('Tabelle', 'Tabelle', 'manage_options', 'ava_liga', 'ava_renderLeagueSettingsPage', 'dashicons-editor-ol', 4);
});

// AJAX: finds the leagues of a season (api/Ligen) in which a club (OrgID) has a team (api/Teams).
add_action('wp_ajax_ava_detectLeague', function () {
    check_ajax_referer('ava_detectLeague', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Keine Berechtigung', 403);

    $orgId = absint($_POST['orgId'] ?? 0);
    if ($orgId === 0) wp_send_json_error('OrgID ist ein Pflichtfeld');

    $queryArgs = [];
    $season = absint($_POST['saison'] ?? 0);
    if ($season > 0) $queryArgs['saison'] = $season;
    $regionFilter = sanitize_text_field(wp_unslash($_POST['region'] ?? ''));

    $leagues = ava_fetchLigaDb('Ligen', $queryArgs);
    if (is_wp_error($leagues)) wp_send_json_error($leagues->get_error_message());

    // One Teams call per league, so the region filter keeps the number of calls small.
    $matches = [];
    foreach ($leagues as $league) {
        if ($regionFilter !== '' && stripos($league['VerbandsregionName'] ?? '', $regionFilter) === false) continue;

        $teams = ava_fetchLigaDb('Teams', ['saisonLigaId' => (int) $league['SaisonLigaID']]);
        if (is_wp_error($teams)) continue;
        foreach ($teams as $team) {
            if ((int) ($team['OrgID'] ?? 0) === $orgId) {
                $matches[] = $league + ['TeamName' => $team['TeamName'] ?? ''];
                break;
            }
        }
    }
    wp_send_json_success($matches);
});

// AJAX: lists the teams of a league (api/Teams) for the team tool on the "Tabelle" page.
add_action('wp_ajax_ava_searchTeams', function () {
    check_ajax_referer('ava_searchTeams', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Keine Berechtigung', 403);

    $saisonLigaId = absint($_POST['saisonLigaId'] ?? 0);
    if ($saisonLigaId === 0) wp_send_json_error('saisonLigaId ist ein Pflichtfeld');

    $teams = ava_fetchLigaDb('Teams', ['saisonLigaId' => $saisonLigaId]);
    if (is_wp_error($teams)) wp_send_json_error($teams->get_error_message());
    wp_send_json_success($teams);
});

// AJAX: lists matches (api/TeamMatches) for the match tool on the "Tabelle" page.
add_action('wp_ajax_ava_searchMatches', function () {
    check_ajax_referer('ava_searchMatches', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Keine Berechtigung', 403);

    $saisonLigaId = absint($_POST['saisonLigaID'] ?? 0);
    if ($saisonLigaId === 0) wp_send_json_error('saisonLigaID ist ein Pflichtfeld');

    $queryArgs = ['saisonLigaID' => $saisonLigaId];
    $matchDate = sanitize_text_field(wp_unslash($_POST['kampftag'] ?? ''));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $matchDate)) $queryArgs['kampftag'] = $matchDate;
    $logicalMatchDay = absint($_POST['logischerKampftag'] ?? 0);
    if ($logicalMatchDay > 0) $queryArgs['logischerKampftag'] = $logicalMatchDay;
    $teamId = absint($_POST['mannschaftenID'] ?? 0);
    if ($teamId > 0) $queryArgs['mannschaftenID'] = $teamId;

    $matches = ava_fetchLigaDb('TeamMatches', $queryArgs);
    if (is_wp_error($matches)) wp_send_json_error($matches->get_error_message());
    wp_send_json_success($matches);
});

// AJAX: lists the regions (api/Regions), optionally only those of one season.
add_action('wp_ajax_ava_searchRegions', function () {
    check_ajax_referer('ava_searchRegions', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Keine Berechtigung', 403);

    $queryArgs = [];
    $season = absint($_POST['season'] ?? 0);
    if ($season > 0) $queryArgs['season'] = $season;

    $regions = ava_fetchLigaDb('Regions', $queryArgs);
    if (is_wp_error($regions)) wp_send_json_error($regions->get_error_message());
    wp_send_json_success($regions);
});

/** Prints the "Tabelle" settings page. */
function ava_renderLeagueSettingsPage() {
    ?>
    <div class="wrap">
        <h1>Tabelle</h1>
        <p>
            Tabelle und Kampftage auf der Startseite werden live von liga-db.de geladen
            (<code>GET https://ringen.liga-db.de/api/ErgebnisTabelle</code> und <code>api/TeamMatches</code>)
            und 15 Minuten zwischengespeichert. Hier stellst du die Parameter der Aufrufe ein.
            Wenn die API nicht erreichbar ist, werden die unten gepflegten manuellen Ersatzdaten verwendet.
        </p>
        <?php settings_errors('ava_liga'); ?>
        <form method="post" action="options.php">
    <?php
    settings_fields('ava_liga');

    $currentSection = '';
    foreach (ava_getLeagueSettingFields() as $key => $field) {
        // A new section heading starts a new table.
        if ($field[4] !== $currentSection) {
            if ($currentSection) echo '</table>';
            $currentSection = $field[4];
            printf('<h2>%s</h2><table class="form-table">', esc_html($currentSection));
        }
        printf('<tr><th>%s</th><td>', esc_html($field[0]));
        if ($field[1] === 'manual_table' || $field[1] === 'manual_matches') {
            ava_renderManualLeagueRows('ava_liga[' . $key . ']', $key, ava_getLeagueOption($key));
        } else {
            ava_renderField('ava_liga[' . $key . ']', $field, ava_getLeagueOption($key));
        }
        printf('<p class="description">%s</p></td></tr>', esc_html($field[3]));
    }
    echo '</table>';
    submit_button();
    echo '</form>';
    ?>
    <script>
    jQuery(function ($) {
        $(".ava-add-manual-row").on("click", function () {
            var wrapper = $("#" + $(this).data("target"));
            var template = wrapper.find(".ava-manual-row-template")[0];
            var index = parseInt(wrapper.attr("data-next-index"), 10);
            var row = document.importNode(template.content, true);
            $(row).find("input").each(function () {
                this.name = this.name.replace("__index__", index);
            });
            wrapper.find("tbody").append(row);
            wrapper.attr("data-next-index", index + 1);
        });

        $(".ava-manual-rows").on("click", ".ava-remove-manual-row", function () {
            $(this).closest("tr").remove();
        });
    });
    </script>
    <?php

    ava_renderLeagueDetectTool();
    ava_renderTeamSearchTool();
    ava_renderMatchSearchTool();
    ava_renderRegionSearchTool();
    echo '</div>';
}

/** Prints the match list tool: queries api/TeamMatches and lists the matches. */
function ava_renderMatchSearchTool() {
    ?>
    <hr>
    <h2>Begegnungen</h2>
    <p>
        Ruft <code>GET api/TeamMatches?saisonLigaID={saisonLigaID}&amp;kampftag={kampftag}&amp;logischerKampftag={logischerKampftag}&amp;mannschaftenID={mannschaftenID}</code>
        auf und listet die Begegnungen. Nur saisonLigaID ist Pflicht; die Felder sind mit den gespeicherten Werten vorbelegt.
    </p>
    <p>
        <label>saisonLigaID <input type="number" id="ava-matches-liga" class="small-text" value="<?php echo esc_attr(ava_getLeagueOption('saison_liga_id')); ?>"></label>
        <label>kampftag <input type="date" id="ava-matches-date" value="<?php echo esc_attr(ava_getLeagueOption('kampftag')); ?>"></label>
        <label>logischerKampftag <input type="number" id="ava-matches-logical" class="small-text" value="<?php echo esc_attr(ava_getLeagueOption('logischer_kampftag')); ?>"></label>
        <label>mannschaftenID <input type="number" id="ava-matches-team" class="small-text" value="<?php echo esc_attr(ava_getLeagueOption('mannschaften_id')); ?>"></label>
        <button type="button" class="button" id="ava-matches-run">Begegnungen laden</button>
    </p>
    <p id="ava-matches-status"></p>
    <table class="widefat striped" id="ava-matches-results" style="display:none;max-width:1000px">
        <thead>
            <tr><th>BegegnungsID</th><th>LogischerKampftag</th><th>KampftagIst</th><th>Beginn</th><th>Heim</th><th>Gast</th><th>Ergebnis</th></tr>
        </thead>
        <tbody></tbody>
    </table>
    <script>
    jQuery(function ($) {
        var nonce = <?php echo wp_json_encode(wp_create_nonce('ava_searchMatches')); ?>;
        var status = $("#ava-matches-status");
        var results = $("#ava-matches-results");

        $("#ava-matches-run").on("click", function () {
            status.text("Lade ...");
            results.hide().find("tbody").empty();
            $.post(ajaxurl, {
                action: "ava_searchMatches",
                nonce: nonce,
                saisonLigaID: $("#ava-matches-liga").val(),
                kampftag: $("#ava-matches-date").val(),
                logischerKampftag: $("#ava-matches-logical").val(),
                mannschaftenID: $("#ava-matches-team").val()
            }).done(function (response) {
                if (!response.success) { status.text("Fehler: " + response.data); return; }
                status.text(response.data.length + " Begegnungen gefunden.");
                $.each(response.data, function (i, match) {
                    var played = match.PunkteHeimWertung !== null && match.PunkteGastWertung !== null;
                    $("<tr>")
                        .append($("<td>").text(match.BegegnungsID))
                        .append($("<td>").text(match.LogischerKampftag))
                        .append($("<td>").text(String(match.KampftagIst).substring(0, 10)))
                        .append($("<td>").text(match.Beginn))
                        .append($("<td>").text(match.HeimMannschaft))
                        .append($("<td>").text(match.GastMannschaft))
                        .append($("<td>").text(played ? match.PunkteHeimWertung + " : " + match.PunkteGastWertung : "-"))
                        .appendTo(results.find("tbody"));
                });
                results.show();
            }).fail(function () { status.text("Anfrage fehlgeschlagen."); });
        });
    });
    </script>
    <?php
}

/** Prints the region list tool: queries api/Regions and lists all regions. */
function ava_renderRegionSearchTool() {
    ?>
    <hr>
    <h2>Regionen auflisten</h2>
    <p>
        Ruft <code>GET api/Regions?season={season}</code> auf und listet die Regionen
        (leere <code>season</code> = alle Regionen).
    </p>
    <p>
        <label>season <input type="number" id="ava-regions-season" class="small-text"></label>
        <button type="button" class="button" id="ava-regions-run">Regionen laden</button>
    </p>
    <p id="ava-regions-status"></p>
    <table class="widefat striped" id="ava-regions-results" style="display:none;max-width:800px">
        <thead><tr><th>RegionID</th><th>RegionName</th><th>RegionNameShort</th><th>RegionCode</th></tr></thead>
        <tbody></tbody>
    </table>
    <script>
    jQuery(function ($) {
        var nonce = <?php echo wp_json_encode(wp_create_nonce('ava_searchRegions')); ?>;
        var status = $("#ava-regions-status");
        var results = $("#ava-regions-results");

        $("#ava-regions-run").on("click", function () {
            status.text("Lade ...");
            results.hide().find("tbody").empty();
            $.post(ajaxurl, {
                action: "ava_searchRegions",
                nonce: nonce,
                season: $("#ava-regions-season").val()
            }).done(function (response) {
                if (!response.success) { status.text("Fehler: " + response.data); return; }
                status.text(response.data.length + " Regionen gefunden.");
                $.each(response.data, function (i, region) {
                    $("<tr>")
                        .append($("<td>").text(region.RegionID))
                        .append($("<td>").text(region.RegionName))
                        .append($("<td>").text(region.RegionNameShort))
                        .append($("<td>").text(region.RegionCode))
                        .appendTo(results.find("tbody"));
                });
                results.show();
            }).fail(function () { status.text("Anfrage fehlgeschlagen."); });
        });
    });
    </script>
    <?php
}

/**
 * Prints the league detection tool: finds the league of the current season in
 * which the club plays, so the saisonLigaId can be updated every season.
 * "Übernehmen" only fills the saisonLigaId field above; saving is up to the user.
 */
function ava_renderLeagueDetectTool() {
    ?>
    <hr>
    <h2>Liga automatisch ermitteln</h2>
    <p>
        Lädt mit <code>GET api/Ligen?saison={saison}</code> die Ligen der Saison und prüft per
        <code>GET api/Teams?saisonLigaId=…</code>, in welcher davon der Verein (OrgID) spielt.
        So findest du zu Saisonbeginn die neue saisonLigaId. Der Regionsfilter (Teil von VerbandsregionName)
        verkürzt die Suche, da pro Liga ein Aufruf nötig ist.
    </p>
    <p>
        <label>OrgID
            <input type="number" id="ava-detect-org" class="small-text" value="<?php echo esc_attr(ava_getLeagueOption('eigene_org_id')); ?>">
        </label>
        <label>saison (leer = aktuelle Saison) <input type="number" id="ava-detect-saison" class="small-text"></label>
        <label>Region enthält <input type="text" id="ava-detect-region" class="regular-text" value="Hessen"></label>
        <button type="button" class="button" id="ava-detect-run">Liga ermitteln</button>
    </p>
    <p id="ava-detect-status"></p>
    <table class="widefat striped" id="ava-detect-results" style="display:none;max-width:800px">
        <thead><tr><th>SaisonLigaID</th><th>Liga</th><th>Region</th><th>Mannschaft</th><th></th></tr></thead>
        <tbody></tbody>
    </table>
    <script>
    jQuery(function ($) {
        var nonce = <?php echo wp_json_encode(wp_create_nonce('ava_detectLeague')); ?>;
        var status = $("#ava-detect-status");
        var results = $("#ava-detect-results");

        $("#ava-detect-run").on("click", function () {
            status.text("Suche läuft, das kann einige Sekunden dauern ...");
            results.hide().find("tbody").empty();
            $.post(ajaxurl, {
                action: "ava_detectLeague",
                nonce: nonce,
                orgId: $("#ava-detect-org").val(),
                saison: $("#ava-detect-saison").val(),
                region: $("#ava-detect-region").val()
            }).done(function (response) {
                if (!response.success) { status.text("Fehler: " + response.data); return; }
                status.text(response.data.length + " Liga(en) gefunden.");
                $.each(response.data, function (i, league) {
                    var useButton = $('<button type="button" class="button">Übernehmen</button>').on("click", function () {
                        $('input[name="ava_liga[saison_liga_id]"]').val(league.SaisonLigaID);
                    });
                    $("<tr>")
                        .append($("<td>").text(league.SaisonLigaID))
                        .append($("<td>").text(league.LigaName))
                        .append($("<td>").text(league.VerbandsregionName))
                        .append($("<td>").text(league.TeamName))
                        .append($("<td>").append(useButton))
                        .appendTo(results.find("tbody"));
                });
                results.show();
            }).fail(function () { status.text("Anfrage fehlgeschlagen."); });
        });
    });
    </script>
    <?php
}

/**
 * Prints the team list tool: queries api/Teams for one league. Each team has a
 * button that copies its OrgID into the "Eigener Verein" field above.
 * Nothing is saved until the settings form is submitted.
 */
function ava_renderTeamSearchTool() {
    ?>
    <hr>
    <h2>Mannschaften einer Liga</h2>
    <p>
        Ruft <code>GET api/Teams?saisonLigaId={saisonLigaId}</code> auf und listet die Mannschaften der Liga.
        Mit „OrgID übernehmen“ bzw. „TeamID übernehmen“ werden die Werte in die Felder „Eigener Verein“ und
        mannschaftenID oben eingetragen (danach noch speichern).
    </p>
    <p>
        <label>saisonLigaId (Pflicht)
            <input type="number" id="ava-teams-liga" class="small-text" value="<?php echo esc_attr(ava_getLeagueOption('saison_liga_id')); ?>">
        </label>
        <button type="button" class="button" id="ava-teams-run">Mannschaften laden</button>
    </p>
    <p id="ava-teams-status"></p>
    <table class="widefat striped" id="ava-teams-results" style="display:none;max-width:800px">
        <thead><tr><th>TeamID</th><th>TeamName</th><th>OrgID</th><th>TeamIndex</th><th></th></tr></thead>
        <tbody></tbody>
    </table>
    <script>
    jQuery(function ($) {
        var nonce = <?php echo wp_json_encode(wp_create_nonce('ava_searchTeams')); ?>;
        var status = $("#ava-teams-status");
        var results = $("#ava-teams-results");

        $("#ava-teams-run").on("click", function () {
            status.text("Lade ...");
            results.hide().find("tbody").empty();
            $.post(ajaxurl, {
                action: "ava_searchTeams",
                nonce: nonce,
                saisonLigaId: $("#ava-teams-liga").val()
            }).done(function (response) {
                if (!response.success) { status.text("Fehler: " + response.data); return; }
                status.text(response.data.length + " Mannschaften gefunden.");
                $.each(response.data, function (i, team) {
                    var useOrgButton = $('<button type="button" class="button">OrgID übernehmen</button>').on("click", function () {
                        $('input[name="ava_liga[eigene_org_id]"]').val(team.OrgID);
                    });
                    var useTeamButton = $('<button type="button" class="button">TeamID übernehmen</button>').on("click", function () {
                        $('input[name="ava_liga[mannschaften_id]"]').val(team.TeamID);
                    });
                    $("<tr>")
                        .append($("<td>").text(team.TeamID))
                        .append($("<td>").text(team.TeamName))
                        .append($("<td>").text(team.OrgID))
                        .append($("<td>").text(team.TeamIndex))
                        .append($("<td>").append(useOrgButton, " ", useTeamButton))
                        .appendTo(results.find("tbody"));
                });
                results.show();
            }).fail(function () { status.text("Anfrage fehlgeschlagen."); });
        });
    });
    </script>
    <?php
}
