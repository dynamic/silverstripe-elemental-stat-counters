<?php

namespace Dynamic\Elements\StatCounters\Test\Model;

use Dynamic\Elements\StatCounters\Model\StatCounter;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBPercentage;

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

    public function testIntCast()
    {
        /** @var StatCounter $stat */
        $stat = $this->objFromFixture(StatCounter::class, 'one');
        $this->assertInstanceOf(DBInt::class, $stat->getStatNumber());
    }

    public function testDecimalCast()
    {
        /** @var StatCounter $stat */
        $stat = $this->objFromFixture(StatCounter::class, 'two');
        $this->assertInstanceOf(DBDecimal::class, $stat->getStatNumber());
    }

    public function testCurrencyCast()
    {
        /** @var StatCounter $stat */
        $stat = $this->objFromFixture(StatCounter::class, 'three');
        $this->assertInstanceOf(DBCurrency::class, $stat->getStatNumber());
    }

    public function testPercentageCast()
    {
        /** @var StatCounter $stat */
        $stat = $this->objFromFixture(StatCounter::class, 'four');
        $this->assertInstanceOf(DBPercentage::class, $stat->getStatNumber());
    }
}
