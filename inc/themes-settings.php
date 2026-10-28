<?php
/**
 * Theme settings ("Vereinsdaten"): definition of all editable texts/links
 * with their default values, plus registration and sanitizing.
 */
if (!defined('ABSPATH')) exit;

/**
 * Definition of all theme settings, in display order.
 *
 * Per setting: key => [label, input type, default value, section heading].
 * Read them with ava_getOption($key).
 *
 * @return array
 */
function ava_getThemeSettingFields() {
    return [
        // --- Startseite: Hero ---
        'hero_geo'    => ['Zeile über der Überschrift', 'text', 'Alzenau - Franken - Bayern', 'Startseite: Hero'],
        'hero_l1'     => ['Überschrift Zeile 1', 'text', 'Seit 1899.', 'Startseite: Hero'],
        'hero_l2'     => ['Überschrift Zeile 2 (blau)', 'text', 'Ringen', 'Startseite: Hero'],
        'hero_l3'     => ['Überschrift Zeile 3', 'text', 'in Alzenau.', 'Startseite: Hero'],
        'hero_lead'   => ['Einleitungstext', 'textarea', 'Wir machen Kinder stark — und Sportler stärker. Vom Einsteiger bis zur Verbandsrunde. Trainiere mit uns in der Prischoßhalle.', 'Startseite: Hero'],
        'hero_mp4'    => ['Hero-Video MP4', 'media', ava_getAssetUrl('video/hero.mp4'), 'Startseite: Hero'],
        'hero_webm'   => ['Hero-Video WebM (optional)', 'media', ava_getAssetUrl('video/hero.webm'), 'Startseite: Hero'],
        'hero_poster' => ['Hero-Standbild', 'media', '', 'Startseite: Hero'],
        'btn1'        => ['Button 1 (führt zu Mitmachen)', 'text', 'Jetzt mitmachen', 'Startseite: Hero'],
        'btn2'        => ['Button 2 Text', 'text', 'Kampftag-Tickets', 'Startseite: Hero'],
        'btn2_link'   => ['Button 2 Link', 'text', '#liga', 'Startseite: Hero'],
        'video_link'  => ['Link für „Video ansehen“ (leer = ausblenden)', 'url', '', 'Startseite: Hero'],

        // --- News ---
        'news_shortcode' => ['Instagram-Plugin Shortcode (z. B. [instagram-feed])', 'text', '', 'News aus dem Verein'],

        // --- Liga ---
        'liga_titel' => ['Titel der Tabelle', 'text', 'Tabelle (Oberliga)', 'Liga'],

        // --- Training ---
        'trainingsort'     => ['Trainingsort', 'text', 'Trainingsort: Prischoßhalle, Prischoßstraße 42, 63755 Alzenau.', 'Training'],
        'trainingshinweis' => ['Hinweis', 'textarea', 'Interessierte sind jederzeit zu einem kostenlosen Probetraining eingeladen. Bitte saubere Sportsachen und Hallenschuhe mitbringen.', 'Training'],

        // --- Verein (z* = Kennzahlen mit Beschriftung "l", v* = Vorstand mit Rolle "r") ---
        'verein_p1' => ['Text Absatz 1', 'textarea', 'Seit über einem Jahrhundert steht die Athleten-Vereinigung Alzenau 1899 e.V. für fairen Kampfsport, diszipliniertes Training und eine unschlagbare Gemeinschaft. Gegründet 1899, haben wir Generationen von Athleten geformt, von kleinen Bambinis bis hin zu Bundesliga-Athleten.', 'Verein'],
        'verein_p2' => ['Text Absatz 2', 'textarea', 'Unsere Philosophie verbindet klassisches Freistil- und Griechisch-Römisch-Ringen mit modernem Athletiktraining. Doch der sportliche Erfolg ist nur die halbe Medaille: Respekt, Teamgeist und die Förderung der Jugend stehen bei uns an erster Stelle.', 'Verein'],
        'z1'  => ['Zahl 1', 'text', '320+', 'Verein'],
        'z1l' => ['Beschriftung 1', 'text', 'Mitglieder', 'Verein'],
        'z2'  => ['Zahl 2', 'text', '1899', 'Verein'],
        'z2l' => ['Beschriftung 2', 'text', 'Gründungsjahr', 'Verein'],
        'z3'  => ['Zahl 3', 'text', '12', 'Verein'],
        'z3l' => ['Beschriftung 3', 'text', 'Titel', 'Verein'],
        'z4'  => ['Zahl 4', 'text', '100', 'Verein'],
        'z4l' => ['Beschriftung 4', 'text', 'Kinder & Jugendliche', 'Verein'],
        'v1'  => ['1. Vorstand (Name)', 'text', 'Alexander Staab', 'Verein'],
        'v1r' => ['1. Vorstand (Rolle)', 'text', '1. Vorstand', 'Verein'],
        'v2'  => ['2. Vorstand (Name)', 'text', 'Sebastian Amreihn', 'Verein'],
        'v2r' => ['2. Vorstand (Rolle)', 'text', '2. Vorstand', 'Verein'],
        'team_img' => ['Teamfoto', 'media', '', 'Verein'],

        // --- Mitmachen ---
        'plans_note'   => ['Fußnote Mitgliedschaften', 'text', '* Bedingungen siehe Hallenordnung.', 'Mitmachen'],
        'probe_titel'  => ['Probetraining Überschrift', 'text', 'Noch unsicher? Komm zum Probetraining!', 'Mitmachen'],
        'probe_text'   => ['Probetraining Text', 'textarea', 'Egal welches Alter, ob Junge oder Mädchen, absoluter Anfänger oder Wiedereinsteiger. Vereinbare jetzt dein unverbindliches Probetraining.', 'Mitmachen'],

        // --- Kontakt ---
        'k_halle'   => ['Name der Halle', 'text', 'Prischoßhalle Alzenau', 'Kontakt'],
        'k_adresse' => ['Adresse', 'text', 'Prischoßstraße 42, 63755 Alzenau', 'Kontakt'],
        'k_text'    => ['Text', 'textarea', 'Du hast Fragen zur Mitgliedschaft, zu den Trainingszeiten oder zu unseren Kampftagen? Melde dich direkt bei uns. Vorstand und Trainerteam helfen gerne weiter.', 'Kontakt'],
        'k_tel'     => ['Telefon', 'text', '+49 (0) 6023 987654', 'Kontakt'],
        'k_mail'    => ['E-Mail', 'text', 'kontakt@ringerverein-alzenau.de', 'Kontakt'],
        'k_insta'   => ['Instagram-Link', 'url', '', 'Kontakt'],
        'k_fb'      => ['Facebook-Link', 'url', '', 'Kontakt'],
        'map_img'   => ['Kartenbild', 'media', '', 'Kontakt'],
        'map_link'  => ['Link der Karte', 'url', 'https://www.openstreetmap.org/search?query=Prischo%C3%9Fstra%C3%9Fe%2042%20Alzenau', 'Kontakt'],

        // --- Partner / Sponsoring ---
        'sp_titel' => ['Sponsoring Überschrift', 'text', 'Interessiert an reichweitenstarken Werbeflächen?', 'Partner'],
        'sp_text'  => ['Sponsoring Text', 'text', 'Bei Interesse an einem Sponsoring melden Sie sich gerne jederzeit bei uns.', 'Partner'],

        // --- Footer ---
        'copy' => ['Copyright-Zeile', 'text', '© 2026 Ringer Verein Alzenau e.V. Alle Rechte vorbehalten.', 'Footer'],
    ];
}

// Register the settings option so WordPress saves it via options.php.
add_action('admin_init', function () {
    register_setting('ava', 'ava_opts', ['sanitize_callback' => 'ava_sanitizeSettings']);
});

/**
 * Sanitizes the submitted settings; only known keys are kept.
 *
 * @param array $input Raw submitted values (key => value).
 * @return array Clean values (key => value).
 */
function ava_sanitizeSettings($input) {
    $clean = [];
    foreach (ava_getThemeSettingFields() as $key => $field) {
        $clean[$key] = ava_sanitizeField($field, isset($input[$key]) ? $input[$key] : '');
    }
    return $clean;
}
