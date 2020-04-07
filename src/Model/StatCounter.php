<?php

namespace Dynamic\Elements\StatCounters\Model;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use phpDocumentor\Reflection\Element;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;

/**
 * Class StatCounter
 * @package Dynamic\Elements\StatCounters\Model
 */
class StatCounter extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'Statistic' => 'Decimal',
        'Label' => 'Varchar(20)',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'ElementStatCounters' => ElementStatCounters::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Statistic',
        'Label',
        'Title',
    ];

    /**
     * @var string
     */
    private static $table_name = 'StatCounterObject';

    /**
     * @return FieldList|void
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                'ElementStatCountersID',
            ]);

            $fields->addFieldsToTab(
                'Root.Main',
                [
                    $fields->dataFieldByName('Statistic'),
                    $fields->dataFieldByName('Label'),
                ],
                'Title'
            );
        });

        return parent::getCMSFields();
    }
}
