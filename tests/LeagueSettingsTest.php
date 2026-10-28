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

    public function testSanitizeKeepsPositiveIntegersAndValidDatesOnly() {
        $clean = ava_sanitizeLeagueSettings([
            'saison_liga_id' => '7', 'kampf_tag_id' => 'abc', 'mannschaften_id' => '13503',
            'kampftag' => '2026-10-31', 'logischer_kampftag' => '-5', 'eigene_org_id' => '',
        ]);
        $this->assertSame([
            'saison_liga_id' => '7', 'kampf_tag_id' => '', 'mannschaften_id' => '13503',
            'kampftag' => '2026-10-31', 'logischer_kampftag' => '5', 'eigene_org_id' => '',
        ], $clean);
    }

    public function testSanitizeRejectsInvalidDate() {
        $this->assertSame('', ava_sanitizeLeagueSettings(['kampftag' => '31.10.2026'])['kampftag']);
    }

    public function testFindNextAndLastMatch() {
        $matches = [
            ['KampftagIst' => '2026-10-10T00:00:00', 'PunkteHeimWertung' => 23.0, 'PunkteGastWertung' => 4.0],
            ['KampftagIst' => '2026-10-16T00:00:00', 'PunkteHeimWertung' => 14.0, 'PunkteGastWertung' => 7.0],
            ['KampftagIst' => '2026-10-31T00:00:00', 'PunkteHeimWertung' => null, 'PunkteGastWertung' => null],
            ['KampftagIst' => '2026-11-07T00:00:00', 'PunkteHeimWertung' => null, 'PunkteGastWertung' => null],
        ];
        $result = ava_findNextAndLastMatch($matches, '2026-10-28');
        $this->assertSame('2026-10-31T00:00:00', $result['next']['KampftagIst']);
        $this->assertSame('2026-10-16T00:00:00', $result['last']['KampftagIst']);
    }

    public function testMatchWithoutResultInThePastIsNotTheNextMatch() {
        $matches = [['KampftagIst' => '2026-10-10T00:00:00', 'PunkteHeimWertung' => null, 'PunkteGastWertung' => null]];
        $this->assertNull(ava_findNextAndLastMatch($matches, '2026-10-28')['next']);
    }

    public function testEmptySaisonLigaIdFallsBackToDefault() {
        $this->assertSame('1257', ava_sanitizeLeagueSettings([])['saison_liga_id']);
    }
}
