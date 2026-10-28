<?php
/**
 * Admin menu page "Vereinsdaten" where the theme settings are edited.
 */
if (!defined('ABSPATH')) exit;

// Add the top-level "Vereinsdaten" menu entry.
add_action('admin_menu', function () {
    add_menu_page('Vereinsdaten', 'Vereinsdaten', 'manage_options', 'ava', 'ava_renderSettingsPage', 'dashicons-admin-home', 3);
});

// Load the media library scripts, only on our settings page.
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_ava') wp_enqueue_media();
});

/**
 * Prints the settings page: one table per section heading,
 * plus a small script for the "Aus Mediathek wählen" buttons.
 */
function ava_renderSettingsPage() {
    $currentSection = '';

    ?>
    <div class="wrap">
        <h1>Vereinsdaten</h1>
        <p>
            Texte, Zahlen und Links der Startseite. Listen (Trainer, Training, Mitgliedschaften, Sponsoren)
            pflegst du in den eigenen Menüpunkten links. Tabelle und Kampftage kommen von liga-db.de
            (Menüpunkt „Tabelle“).
        </p>
        <form method="post" action="options.php">
    <?php
    settings_fields('ava');

    foreach (ava_getThemeSettingFields() as $key => $field) {
        // A new section heading starts a new table.
        if ($field[3] !== $currentSection) {
            if ($currentSection) echo '</table>';
            $currentSection = $field[3];
            printf('<h2>%s</h2><table class="form-table">', esc_html($currentSection));
        }
        printf('<tr><th>%s</th><td>', esc_html($field[0]));
        ava_renderField('ava_opts[' . $key . ']', $field, ava_getOption($key));
        echo '</td></tr>';
    }

    echo '</table>';
    submit_button();
    echo '</form></div>';

    // Opens the media library and writes the chosen URL into the preceding input.
    ?>
    <script>
    jQuery(function ($) {
        $(".ava-media").on("click", function (e) {
            e.preventDefault();
            var input = $(this).prev();
            var frame = wp.media({ multiple: false });
            frame.on("select", function () {
                input.val(frame.state().get("selection").first().toJSON().url);
            });
            frame.open();
        });
    });
    </script>
    <?php
}
