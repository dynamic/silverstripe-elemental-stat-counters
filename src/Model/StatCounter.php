<?php

namespace Dynamic\Elements\StatCounters\Model;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use phpDocumentor\Reflection\Element;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
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
     * a row has a Live version, so repeated builds publish nothing. The build
     * output reports the number published, when there is at least one.
     *
     * Stats whose parent block has no Live row (never published, or missing)
     * are published too. This is intentional: publishRecursive() on a block
     * skips a Version 0 stat, so one left behind here would stay off the live
     * page when its block is published. The block's template does not render a
     * stat without a Live block, but archiving a never-published block leaves
     * its stats' Live rows in place.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $published = $this->publishUnversionedStats();
        if ($published > 0) {
            DB::alteration_message(
                sprintf('Published %d existing stat counter(s) to Live', $published),
                'changed'
            );
        }
    }

    /**
     * Publish stats that predate Versioned and have no Live row.
     *
     * @return int The number of stats published
     */
    protected function publishUnversionedStats(): int
    {
        $table = DataObject::getSchema()->baseDataTable(static::class);
        $liveTable = $this->stageTable($table, Versioned::LIVE);

        $stats = Versioned::get_by_stage(static::class, Versioned::DRAFT)
            ->filter('Version', 0)
            ->where(sprintf(
                'NOT EXISTS (SELECT 1 FROM "%1$s" WHERE "%1$s"."ID" = "%2$s"."ID")',
                $liveTable,
                $table
            ));

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
