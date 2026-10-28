<?php
/**
 * Generic helper functions used by templates and by the other inc/ files.
 */
if (!defined('ABSPATH')) exit;

/**
 * Returns the full URL of a file inside the theme's assets/ folder.
 *
 * @param string $file Path relative to assets/ (e.g. 'img/logo.webp').
 * @return string
 */
function ava_getAssetUrl($file) {
    return get_template_directory_uri() . '/assets/' . $file;
}

/**
 * Returns the German weekday names, Monday first.
 *
 * @return string[]
 */
function ava_getWeekDays() {
    return ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
}

/**
 * Returns a theme setting, translated into the current language.
 * Uses the saved value (from the 'ava_opts' option) or the default from
 * ava_getThemeSettingFields(). Text settings go through ava_t() so the
 * English version is shown on the English site.
 * The option is loaded from the database only once per request.
 *
 * @param string $key Setting key, e.g. 'hero_l1'.
 * @return string
 */
function ava_getOption($key) {
    static $savedOptions = null;
    if ($savedOptions === null) {
        $savedOptions = get_option('ava_opts', []);
        if (!is_array($savedOptions)) $savedOptions = [];
    }
    $fields = ava_getThemeSettingFields();
    $value = isset($savedOptions[$key]) ? $savedOptions[$key] : ($fields[$key][2] ?? '');

    $type = $fields[$key][1] ?? '';
    if ($value !== '' && ($type === 'text' || $type === 'textarea')) return ava_t($value);
    return $value;
}

/**
 * Returns a single custom field (post meta) value of a post.
 *
 * @param int    $postId
 * @param string $key Meta key, e.g. 'datum'.
 * @return mixed
 */
function ava_getMeta($postId, $key) {
    return get_post_meta($postId, $key, true);
}

/**
 * Returns the image URL for a post: the featured image if set,
 * otherwise the demo image from assets/img/ (stored in the '_demo' meta).
 *
 * @param int    $postId
 * @param string $size WordPress image size name.
 * @return string URL or empty string.
 */
function ava_getPostImageUrl($postId, $size = 'ava-card') {
    $url = get_the_post_thumbnail_url($postId, $size);
    if ($url) return $url;
    $demoImage = ava_getMeta($postId, '_demo');
    return $demoImage ? ava_getAssetUrl('img/' . $demoImage) : '';
}

/**
 * Calls an endpoint of the liga-db.de API and decodes the JSON list.
 *
 * @param string $endpoint  Endpoint name, e.g. 'ErgebnisTabelle'.
 * @param array  $queryArgs Query parameters (name => value).
 * @return array|WP_Error Decoded response, or an error if the call failed.
 */
function ava_fetchLigaDb($endpoint, $queryArgs = []) {
    $response = wp_remote_get(
        add_query_arg($queryArgs, 'https://ringen.liga-db.de/api/' . $endpoint),
        ['timeout' => 8, 'headers' => ['Accept' => 'application/json']]
    );
    if (is_wp_error($response)) return $response;

    $statusCode = wp_remote_retrieve_response_code($response);
    if ($statusCode !== 200) return new WP_Error('ava_liga_db', 'liga-db.de antwortet mit HTTP ' . $statusCode);

    $data = json_decode(wp_remote_retrieve_body($response), true);
    return is_array($data) ? $data : new WP_Error('ava_liga_db', 'Ungültige Antwort von liga-db.de');
}

/**
 * Returns the league standings from the liga-db.de API (ErgebnisTabelle),
 * cached for 15 minutes. Returns an empty array when the API is unavailable.
 * Parameters come from the "Tabelle" admin page (see league-settings.php).
 *
 * @param int|null $saisonLigaId liga-db.de league ID; default: saved setting.
 * @param int|null $kampfTagId   Match day ID; default: saved setting (empty = current standings).
 * @return array[] Rows with keys Platzierung, TeamName, Differenz, WertungPlus, WertungMinus, OrgID.
 */
function ava_getLeagueTable($saisonLigaId = null, $kampfTagId = null) {
    $saisonLigaId = (int) ($saisonLigaId ?? ava_getLeagueOption('saison_liga_id'));
    $kampfTagId   = (int) ($kampfTagId ?? ava_getLeagueOption('kampf_tag_id'));

    $queryArgs = ['saisonLigaId' => $saisonLigaId];
    if ($kampfTagId > 0) $queryArgs['kampfTagID'] = $kampfTagId;

    $cacheKey = 'ava_liga_table_' . $saisonLigaId . '_' . $kampfTagId;
    $cached = get_transient($cacheKey);
    if (is_array($cached)) return $cached;

    $rows = ava_fetchLigaDb('ErgebnisTabelle', $queryArgs);
    if (is_wp_error($rows)) return [];

    usort($rows, function ($a, $b) {
        return ($a['Platzierung'] ?? 0) <=> ($b['Platzierung'] ?? 0);
    });
    set_transient($cacheKey, $rows, 15 * MINUTE_IN_SECONDS);
    return $rows;
}

/**
 * Returns the team matches from the liga-db.de API (TeamMatches), sorted by
 * date and start time and cached for 15 minutes. Returns an empty array when
 * the API is unavailable. Parameters come from the "Tabelle" admin page.
 *
 * @return array[] Rows with keys BegegnungsID, KampftagIst, Beginn, HeimMannschaft, GastMannschaft,
 *                 PunkteHeimWertung, PunkteGastWertung (null until played), HeimOrganisationsID, ...
 */
function ava_getLeagueMatches() {
    $queryArgs = ['saisonLigaID' => (int) ava_getLeagueOption('saison_liga_id')];

    $teamId = (int) ava_getLeagueOption('mannschaften_id');
    if ($teamId > 0) $queryArgs['mannschaftenID'] = $teamId;
    $matchDate = ava_getLeagueOption('kampftag');
    if ($matchDate !== '') $queryArgs['kampftag'] = $matchDate;
    $logicalMatchDay = (int) ava_getLeagueOption('logischer_kampftag');
    if ($logicalMatchDay > 0) $queryArgs['logischerKampftag'] = $logicalMatchDay;

    $cacheKey = 'ava_liga_matches_' . md5(http_build_query($queryArgs));
    $cached = get_transient($cacheKey);
    if (is_array($cached)) return $cached;

    $matches = ava_fetchLigaDb('TeamMatches', $queryArgs);
    if (is_wp_error($matches)) return [];

    usort($matches, function ($a, $b) {
        return [$a['KampftagIst'] ?? '', $a['Beginn'] ?? ''] <=> [$b['KampftagIst'] ?? '', $b['Beginn'] ?? ''];
    });
    set_transient($cacheKey, $matches, 15 * MINUTE_IN_SECONDS);
    return $matches;
}

/**
 * Picks the next upcoming match (no result yet, date today or later) and the
 * last played match (result entered) from a list sorted by date.
 *
 * @param array[] $matches Rows from ava_getLeagueMatches().
 * @param string  $today   Current date, 'Y-m-d'.
 * @return array ['next' => match|null, 'last' => match|null]
 */
function ava_findNextAndLastMatch($matches, $today) {
    $nextMatch = null;
    $lastMatch = null;
    foreach ($matches as $match) {
        $hasResult = ($match['PunkteHeimWertung'] ?? null) !== null && ($match['PunkteGastWertung'] ?? null) !== null;
        if ($hasResult) {
            $lastMatch = $match;
        } elseif ($nextMatch === null && substr($match['KampftagIst'] ?? '', 0, 10) >= $today) {
            $nextMatch = $match;
        }
    }
    return ['next' => $nextMatch, 'last' => $lastMatch];
}

/**
 * Start of a match as a DateTimeImmutable in the site timezone.
 *
 * @param array $match Row from ava_getLeagueMatches().
 * @return DateTimeImmutable
 */
function ava_getMatchStart($match) {
    $startTime = preg_match('/^\d{1,2}:\d{2}$/', $match['Beginn'] ?? '') ? $match['Beginn'] : '00:00';
    return new DateTimeImmutable(substr($match['KampftagIst'], 0, 10) . ' ' . $startTime, wp_timezone());
}

/**
 * Fetches all posts of a custom post type, sorted by menu order, then title.
 *
 * With Polylang the German (default language) posts are the base: in another
 * language each post is replaced by its translation when one exists, otherwise
 * the German post is shown. So untranslated lists never come up empty.
 *
 * @param string $postType
 * @param array  $overrides get_posts() arguments overriding the defaults.
 * @return WP_Post[]
 */
function ava_getPosts($postType, $overrides = []) {
    $args = array_merge([
        'post_type'        => $postType,
        'numberposts'      => -1,
        'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'suppress_filters' => false,
    ], $overrides);

    $canFallBack = function_exists('pll_default_language') && function_exists('pll_current_language')
        && function_exists('pll_get_post');
    if (!$canFallBack) return get_posts($args);

    $current = pll_current_language();
    $default = pll_default_language();
    if (!$current || !$default || $current === $default) return get_posts($args);

    $args['lang'] = $default;
    $posts = [];
    foreach (get_posts($args) as $post) {
        $translatedId = pll_get_post($post->ID, $current);
        $translated   = $translatedId ? get_post($translatedId) : null;
        $posts[]      = $translated ?: $post;
    }
    return $posts;
}

/**
 * Prints the HTML input for one field definition.
 * Supported types: textarea, select, check, media, and any HTML input type
 * (text, url, number, time, datetime-local, ...).
 *
 * @param string $inputName Value of the name attribute.
 * @param array  $field     Field definition: [label, type, options-or-default, ...].
 * @param mixed  $value     Current value.
 */
function ava_renderField($inputName, $field, $value) {
    $type = $field[1];
    $name = esc_attr($inputName);

    if ($type === 'textarea') {
        printf(
            '<textarea name="%s" rows="4" class="large-text">%s</textarea>',
            $name,
            esc_textarea($value)
        );
    } elseif ($type === 'select') {
        printf('<select name="%s">', $name);
        foreach ($field[2] as $optionValue => $optionLabel) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($optionValue),
                selected((string) $value, (string) $optionValue, false),
                esc_html($optionLabel)
            );
        }
        echo '</select>';
    } elseif ($type === 'check') {
        // The hidden input makes sure an unchecked box is still submitted (as empty).
        printf(
            '<input type="hidden" name="%1$s" value="">'
            . '<label><input type="checkbox" name="%1$s" value="1"%2$s> Ja</label>',
            $name,
            checked($value, '1', false)
        );
    } elseif ($type === 'media') {
        printf(
            '<input type="url" class="large-text" name="%s" value="%s">'
            . ' <button type="button" class="button ava-media">Aus Mediathek wählen</button>',
            $name,
            esc_attr($value)
        );
    } else {
        printf(
            '<input type="%s" name="%s" value="%s" class="regular-text">',
            esc_attr($type),
            $name,
            esc_attr($value)
        );
    }
}

/**
 * Sanitizes a submitted value according to its field type.
 * Selects fall back to their first option if the value is not allowed.
 *
 * @param array $field Field definition (see ava_renderField()).
 * @param mixed $value Raw submitted value.
 * @return string
 */
function ava_sanitizeField($field, $value) {
    $type = $field[1];

    if ($type === 'url' || $type === 'media') return esc_url_raw($value);
    if ($type === 'textarea') return sanitize_textarea_field($value);
    if ($type === 'check') return $value === '1' ? '1' : '';
    if ($type === 'select') {
        $allowedValues = array_map('strval', array_keys($field[2]));
        return in_array((string) $value, $allowedValues, true) ? (string) $value : $allowedValues[0];
    }
    return sanitize_text_field($value);
}
