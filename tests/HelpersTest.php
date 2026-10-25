<?php
use PHPUnit\Framework\TestCase;

/** Tests for inc/helpers.php */
class HelpersTest extends TestCase {
    protected function setUp(): void { wp_stub_reset(); }

    public function testAssetUrlPointsIntoAssetsFolder() {
        $this->assertSame('http://example.test/theme/assets/img/logo.webp', ava_getAssetUrl('img/logo.webp'));
    }

    public function testWeekDaysStartMondayAndHaveSevenDays() {
        $days = ava_getWeekDays();
        $this->assertCount(7, $days);
        $this->assertSame('Montag', $days[0]);
        $this->assertSame('Sonntag', $days[6]);
    }

    public function testGetMetaReadsPostMeta() {
        $GLOBALS['wp_stub']['meta'][5]['gegner'] = 'KSC Goldbach';
        $this->assertSame('KSC Goldbach', ava_getMeta(5, 'gegner'));
    }

    public function testPostImagePrefersFeaturedImage() {
        $GLOBALS['wp_stub']['thumbs'][7] = 'http://x/featured.jpg';
        $GLOBALS['wp_stub']['meta'][7]['_demo'] = 'coach-1.webp';
        $this->assertSame('http://x/featured.jpg', ava_getPostImageUrl(7));
    }

    public function testPostImageFallsBackToDemoImage() {
        $GLOBALS['wp_stub']['meta'][7]['_demo'] = 'coach-1.webp';
        $this->assertSame('http://example.test/theme/assets/img/coach-1.webp', ava_getPostImageUrl(7));
    }

    public function testPostImageIsEmptyWithoutAnyImage() {
        $this->assertSame('', ava_getPostImageUrl(8));
    }

    public function testGetPostsMergesOverridesIntoDefaults() {
        ava_getPosts('trainer', ['numberposts' => 1]);
        $args = $GLOBALS['wp_stub']['last_get_posts'];
        $this->assertSame('trainer', $args['post_type']);
        $this->assertSame(1, $args['numberposts']);
        $this->assertSame('ASC', $args['orderby']['menu_order']);
    }

    /**
     * ava_getOption caches the saved options for the whole request,
     * so it needs its own PHP process.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetOptionUsesSavedValueThenDefault() {
        $GLOBALS['wp_stub']['options']['ava_opts'] = ['hero_l1' => 'Gespeichert'];
        $this->assertSame('Gespeichert', ava_getOption('hero_l1'));
        $this->assertSame('Ringen', ava_getOption('hero_l2'));
        $this->assertSame('', ava_getOption('does_not_exist'));
    }

    public function testSanitizeUrlAndMediaRejectNonUrls() {
        $this->assertSame('', ava_sanitizeField(['L', 'url'], 'javascript:alert(1)'));
        $this->assertSame('https://a.de/x.png', ava_sanitizeField(['L', 'media'], 'https://a.de/x.png'));
    }

    public function testSanitizeCheckboxOnlyAcceptsOne() {
        $this->assertSame('1', ava_sanitizeField(['L', 'check'], '1'));
        $this->assertSame('', ava_sanitizeField(['L', 'check'], 'yes'));
        $this->assertSame('', ava_sanitizeField(['L', 'check'], ''));
    }

    public function testSanitizeSelectFallsBackToFirstOption() {
        $field = ['L', 'select', ['1' => 'Heim', '0' => 'Auswärts']];
        $this->assertSame('0', ava_sanitizeField($field, '0'));
        $this->assertSame('1', ava_sanitizeField($field, 'evil'));
    }

    public function testSanitizeTextStripsTags() {
        $this->assertSame('alert(1)', ava_sanitizeField(['L', 'text'], '<script>alert(1)</script>'));
    }

    public function testRenderFieldEscapesValue() {
        ob_start();
        ava_renderField('n', ['L', 'text'], '"><script>');
        $html = ob_get_clean();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('type="text"', $html);
    }

    public function testRenderFieldPicksElementByType() {
        $render = function ($field, $value) {
            ob_start();
            ava_renderField('n', $field, $value);
            return ob_get_clean();
        };
        $this->assertStringContainsString('<textarea', $render(['L', 'textarea'], 'x'));
        $this->assertStringContainsString('value="b" selected="selected"', $render(['L', 'select', ['a' => 'A', 'b' => 'B']], 'b'));
        $this->assertStringContainsString('checked="checked"', $render(['L', 'check'], '1'));
        $this->assertStringContainsString('ava-media', $render(['L', 'media'], ''));
    }
}
