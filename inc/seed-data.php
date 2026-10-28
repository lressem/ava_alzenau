<?php
/**
 * Demo content that is created once when the theme is activated.
 */
if (!defined('ABSPATH')) exit;

add_action('after_switch_theme', 'ava_seedDemoData');

/**
 * Creates the demo sponsors, match days, training slots,
 * trainers, memberships and legal pages. Runs only once ('ava_seeded' flag).
 */
function ava_seedDemoData() {
    if (get_option('ava_seeded')) return;

    // Small helper: inserts one published post of the given type.
    $createPost = function ($postType, $title, $meta, $menuOrder = 0) {
        wp_insert_post([
            'post_type'   => $postType,
            'post_title'  => $title,
            'post_status' => 'publish',
            'menu_order'  => $menuOrder,
            'meta_input'  => $meta,
        ]);
    };

    // Sponsors shown in the logo bar.
    $logoBarSponsors = ['Vita Sports', 'Hofladen Erdbeer Ranch', 'David Stelter KFZ', 'Kost Bar', 'Gasthaus Krone', 'Stadtwerke Alzenau', 'Sponsor 7'];
    foreach ($logoBarSponsors as $index => $name) {
        $createPost('sponsor', $name, ['gruppe' => 'leiste', '_demo' => 'logos/logo-' . ($index + 1) . '.webp'], $index);
    }

    // Partners: [name, subtitle].
    $partners = [['Hessischer Ringer-Verband', 'Landesverband'], ['Deutscher Ringer-Bund', 'Bundesverband'], ['Sponsoren', 'Unternehmen']];
    foreach ($partners as $index => $partner) {
        $createPost('sponsor', $partner[0], ['gruppe' => 'partner', 'untertitel' => $partner[1]], 10 + $index);
    }

    // One upcoming and one finished match day.
    $createPost('kampftag', 'Nächster Kampftag', [
        'datum'  => date('Y-m-d\T19:30', strtotime('next saturday')),
        'heim'   => '1',
        'gegner' => 'KSC Goldbach',
        'ort'    => 'Prischoßhalle Alzenau',
    ]);
    $createPost('kampftag', 'Letzter Kampftag', [
        'datum'  => date('Y-m-d\T19:30', strtotime('last saturday')),
        'heim'   => '0',
        'gegner' => 'SG Arheilgen 06',
        'ort'    => 'Arheilgen',
        'p_heim' => '6',
        'p_gast' => '24',
    ]);

    // Training slots: [title, weekday, from, to, coaches].
    $youthCoaches = 'Heiko, Matze, Sebi & Sebastian';
    $trainingSlots = [
        ['Kinder & Jugendtraining', 'Dienstag', '17:30', '19:00', $youthCoaches],
        ['Training 1. Mannschaft RWG', 'Dienstag', '19:00', '21:00', 'Tim & Ralf'],
        ['Jugendintensiv-Training', 'Mittwoch', '18:00', '19:00', $youthCoaches],
        ['Athletik-Training', 'Mittwoch', '19:00', '21:00', 'Tim'],
        ['Bambini-Training', 'Freitag', '17:00', '18:15', 'Tamina'],
        ['Kinder & Jugendtraining', 'Freitag', '17:30', '19:00', $youthCoaches],
        ['Training 1. Mannschaft RWG', 'Freitag', '19:30', '21:30', 'Tim & Ralf'],
    ];
    foreach ($trainingSlots as $slot) {
        $createPost('training', $slot[0], ['tag' => $slot[1], 'von' => $slot[2], 'bis' => $slot[3], 'coach' => $slot[4]]);
    }

    // Trainers: [name, role, quote].
    $trainers = [
        ['Tim Heiniger', 'Trainer 1. Mannschaft', 'Jede Übung kann auch durch Burpees ersetzt werden!'],
        ['Heiko Bork', 'Trainer Kinder & Jugend', 'Zitat folgt'],
        ['Sebastian Amreihn', 'Trainer Kinder & Jugend', 'Zitat folgt'],
        ['Sebastian', 'Trainer Kinder & Jugend', 'Zitat folgt'],
        ['Matthias Amreihn', 'Trainer Kinder & Jugend', 'Zitat folgt'],
        ['Tamina Staab', 'Trainerin Bambinis', 'Zitat folgt'],
    ];
    foreach ($trainers as $index => $trainer) {
        $createPost('trainer', $trainer[0], ['rolle' => $trainer[1], 'zitat' => $trainer[2], '_demo' => 'coach-' . ($index + 1) . '.webp'], $index);
    }

    // Membership plans.
    $createPost('mitgliedschaft', 'Jugend / Schüler', [
        'preis'     => '45,00 €',
        'intervall' => 'jährlich',
        'vorteile'  => "Zutritt zum Jugendtraining\nLizenzen für offizielle Wettkämpfe\nFreitickets für Heimkämpfe der 1. Mannschaft\nFreizeitaktivitäten wie Zeltlager und Wandern",
        'button'    => 'Nachwuchs anmelden',
    ], 1);
    $createPost('mitgliedschaft', 'Aktive Ringer', [
        'preis'     => '45,00 €',
        'intervall' => 'monatlich',
        'badge'     => 'Wettkämpfer',
        'top'       => '1',
        'vorteile'  => "Voller Zutritt zu allen Trainingseinheiten\nProfessionelle Wettkampfbetreuung\nNutzung des Fitness- & Kraftraums*\nTrainingsanzüge inklusive",
        'button'    => 'Jetzt anmelden',
    ], 2);
    $createPost('mitgliedschaft', 'Familienmitgliedschaft', [
        'preis'     => '100,00 €',
        'intervall' => 'jährlich',
        'vorteile'  => "Unterstützung der Nachwuchsarbeit\nEinladung zu allen Vereinsevents\nZugang zum Athletik-Training\nNutzung des Fitness- & Kraftraums*",
        'button'    => 'Mitglied werden',
    ], 3);

    // Legal pages (only if they do not exist yet).
    foreach (['Impressum', 'Datenschutz', 'Vereinssatzung'] as $pageTitle) {
        if (!get_page_by_path(sanitize_title($pageTitle))) {
            wp_insert_post(['post_type' => 'page', 'post_title' => $pageTitle, 'post_status' => 'publish', 'post_content' => 'Bitte Inhalt ergänzen.']);
        }
    }

    update_option('ava_seeded', 1);
}
