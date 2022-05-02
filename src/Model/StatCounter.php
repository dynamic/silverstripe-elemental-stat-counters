<?php

namespace Dynamic\Elements\StatCounters\Model;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use phpDocumentor\Reflection\Element;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBPercentage;

/**
 * Class StatCounter
 * @package Dynamic\Elements\StatCounters\Model
 *
 * @property string $Title
 * @property float $Statistic
 * @property string $Label
 * @property int $SortOrder
 * @property string $StatType
 * @property int $ElementStatCounters
 *
 * @method ElementStatCounters ElementStatCounters
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
        'SortOrder' => 'Int',
        'StatType' => 'Enum(array("Int","Decimal","Currency","Percentage"))',
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
        'Label',
        'Statistic',
        'Title',
        'StatType',
    ];

    /**
     * @var string[]
     */
    private static $defaults = [
        'StatType' => 'Int',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'SortOrder';

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
            $label = $fields->dataFieldByName('Label');
            $stat = $fields->dataFieldByName('Statistic');
            $title = $fields->dataFieldByName('Title');

            $fields->removeByName([
                'ElementStatCountersID',
                'SortOrder',
                'SiteConfigID',
                'Statistic',
                'Title',
                'Label',
                'StatType',
            ]);

            $fields->addFieldToTab(
                'Root.Main',
                FieldGroup::create([
                    $label,
                    $stat,
                    $title,
                    DropdownField::create('StatType')
                        ->setSource($this->dbObject('StatType')->enumValues())
                        ->setTitle('Setting the stat type helps ensure the number is formatted properly'),
                ])
            );
        });

        return parent::getCMSFields();
    }

    /**
     * @return DBField
     */
    public function getStatNumber()
    {
        return DBField::create_field($this->StatType, $this->Statistic);
    }
}
