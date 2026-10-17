<?php

namespace Dynamic\Elements\StatCounters\Test\Elements;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use Dynamic\Elements\StatCounters\Model\StatCounter;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\Requirements;

/**
 * Tests the JavaScript the module's default front end template asks for.
 *
 * Regression coverage for GH #12: the template used to load a jQuery build of
 * Waypoints that the module never paired with jQuery itself, so the counter
 * init threw on a front end without jQuery.
 *
 * @package Dynamic\Elements\StatCounters\Test\Elements
 */
class ElementStatCountersTemplateTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     * Render the element's default template and return every JavaScript file it
     * registered on the Requirements backend.
     *
     * The backend is cleared both before and after, so the render cannot leak
     * into another test's requirements.
     *
     * @return string[] Script paths/URLs registered by the template
     */
    private function getJavascriptRequiredByTemplate(): array
    {
        /** @var ElementStatCounters $element */
        $element = $this->objFromFixture(ElementStatCounters::class, 'one');

        // The template only emits its requires inside <% if $Stats %>, so the
        // element needs at least one stat for this to be a real render.
        StatCounter::create([
            'Title' => 'Regression stat',
            'Statistic' => '99.7',
            'Label' => 'Uptime',
            'StatType' => 'Natural',
            'ElementStatCountersID' => $element->ID,
        ])->write();

        $backend = Requirements::backend();
        $backend->clear();

        try {
            $element->forTemplate();

            return array_keys($backend->getJavascript() ?? []);
        } finally {
            $backend->clear();
        }
    }

    /**
     * The scripts the counter animation actually needs are still required.
     */
    public function testTemplateRequiresCounterUpAndInitScript(): void
    {
        $scripts = $this->getJavascriptRequiredByTemplate();
        $haystack = strtolower(implode("\n", $scripts));

        $this->assertStringContainsString(
            'counter-up.min.js',
            $haystack,
            'The default template should still require Counter-Up2'
        );
        $this->assertStringContainsString(
            'counterup-block_init.js',
            $haystack,
            'The default template should still require the counter init script'
        );
    }

    /**
     * GH #12: the template must not drag in the Waypoints bridge any more -
     * it needed a library the module never loaded.
     */
    public function testTemplateDoesNotRequireWaypoints(): void
    {
        $scripts = $this->getJavascriptRequiredByTemplate();

        foreach ($scripts as $script) {
            $this->assertStringNotContainsString(
                'waypoints',
                strtolower($script),
                'The default template should not require Waypoints'
            );
        }
    }

    /**
     * Nothing the default template requires should come from a third party CDN.
     */
    public function testTemplateRequiresNoRemoteScripts(): void
    {
        $scripts = $this->getJavascriptRequiredByTemplate();

        $this->assertNotEmpty($scripts, 'Precondition: the render should register scripts at all');

        foreach ($scripts as $script) {
            $this->assertStringNotContainsString(
                '://',
                $script,
                'The default template should not require a script from a remote host'
            );
        }
    }
}
