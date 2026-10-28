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
            'manual_table' => [], 'manual_matches' => [],
            'test_api_unavailable' => '',
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

    public function testManualLeagueRowsAreParsedAndInvalidRowsIgnored() {
        $clean = ava_sanitizeLeagueSettings([
            'manual_table' => "1 | RWG Alzenau/Kahl | 12 | 28 | 16 | 1234\ninvalid row",
            'manual_matches' => "2026-10-31 | 19:30 | RWG Alzenau/Kahl | KSV Beispiel | | | 1234 | 5678\nbad row",
        ]);

        $this->assertSame([[
            'Platzierung' => 1,
            'TeamName' => 'RWG Alzenau/Kahl',
            'Differenz' => 12,
            'WertungPlus' => 28,
            'WertungMinus' => 16,
            'OrgID' => 1234,
        ]], ava_parseManualLeagueTable($clean['manual_table']));
        $this->assertSame([[
            'KampftagIst' => '2026-10-31',
            'Beginn' => '19:30',
            'HeimMannschaft' => 'RWG Alzenau/Kahl',
            'GastMannschaft' => 'KSV Beispiel',
            'PunkteHeimWertung' => null,
            'PunkteGastWertung' => null,
            'HeimOrganisationsID' => 1234,
            'GastOrganisationsID' => 5678,
        ]], ava_parseManualLeagueMatches($clean['manual_matches']));
    }

    public function testManualDataIsUsedWhenLigaDbIsUnavailable() {
        update_option('ava_liga', [
            'manual_table' => '1 | RWG Alzenau/Kahl | 12 | 28 | 16 | 1234',
            'manual_matches' => "2026-11-07 | 19:30 | RWG Alzenau/Kahl | KSV Beispiel | | | 1234 | 5678\n2026-10-31 | 19:00 | KSV Beispiel | RWG Alzenau/Kahl | 4 | 20 | 5678 | 1234",
        ]);

        $this->assertSame('RWG Alzenau/Kahl', ava_getLeagueTable()[0]['TeamName']);
        $matches = ava_getLeagueMatches();
        $this->assertSame('2026-10-31', $matches[0]['KampftagIst']);
        $this->assertSame(20, $matches[0]['PunkteGastWertung']);
        $this->assertNull($matches[1]['PunkteHeimWertung']);
    }

    public function testManualAdminRowsAreSanitizedAndSavedAsStructuredData() {
        $clean = ava_sanitizeLeagueSettings([
            'manual_table' => [[
                'Platzierung' => '1',
                'TeamName' => 'RWG Alzenau/Kahl',
                'Differenz' => '12',
                'WertungPlus' => '28',
                'WertungMinus' => '16',
                'OrgID' => '1234',
            ]],
            'manual_matches' => [[
                'KampftagIst' => '2026-10-31',
                'Beginn' => '19:30',
                'HeimMannschaft' => 'RWG Alzenau/Kahl',
                'GastMannschaft' => 'KSV Beispiel',
                'PunkteHeimWertung' => '',
                'PunkteGastWertung' => '',
                'HeimOrganisationsID' => '1234',
                'GastOrganisationsID' => '5678',
            ]],
        ]);

        $this->assertSame('RWG Alzenau/Kahl', $clean['manual_table'][0]['TeamName']);
        $this->assertSame('2026-10-31', $clean['manual_matches'][0]['KampftagIst']);
        $this->assertNull($clean['manual_matches'][0]['PunkteHeimWertung']);
    }

    public function testApiFailureTestModeCanBeEnabledAndDisabled() {
        $this->assertSame('1', ava_sanitizeLeagueSettings(['test_api_unavailable' => '1'])['test_api_unavailable']);
        update_option('ava_liga', ['test_api_unavailable' => '1', 'manual_table' => '1 | Testverein | 5 | 10 | 5 |']);

        $this->assertTrue(ava_isLeagueApiTestMode());
        $this->assertSame('Testverein', ava_getLeagueTable()[0]['TeamName']);

        update_option('ava_liga', ['test_api_unavailable' => '']);
        $this->assertFalse(ava_isLeagueApiTestMode());
    }
}
