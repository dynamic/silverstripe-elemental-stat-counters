<?php

namespace Dynamic\Elements\StatCounters\Elements;

use DNADesign\Elemental\Models\BaseElement;
use Dynamic\Elements\StatCounters\Model\StatCounter;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Class ElementStatCounters
 * @package Dynamic\Elements\StatCounters\Elements
 */
class ElementStatCounters extends BaseElement
{
    /**
     * @var string
     */
    //private static $icon = 'font-icon-block-banner';

    /**
     * @return string
     */
    private static $singular_name = 'Stat Counters Element';

    /**
     * @return string
     */
    private static $plural_name = 'Stat Counters Elements';

    /**
     * @var array
     */
    private static $has_many = [
        'Stats' => StatCounter::class,
    ];

    /**
     * @var string
     */
    private static $table_name = 'ElementStatCounters';

    /**
     * @var bool
     */
    private static $inline_editable = false;

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            if ($stats = $fields->dataFieldByName('Stats')) {
                $fields->removeByName('Stats');
                $config = $stats->getConfig();
                $config->removeComponentsByType([
                    GridFieldAddExistingAutocompleter::class,
                    GridFieldDeleteAction::class,
                    GridFieldAddNewButton::class,
                ])->addComponents([
                    new GridFieldDeleteAction(false),
                    GridFieldOrderableRows::create('SortOrder'),
                    $columns = new GridFieldEditableColumns(),
                    $addButton = new GridFieldAddNewInlineButton()
                ]);

                $addButton->setTitle('Add Stat Counter');

                $fields->addFieldToTab('Root.Main', $stats);
            }
        });

        return parent::getCMSFields();
    }

    /**
     * @return DBHTMLText
     */
    public function getSummary()
    {
        if ($this->Stats()->count() == 1) {
            $label = ' stat';
        } else {
            $label = ' stats';
        }
        return DBField::create_field('HTMLText', $this->Stats()->count() . $label)->Summary(20);
    }

    /**
     * @return array
     */
    protected function provideBlockSchema()
    {
        $blockSchema = parent::provideBlockSchema();
        $blockSchema['content'] = $this->getSummary();
        return $blockSchema;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return _t(__CLASS__ . '.BlockType', 'Stat Counters');
    }
}
