<?php
/**
 * Bilingual support (German/English) on top of the Polylang plugin.
 *
 * Without Polylang everything still works and the site stays German.
 * With Polylang:
 *  - fixed theme texts go through ava_t() and are registered under
 *    Languages > Translations, with English defaults from translations-en.php
 *  - text settings of "Vereinsdaten" are registered there as well
 *  - the language switcher is printed by ava_renderLanguageSwitcher()
 */
if (!defined('ABSPATH')) exit;

/**
 * Returns the English dictionary (German => English), loaded once.
 *
 * @return array
 */
function ava_getEnglishStrings() {
    static $strings = null;
    if ($strings === null) $strings = require __DIR__ . '/translations-en.php';
    return $strings;
}

/**
 * Translates a German text into the current site language.
 * Order: translation entered by the editor in Polylang, then the built-in
 * English default (only when English is active), otherwise the original text.
 *
 * @param string $text German original.
 * @return string
 */
function ava_t($text) {
    if (!function_exists('pll__')) return $text;

    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $translated = pll__($text);
    if ($translated !== $text) return $translated;

    $language = function_exists('pll_current_language')
        ? strtolower(str_replace('_', '-', pll_current_language()))
        : '';
    if (preg_match('/^en(?:-|$)/', $language)) {
        $english = ava_getEnglishStrings();
        if (isset($english[$text])) return $english[$text];
    }
    return $text;
}

/**
 * Prints a translated text, HTML-escaped. Shortcut for echo esc_html(ava_t()).
 *
 * @param string $text German original.
 */
function ava_e($text) {
    echo esc_html(ava_t($text));
}

/**
 * Registers every translatable text with Polylang so the editor can
 * translate it under Languages > Translations:
 *  - the fixed texts from the English dictionary
 *  - the text and textarea settings of "Vereinsdaten" (current saved value)
 */
function ava_registerTranslatableStrings() {
    if (!function_exists('pll_register_string')) return;

    foreach (array_keys(ava_getEnglishStrings()) as $text) {
        if ($text !== '') pll_register_string(mb_substr($text, 0, 40), $text, 'AVA Theme', strlen($text) > 80);
    }

    $saved = get_option('ava_opts', []);
    foreach (ava_getThemeSettingFields() as $key => $field) {
        if ($field[1] !== 'text' && $field[1] !== 'textarea') continue;
        $value = is_array($saved) && isset($saved[$key]) ? $saved[$key] : $field[2];
        if ($value !== '') pll_register_string($field[0], $value, 'AVA Vereinsdaten', $field[1] === 'textarea');
    }

    foreach (get_posts(['post_type' => 'training', 'numberposts' => -1, 'suppress_filters' => true]) as $training) {
        if ($training->post_title !== '') {
            $title = html_entity_decode($training->post_title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            pll_register_string(mb_substr($title, 0, 40), $title, 'AVA Training');
        }
    }
}
add_action('init', 'ava_registerTranslatableStrings', 20);

/**
 * Front page URL in the current language (home_url() always points to the default language).
 *
 * @param string $path Appended to the URL, e.g. '#mitmachen'.
 * @return string
 */
function ava_getHomeUrl($path = '') {
    $homeUrl = function_exists('pll_home_url') ? pll_home_url() : home_url('/');
    return $homeUrl . $path;
}

/**
 * Prints the language switcher (flags only). Does nothing without Polylang.
 */
function ava_renderLanguageSwitcher() {
    if (!function_exists('pll_the_languages')) return;

    echo '<ul class="lang-switch">';
    pll_the_languages(['show_flags' => 1, 'show_names' => 0, 'hide_if_no_translation' => 0, 'hide_if_empty' => 0]);
    echo '</ul>';
}
