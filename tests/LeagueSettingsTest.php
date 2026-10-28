<?php
use PHPUnit\Framework\TestCase;

/** Tests for inc/league-settings.php */
class LeagueSettingsTest extends TestCase {
    protected function setUp(): void { wp_stub_reset(); }

    public function testDefaultsAreUsedWhenNothingIsSaved() {
        $this->assertSame('1257', ava_getLeagueOption('saison_liga_id'));
        $this->assertSame('', ava_getLeagueOption('kampf_tag_id'));
    }

    public function testSavedValueOverridesDefault() {
        update_option('ava_liga', ['saison_liga_id' => '42']);
        $this->assertSame('42', ava_getLeagueOption('saison_liga_id'));
    }

    public function testSanitizeKeepsPositiveIntegersOnly() {
        $clean = ava_sanitizeLeagueSettings(['saison_liga_id' => '7', 'kampf_tag_id' => 'abc', 'eigene_org_id' => '-5']);
        $this->assertSame(['saison_liga_id' => '7', 'kampf_tag_id' => '', 'eigene_org_id' => '5'], $clean);
    }

    public function testEmptySaisonLigaIdFallsBackToDefault() {
        $this->assertSame('1257', ava_sanitizeLeagueSettings([])['saison_liga_id']);
    }
}
