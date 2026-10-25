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
 * Returns a saved theme setting (from the 'ava_opts' option).
 * Falls back to the default defined in ava_getThemeSettingFields().
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
    if (isset($savedOptions[$key])) return $savedOptions[$key];
    $fields = ava_getThemeSettingFields();
    return $fields[$key][2] ?? '';
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
 * Fetches all posts of a custom post type, sorted by menu order, then title.
 *
 * @param string $postType
 * @param array  $overrides get_posts() arguments overriding the defaults.
 * @return WP_Post[]
 */
function ava_getPosts($postType, $overrides = []) {
    return get_posts(array_merge([
        'post_type'        => $postType,
        'numberposts'      => -1,
        'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
        'suppress_filters' => false,
    ], $overrides));
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
