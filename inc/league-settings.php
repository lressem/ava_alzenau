<?php
/**
 * Admin page "Tabelle": parameters of the liga-db.de call that provides the
 * league standings (GET https://ringen.liga-db.de/api/ErgebnisTabelle).
 */
if (!defined('ABSPATH')) exit;

/**
 * Definition of the league settings.
 *
 * Per setting: key => [label, input type, default value, help text].
 * The first two are the query parameters of the API endpoint, the last one
 * only controls which row is highlighted on the website.
 *
 * @return array
 */
function ava_getLeagueSettingFields() {
    return [
        'saison_liga_id' => ['saisonLigaId (Pflicht)', 'number', '1257', 'ID der Liga. Steht in der Adresse bei liga-db.de, z. B. https://www.liga-db.de/ligen/1257'],
        'kampf_tag_id'   => ['kampfTagID (optional)', 'number', '', 'Tabellenstand zu diesem Kampftag. Leer = aktueller Tabellenstand. Die IDs liefert api/TeamMatchDays?saisonliga={saisonLigaId}.'],
        'eigene_org_id'  => ['Eigener Verein: OrgID (nur Hervorhebung)', 'number', '2221', 'Die Zeile mit dieser OrgID wird in der Tabelle hervorgehoben (RWG Alzenau/Kahl = 2221). Leer = keine Hervorhebung.'],
    ];
}

/**
 * Returns one league setting (saved value, or the default if nothing is saved).
 *
 * @param string $key Key from ava_getLeagueSettingFields().
 * @return string
 */
function ava_getLeagueOption($key) {
    $fields = ava_getLeagueSettingFields();
    $saved = get_option('ava_liga', []);
    if (is_array($saved) && isset($saved[$key])) return (string) $saved[$key];
    return $fields[$key][2] ?? '';
}

/**
 * Sanitizes the submitted league settings. All values are positive integers
 * or empty; an empty saisonLigaId falls back to its default.
 *
 * @param array $input Raw submitted values (key => value).
 * @return array
 */
function ava_sanitizeLeagueSettings($input) {
    $clean = [];
    foreach (ava_getLeagueSettingFields() as $key => $field) {
        $number = absint(isset($input[$key]) ? $input[$key] : 0);
        $clean[$key] = $number > 0 ? (string) $number : ($key === 'saison_liga_id' ? $field[2] : '');
    }
    return $clean;
}

add_action('admin_init', function () {
    register_setting('ava_liga', 'ava_liga', ['sanitize_callback' => 'ava_sanitizeLeagueSettings']);
});

add_action('admin_menu', function () {
    add_menu_page('Tabelle', 'Tabelle', 'manage_options', 'ava_liga', 'ava_renderLeagueSettingsPage', 'dashicons-editor-ol', 4);
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
            Die Tabelle auf der Startseite wird live von liga-db.de geladen
            (<code>GET https://ringen.liga-db.de/api/ErgebnisTabelle</code>) und 15 Minuten zwischengespeichert.
            Hier stellst du die Parameter des Aufrufs ein.
        </p>
        <form method="post" action="options.php">
    <?php
    settings_fields('ava_liga');
    echo '<table class="form-table">';
    foreach (ava_getLeagueSettingFields() as $key => $field) {
        printf('<tr><th>%s</th><td>', esc_html($field[0]));
        ava_renderField('ava_liga[' . $key . ']', $field, ava_getLeagueOption($key));
        printf('<p class="description">%s</p></td></tr>', esc_html($field[3]));
    }
    echo '</table>';
    submit_button();
    echo '</form>';

    ava_renderTeamSearchTool();
    ava_renderRegionSearchTool();
    echo '</div>';
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
        Mit „Übernehmen“ wird die OrgID in das Feld „Eigener Verein“ oben eingetragen (danach noch speichern).
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
                    var useButton = $('<button type="button" class="button">Übernehmen</button>').on("click", function () {
                        $('input[name="ava_liga[eigene_org_id]"]').val(team.OrgID);
                    });
                    $("<tr>")
                        .append($("<td>").text(team.TeamID))
                        .append($("<td>").text(team.TeamName))
                        .append($("<td>").text(team.OrgID))
                        .append($("<td>").text(team.TeamIndex))
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
