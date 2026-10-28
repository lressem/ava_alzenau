<?php
/**
 * Theme core loader. The original single-file version is kept in core.original.php.
 */
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/helpers.php';         // asset/option/meta helpers, field rendering & sanitizing
require_once __DIR__ . '/i18n.php';            // German/English support via Polylang, ava_t()
require_once __DIR__ . '/content-types.php';   // custom post types, meta boxes
require_once __DIR__ . '/themes-settings.php'; // theme settings definition & sanitizing
require_once __DIR__ . '/admin-page.php';      // "Vereinsdaten" admin page
require_once __DIR__ . '/league-settings.php'; // "Tabelle" admin page (liga-db.de API parameters)
require_once __DIR__ . '/seed-data.php';       // demo content on theme activation
