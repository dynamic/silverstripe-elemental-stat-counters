<?php

namespace Dynamic\Elements\StatCounters\Test\Elements;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

/**
 * Class ElementStatCountersTest
 * @package Dynamic\Elements\StatCounters\Test\Elements
 */
class ElementStatCountersTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture(ElementStatCounters::class, 'one');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }
}
