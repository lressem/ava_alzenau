<?php
use PHPUnit\Framework\TestCase;

/** Tests for inc/themes-settings.php */
class ThemeSettingsTest extends TestCase {
    protected function setUp(): void { wp_stub_reset(); }

    public function testEverySettingHasLabelTypeDefaultAndSection() {
        foreach (ava_getThemeSettingFields() as $key => $field) {
            $this->assertCount(4, $field, $key);
            $this->assertNotSame('', $field[3], "$key section");
        }
    }

    public function testSettingsOfOneSectionAreGrouped() {
        // The admin page opens a new table whenever the section changes,
        // so a section appearing twice would be rendered twice.
        $sections = array_column(array_values(ava_getThemeSettingFields()), 3);
        $changes = array_values(array_unique($sections));
        $this->assertSame(count($changes), count(array_unique($changes)));
        $runs = 1;
        for ($i = 1; $i < count($sections); $i++) if ($sections[$i] !== $sections[$i - 1]) $runs++;
        $this->assertSame(count($changes), $runs);
    }

    public function testSanitizeSettingsKeepsOnlyKnownKeys() {
        $clean = ava_sanitizeSettings(['hero_l1' => '<b>Hallo</b>', 'unknown' => 'x']);
        $this->assertSame('Hallo', $clean['hero_l1']);
        $this->assertArrayNotHasKey('unknown', $clean);
        $this->assertSame(array_keys(ava_getThemeSettingFields()), array_keys($clean));
    }

    public function testSanitizeSettingsFillsMissingValuesWithEmptyString() {
        $this->assertSame('', ava_sanitizeSettings([])['hero_l1']);
    }
}
