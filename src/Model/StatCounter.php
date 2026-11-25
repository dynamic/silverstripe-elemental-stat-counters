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
use SilverStripe\Versioned\Versioned;

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
 * @mixin Versioned
 */
class StatCounter extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
        'Statistic' => 'Varchar',
        'Label' => 'Varchar(20)',
        'SortOrder' => 'Int',
        'StatType' => 'Enum(array("Natural","Int","Decimal","Currency","Percentage"))',
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
     * @var string[]
     */
    private static $extensions = [
        Versioned::class,
    ];

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
                        ->setTitle('Setting the stat type helps ensure the number is formatted properly')
                        ->setDescription('Note: the Natural option displays the number as you type it in'),
                ])
            );
        });

        return parent::getCMSFields();
    }

    /**
     * One-time publish of stats that predate the Versioned extension.
     *
     * Rows written before StatCounter was Versioned have no Live row and were
     * never written through Versioned (Version = 0). Without a Live row they
     * would disappear from published pages after dev/build, so publish exactly
     * those rows to Live. Only the stat itself is published, never its parent
     * block. Stats created through Versioned afterwards (Version > 0) are left
     * alone, so a draft-only stat is not published by a later dev/build, and a
     * row that already has a Live version is never overwritten. Once published
     * a row has a Live version, so repeated builds publish nothing.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $this->publishUnversionedStats();
    }

    /**
     * Publish stats that predate Versioned and have no Live row.
     *
     * @return int The number of stats published
     */
    protected function publishUnversionedStats(): int
    {
        $stats = Versioned::get_by_stage(static::class, Versioned::DRAFT)
            ->filter('Version', 0);

        $liveIDs = Versioned::get_by_stage(static::class, Versioned::LIVE)->column('ID');
        if ($liveIDs) {
            $stats = $stats->exclude('ID', $liveIDs);
        }

        $published = 0;
        /** @var StatCounter $stat */
        foreach ($stats as $stat) {
            $stat->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
            $published++;
        }

        return $published;
    }

    /**
     * @return DBField
     */
    public function getStatNumber()
    {
        return $this->StatType == 'Natural'
            ? $this->Statistic
            : DBField::create_field($this->StatType, $this->Statistic);
    }
}
