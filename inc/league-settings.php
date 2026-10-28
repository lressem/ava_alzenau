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

// AJAX: lists the leagues of a season (api/Ligen) for the search tool on the "Tabelle" page.
add_action('wp_ajax_ava_searchLeagues', function () {
    check_ajax_referer('ava_searchLeagues', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Keine Berechtigung', 403);

    $queryArgs = [];
    $season = absint($_POST['saison'] ?? 0);
    $regionalClassId = absint($_POST['regionalKlassenID'] ?? 0);
    if ($season > 0) $queryArgs['saison'] = $season;
    if ($regionalClassId > 0) $queryArgs['regionalKlassenID'] = $regionalClassId;

    $leagues = ava_fetchLigaDb('Ligen', $queryArgs);
    if (is_wp_error($leagues)) wp_send_json_error($leagues->get_error_message());
    wp_send_json_success($leagues);
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

    ava_renderLeagueSearchTool();
    echo '</div>';
}

/**
 * Prints the search tool: queries api/Ligen and lists the leagues, each with a
 * button that copies its SaisonLigaID into the saisonLigaId field above.
 * Nothing is saved until the settings form is submitted.
 */
function ava_renderLeagueSearchTool() {
    ?>
    <hr>
    <h2>Liga suchen</h2>
    <p>
        Ruft <code>GET api/Ligen?saison={saison}&amp;regionalKlassenID={regionalKlassenID}</code> auf und
        listet die Ligen. Mit „Übernehmen“ wird die ID in das Feld saisonLigaId oben eingetragen
        (danach noch speichern).
    </p>
    <p>
        <label>saison (leer = aktuelle Saison) <input type="number" id="ava-search-saison" class="small-text"></label>
        <label>regionalKlassenID (leer = alle) <input type="number" id="ava-search-region" class="small-text"></label>
        <button type="button" class="button" id="ava-search-run">Ligen laden</button>
    </p>
    <p id="ava-search-status"></p>
    <table class="widefat striped" id="ava-search-results" style="display:none;max-width:800px">
        <thead><tr><th>SaisonLigaID</th><th>Liga</th><th>Region</th><th></th></tr></thead>
        <tbody></tbody>
    </table>
    <script>
    jQuery(function ($) {
        var nonce = <?php echo wp_json_encode(wp_create_nonce('ava_searchLeagues')); ?>;
        var status = $("#ava-search-status");
        var results = $("#ava-search-results");

        $("#ava-search-run").on("click", function () {
            status.text("Lade ...");
            results.hide().find("tbody").empty();
            $.post(ajaxurl, {
                action: "ava_searchLeagues",
                nonce: nonce,
                saison: $("#ava-search-saison").val(),
                regionalKlassenID: $("#ava-search-region").val()
            }).done(function (response) {
                if (!response.success) { status.text("Fehler: " + response.data); return; }
                status.text(response.data.length + " Ligen gefunden.");
                $.each(response.data, function (i, league) {
                    var useButton = $('<button type="button" class="button">Übernehmen</button>').on("click", function () {
                        $('input[name="ava_liga[saison_liga_id]"]').val(league.SaisonLigaID);
                    });
                    $("<tr>")
                        .append($("<td>").text(league.SaisonLigaID))
                        .append($("<td>").text(league.LigaName))
                        .append($("<td>").text(league.VerbandsregionName))
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
