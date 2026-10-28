<?php
use PHPUnit\Framework\TestCase;

/** Tests for inc/content-types.php */
class ContentTypesTest extends TestCase {
    protected function setUp(): void { wp_stub_reset(); }

    public function testAllExpectedTypesExist() {
        $this->assertSame(
            ['sponsor', 'training', 'trainer', 'mitgliedschaft'],
            array_keys(ava_getContentTypes())
        );
    }

    public function testEveryTypeIsWellFormed() {
        foreach (ava_getContentTypes() as $name => $type) {
            $this->assertCount(2, $type['n'], "$name labels");
            $this->assertNotEmpty($type['s'], "$name supports");
            foreach ($type['f'] as $key => $field) {
                $this->assertGreaterThanOrEqual(2, count($field), "$name.$key");
                if ($field[1] === 'select') $this->assertIsArray($field[2], "$name.$key options");
            }
        }
    }

    public function testTrainingWeekdayOptionsAreTheWeekDays() {
        $options = ava_getContentTypes()['training']['f']['tag'][2];
        $this->assertSame(ava_getWeekDays(), array_keys($options));
    }

    public function testInitHookRegistersEveryPostType() {
        foreach ($GLOBALS['wp_hooks']['init'] as $callback) $callback();
        $this->assertEqualsCanonicalizing(
            array_keys(ava_getContentTypes()),
            array_keys($GLOBALS['wp_stub']['post_types'])
        );
        $this->assertFalse($GLOBALS['wp_stub']['post_types']['sponsor']['public']);
    }
}
