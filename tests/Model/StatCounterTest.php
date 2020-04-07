<?php

namespace Dynamic\Elements\StatCounters\Test\Model;

use Dynamic\Elements\StatCounters\Model\StatCounter;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

/**
 * Class StatCounterTest
 * @package Dynamic\Elements\StatCounters\Test\Model
 */
class StatCounterTest extends SapphireTest
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
        $object = $this->objFromFixture(StatCounter::class, 'one');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }
}
