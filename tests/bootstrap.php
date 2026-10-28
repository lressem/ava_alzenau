<?php
/**
 * Test bootstrap: provides tiny in-memory fakes of the WordPress functions
 * the theme uses, so the inc/ files can be tested without WordPress.
 */
require_once __DIR__ . '/../vendor/autoload.php';

define('ABSPATH', __DIR__ . '/');

// Hooks registered by the theme files (hook name => [callbacks]); kept across tests.
$GLOBALS['wp_hooks'] = [];

/** Resets the fake WordPress data (call in setUp of every test). */
function wp_stub_reset() {
    $GLOBALS['wp_stub'] = [
        'options'    => [], // option name => value
        'meta'       => [], // [postId][key] => value
        'thumbs'     => [], // postId => url
        'posts'      => [], // posts created via wp_insert_post()
        'post_types' => [], // post type => args from register_post_type()
        'pages'      => [], // slugs of existing pages
        'transients' => [], // transient name => value
        'last_get_posts' => [],
    ];
}
wp_stub_reset();

// --- Hooks & registration ---
function add_action($hook, $callback) { $GLOBALS['wp_hooks'][$hook][] = $callback; }
function register_post_type($type, $args) { $GLOBALS['wp_stub']['post_types'][$type] = $args; }
function register_setting() {}
function add_menu_page() {}
function wp_enqueue_media() {}

// --- Data access ---
function get_option($name, $default = false) { return $GLOBALS['wp_stub']['options'][$name] ?? $default; }
function update_option($name, $value) { $GLOBALS['wp_stub']['options'][$name] = $value; }
function get_transient($name) { return $GLOBALS['wp_stub']['transients'][$name] ?? false; }
function set_transient($name, $value, $expiration) { $GLOBALS['wp_stub']['transients'][$name] = $value; }
function add_query_arg($args, $url) { return $url . '?' . http_build_query($args); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_remote_get() { return new WP_Error(); }
function get_post_meta($id, $key) { return $GLOBALS['wp_stub']['meta'][$id][$key] ?? ''; }
function get_the_post_thumbnail_url($id) { return $GLOBALS['wp_stub']['thumbs'][$id] ?? false; }
function get_template_directory_uri() { return 'http://example.test/theme'; }
function get_posts($args) { $GLOBALS['wp_stub']['last_get_posts'] = $args; return []; }
function wp_insert_post($args) { $GLOBALS['wp_stub']['posts'][] = $args; return count($GLOBALS['wp_stub']['posts']); }
function get_page_by_path($slug) { return in_array($slug, $GLOBALS['wp_stub']['pages'], true) ? (object) [] : null; }

// --- Escaping & sanitizing (simplified) ---
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_textarea($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_url_raw($s) { return preg_match('#^https?://#', (string) $s) ? (string) $s : ''; }
function sanitize_text_field($s) { return trim(strip_tags((string) $s)); }
function sanitize_textarea_field($s) { return trim(strip_tags((string) $s)); }
function sanitize_title($s) { return strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $s)); }
function absint($n) { return abs((int) $n); }
function selected($a, $b, $echo = true) { return (string) $a === (string) $b ? ' selected="selected"' : ''; }
function checked($a, $b, $echo = true) { return (string) $a === (string) $b ? ' checked="checked"' : ''; }

class WP_Error {}
define('MINUTE_IN_SECONDS', 60);

// Load the theme code under test.
require_once __DIR__ . '/../inc/core.php';
