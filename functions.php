<?php
/**
 * AVA Alzenau theme: setup, assets and menu fallbacks.
 */
if (!defined('ABSPATH')) exit;

define('AVA_V', '1.0.0');

require get_template_directory() . '/inc/core.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['style', 'script']);

    register_nav_menus([
        'main'   => 'Hauptmenü',
        'footer' => 'Footer (Rechtliches)',
    ]);

    add_image_size('ava-card', 864, 440, true);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('ava', get_stylesheet_uri(), [], AVA_V);

    $heroImage = ava_getOption('hero_poster') ?: ava_getAssetUrl('img/hero.webp');
    wp_add_inline_style('ava', ':root{--hero-bg:url("' . esc_url($heroImage) . '")}');
});

/**
 * Main menu shown until an editor assigns a menu: anchors to the front page sections.
 */
function ava_nav_fallback() {
    $items = [
        'aktuelles' => 'Aktuelles',
        'liga'      => 'Liga',
        'training'  => 'Training',
        'verein'    => 'Verein',
        'mitmachen' => 'Mitmachen',
        'kontakt'   => 'Standort & Kontakt',
        'partner'   => 'Partner',
    ];

    echo '<ul>';
    foreach ($items as $anchor => $label) {
        echo '<li><a href="' . esc_url(home_url('/#' . $anchor)) . '">' . esc_html(ava_t($label)) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Footer menu shown until an editor assigns a menu: legal pages, linked if they exist.
 */
function ava_footer_fallback() {
    $pages = [
        'impressum'      => 'Impressum',
        'datenschutz'    => 'Datenschutz',
        'vereinssatzung' => 'Vereinssatzung',
    ];

    echo '<ul>';
    foreach ($pages as $slug => $label) {
        $page = get_page_by_path($slug);
        $url  = $page ? get_permalink($page) : home_url('/' . $slug . '/');
        echo '<li><a href="' . esc_url($url) . '">' . esc_html(ava_t($label)) . '</a></li>';
    }
    echo '</ul>';
}
