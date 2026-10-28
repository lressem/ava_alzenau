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
    echo '</form></div>';
}
