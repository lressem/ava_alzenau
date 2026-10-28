<?php
use PHPUnit\Framework\TestCase;

/** Tests for inc/i18n.php and inc/translations-en.php */
class I18nTest extends TestCase {
    protected function setUp(): void { wp_stub_reset(); }

    public function testWithoutPolylangTextStaysGerman() {
        $this->assertSame('Tickets', ava_t('Tickets'));
        $this->assertSame('Mitglieder', ava_t('Mitglieder'));
    }

    public function testEchoShortcutEscapesHtml() {
        ob_start();
        ava_e('<b>x</b>');
        $this->assertSame('&lt;b&gt;x&lt;/b&gt;', ob_get_clean());
    }

    public function testLanguageSwitcherPrintsNothingWithoutPolylang() {
        ob_start();
        ava_renderLanguageSwitcher();
        $this->assertSame('', ob_get_clean());
    }

    /** @runInSeparateProcess @preserveGlobalState disabled */
    public function testEnglishDefaultsAreUsedWhenEnglishIsActive() {
        function pll__($s) { return $s; }
        function pll_current_language() { return 'en'; }
        $this->assertSame('Home', ava_t('Startseite'));
        $this->assertSame('Children & youth training', ava_t('Kinder & Jugendtraining'));
        $this->assertSame('Children & youth training', ava_t('Kinder &amp; Jugendtraining'));
        $this->assertSame('RWG first-team training', ava_t('Training 1. Mannschaft RWG'));
        $this->assertSame('Intensive youth training', ava_t('Jugendintensiv-Training'));
        $this->assertSame('Strength and conditioning', ava_t('Athletik-Training'));
        $this->assertSame('Bambini training', ava_t('Bambini-Training'));
        $this->assertSame('Family membership', ava_t('Familienmitgliedschaft'));
        $this->assertSame('Use of the fitness and strength room*', ava_t('Nutzung des Fitness- &amp; Kraftraums*'));
        $this->assertSame('Unknown text', ava_t('Unknown text'));
    }

    /** @runInSeparateProcess @preserveGlobalState disabled */
    public function testEnglishDefaultsAreUsedForLocaleStyleLanguageCode() {
        function pll__($s) { return $s; }
        function pll_current_language() { return 'en_US'; }
        $this->assertSame('Children & youth training', ava_t('Kinder & Jugendtraining'));
    }

    /** @runInSeparateProcess @preserveGlobalState disabled */
    public function testEditorTranslationBeatsBuiltInDefault() {
        function pll__($s) { return $s === 'Startseite' ? 'Start' : $s; }
        function pll_current_language() { return 'en'; }
        $this->assertSame('Start', ava_t('Startseite'));
    }

    /** @runInSeparateProcess @preserveGlobalState disabled */
    public function testGermanSiteNeverUsesEnglishDefaults() {
        function pll__($s) { return $s; }
        function pll_current_language() { return 'de'; }
        $this->assertSame('Startseite', ava_t('Startseite'));
    }

    /** @runInSeparateProcess @preserveGlobalState disabled */
    public function testSavedTextSettingIsTranslatedButUrlIsNot() {
        function pll__($s) { return $s === 'Hallo' ? 'Hello' : $s; }
        function pll_current_language() { return 'en'; }
        $GLOBALS['wp_stub']['options']['ava_opts'] = ['hero_l1' => 'Hallo', 'video_link' => 'Hallo'];
        $this->assertSame('Hello', ava_getOption('hero_l1'));
        $this->assertSame('Hallo', ava_getOption('video_link'));
    }

    public function testEveryTextSettingDefaultHasAnEnglishTranslation() {
        $english = ava_getEnglishStrings();
        foreach (ava_getThemeSettingFields() as $key => $field) {
            if (in_array($field[1], ['text', 'textarea'], true) && $field[2] !== '' && !preg_match('/^[0-9+() ]+$|@|^\d+\+?$/', $field[2]) && !preg_match('/^(Alexander Staab|Sebastian Amreihn|#liga)$/', $field[2])) {
                $this->assertArrayHasKey($field[2], $english, "setting $key");
            }
        }
    }

    public function testEveryTextUsedInTemplatesHasAnEnglishTranslation() {
        $english = ava_getEnglishStrings();
        $root = __DIR__ . '/..';
        foreach (['header.php', 'footer.php', 'functions.php', 'front-page.php'] as $file) {
            $code = file_get_contents("$root/$file");
            preg_match_all("/ava_(?:t|e)\\('((?:[^'\\\\]|\\\\.)*)'\\)/u", $code, $m);
            foreach ($m[1] as $text) {
                $text = stripslashes($text);
                $this->assertArrayHasKey($text, $english, "$file: $text");
            }
        }
    }

    public function testFallbackListsAndNewsPlaceholdersAreTranslated() {
        $english = ava_getEnglishStrings();
        foreach (['Aktuelles', 'Liga', 'Impressum', 'Datenschutz', 'Vereinssatzung', 'Montag', 'Sonntag', 'Heim', 'Gast', 'Anmelden'] as $text) {
            $this->assertArrayHasKey($text, $english);
        }
    }

    public function testPolylangConfigDeclaresEveryContentType() {
        $xml = simplexml_load_file(__DIR__ . '/../wpml-config.xml');
        $declared = array_map('strval', $xml->xpath('//custom-types/custom-type'));
        $this->assertEqualsCanonicalizing(array_keys(ava_getContentTypes()), $declared);
    }

    public function testPolylangConfigCoversEveryCustomField() {
        $xml = simplexml_load_file(__DIR__ . '/../wpml-config.xml');
        $declared = array_map('strval', $xml->xpath('//custom-fields/custom-field'));
        foreach (ava_getContentTypes() as $type => $config) {
            foreach (array_keys($config['f']) as $key) {
                $this->assertContains($key, $declared, "$type.$key");
            }
        }
    }
}
