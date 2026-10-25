<?php
/**
 * Custom post types (sponsors, league table, matches, training, trainers,
 * memberships), their meta boxes and saving of their custom fields.
 */
if (!defined('ABSPATH')) exit;

/**
 * Definition of all custom post types.
 *
 * Per type:
 *  - 'n' => [singular label, plural label]
 *  - 'i' => dashicon for the admin menu
 *  - 's' => post type "supports" (title, thumbnail, page-attributes)
 *  - 'f' => custom fields: key => [label, input type, (select options)]
 *
 * @return array
 */
function ava_getContentTypes() {
    $weekDayOptions = array_combine(ava_getWeekDays(), ava_getWeekDays());

    return [
        // Sponsor logos (top bar) and partner section.
        'sponsor' => [
            'n' => ['Sponsor', 'Sponsoren'],
            'i' => 'dashicons-heart',
            's' => ['title', 'thumbnail', 'page-attributes'],
            'f' => [
                'link'       => ['Link (URL)', 'url'],
                'untertitel' => ['Untertitel (nur Bereich Partner)', 'text'],
                'gruppe'     => ['Anzeige', 'select', ['leiste' => 'Logoleiste oben', 'partner' => 'Bereich Partner']],
            ],
        ],
        // One row of the league table.
        'tabelle' => [
            'n' => ['Tabelleneintrag', 'Tabelle (Oberliga)'],
            'i' => 'dashicons-editor-ol',
            's' => ['title'],
            'f' => [
                'platz'   => ['Platz', 'number'],
                'diff'    => ['Differenz (z. B. +32)', 'text'],
                'punkte'  => ['Punkte (z. B. 14:4)', 'text'],
                'eigener' => ['Eigener Verein hervorheben', 'check'],
            ],
        ],
        // Match day (upcoming or finished).
        'kampftag' => [
            'n' => ['Kampftag', 'Kampftage'],
            'i' => 'dashicons-awards',
            's' => ['title'],
            'f' => [
                'datum'  => ['Datum und Uhrzeit', 'datetime-local'],
                'heim'   => ['Heim oder Auswärts', 'select', ['1' => 'Heimkampf', '0' => 'Auswärtskampf']],
                'gegner' => ['Gegner', 'text'],
                'ort'    => ['Ort', 'text'],
                'p_heim' => ['Punkte Heim-Mannschaft (nach dem Kampf)', 'number'],
                'p_gast' => ['Punkte Gast-Mannschaft (nach dem Kampf)', 'number'],
                'ticket' => ['Ticket-Link (optional)', 'url'],
            ],
        ],
        // Weekly training slot.
        'training' => [
            'n' => ['Trainingseinheit', 'Training'],
            'i' => 'dashicons-clock',
            's' => ['title'],
            'f' => [
                'tag'   => ['Wochentag', 'select', $weekDayOptions],
                'von'   => ['Beginn', 'time'],
                'bis'   => ['Ende', 'time'],
                'coach' => ['Coach(es)', 'text'],
            ],
        ],
        // Coaches.
        'trainer' => [
            'n' => ['Trainer*in', 'Trainer*innen'],
            'i' => 'dashicons-groups',
            's' => ['title', 'thumbnail', 'page-attributes'],
            'f' => [
                'rolle' => ['Rolle (z. B. Trainer Kinder & Jugend)', 'text'],
                'zitat' => ['Zitat', 'text'],
            ],
        ],
        // Membership plans / prices.
        'mitgliedschaft' => [
            'n' => ['Mitgliedschaft', 'Mitgliedschaften'],
            'i' => 'dashicons-id',
            's' => ['title', 'page-attributes'],
            'f' => [
                'preis'     => ['Preis (z. B. 45,00 €)', 'text'],
                'intervall' => ['Intervall (z. B. jährlich)', 'text'],
                'badge'     => ['Badge (optional)', 'text'],
                'vorteile'  => ['Leistungen (eine pro Zeile)', 'textarea'],
                'button'    => ['Button-Text', 'text'],
                'top'       => ['Hervorgehoben', 'check'],
            ],
        ],
    ];
}

// Register every custom post type (admin UI only, no public URLs).
add_action('init', function () {
    foreach (ava_getContentTypes() as $postType => $config) {
        register_post_type($postType, [
            'labels' => [
                'name'          => $config['n'][1],
                'singular_name' => $config['n'][0],
                'add_new_item'  => $config['n'][0] . ' hinzufügen',
                'edit_item'     => $config['n'][0] . ' bearbeiten',
                'all_items'     => 'Alle ' . $config['n'][1],
            ],
            'public'       => false,
            'show_ui'      => true,
            'menu_icon'    => $config['i'],
            'supports'     => $config['s'],
            'rewrite'      => false,
            'show_in_rest' => false,
        ]);
    }
});

// Add a "Details" meta box with the custom fields to every post type.
add_action('add_meta_boxes', function () {
    foreach (ava_getContentTypes() as $postType => $config) {
        add_meta_box('ava_f', 'Details', function ($post) use ($config) {
            wp_nonce_field('ava_save', 'ava_n');
            echo '<table class="form-table">';
            foreach ($config['f'] as $key => $field) {
                printf('<tr><th>%s</th><td>', esc_html($field[0]));
                ava_renderField('ava_m[' . $key . ']', $field, ava_getMeta($post->ID, $key));
                echo '</td></tr>';
            }
            echo '</table>';
        }, $postType);
    }
});

// Validate and save the custom fields when a post is saved.
add_action('save_post', function ($postId) {
    if (
        !isset($_POST['ava_n'])
        || !wp_verify_nonce($_POST['ava_n'], 'ava_save')
        || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || !current_user_can('edit_post', $postId)
    ) return;

    $contentTypes = ava_getContentTypes();
    $postType = get_post_type($postId);
    if (!isset($contentTypes[$postType])) return;

    $submitted = isset($_POST['ava_m']) ? wp_unslash($_POST['ava_m']) : [];
    foreach ($contentTypes[$postType]['f'] as $key => $field) {
        update_post_meta($postId, $key, ava_sanitizeField($field, isset($submitted[$key]) ? $submitted[$key] : ''));
    }
});
