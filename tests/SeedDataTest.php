<?php
use PHPUnit\Framework\TestCase;

/** Tests for inc/seed-data.php */
class SeedDataTest extends TestCase {
    protected function setUp(): void { wp_stub_reset(); }

    private function countOfType($type) {
        return count(array_filter($GLOBALS['wp_stub']['posts'], fn($p) => $p['post_type'] === $type));
    }

    public function testSeedIsHookedToThemeActivation() {
        $this->assertContains('ava_seedDemoData', $GLOBALS['wp_hooks']['after_switch_theme']);
    }

    public function testSeedCreatesDemoContent() {
        ava_seedDemoData();
        $this->assertSame(10, $this->countOfType('sponsor'));
        $this->assertSame(6, $this->countOfType('tabelle'));
        $this->assertSame(2, $this->countOfType('kampftag'));
        $this->assertSame(7, $this->countOfType('training'));
        $this->assertSame(6, $this->countOfType('trainer'));
        $this->assertSame(3, $this->countOfType('mitgliedschaft'));
        $this->assertSame(3, $this->countOfType('page'));
        $this->assertSame(1, $GLOBALS['wp_stub']['options']['ava_seeded']);
    }

    public function testSeedRunsOnlyOnce() {
        ava_seedDemoData();
        $created = count($GLOBALS['wp_stub']['posts']);
        ava_seedDemoData();
        $this->assertCount($created, $GLOBALS['wp_stub']['posts']);
    }

    public function testSeedSkipsExistingLegalPages() {
        $GLOBALS['wp_stub']['pages'] = ['impressum'];
        ava_seedDemoData();
        $this->assertSame(2, $this->countOfType('page'));
    }

    public function testSeededTrainingWeekdaysAreValid() {
        ava_seedDemoData();
        foreach ($GLOBALS['wp_stub']['posts'] as $post) {
            if ($post['post_type'] === 'training') {
                $this->assertContains($post['meta_input']['tag'], ava_getWeekDays());
            }
        }
    }
}
