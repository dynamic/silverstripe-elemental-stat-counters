<?php

namespace Dynamic\Elements\StatCounters\Test\Elements;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use Dynamic\Elements\StatCounters\Model\StatCounter;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

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

    /**
     * Regression test for GH #26: a saved Stat's Title column in the inline
     * editable grid must render as an editable field, not read-only text.
     *
     * Without an explicit setDisplayFields() call, GridFieldEditableColumns
     * resolves each column for a saved row via the record's own
     * getCMSFields(), which other extensions applied to StatCounter (e.g.
     * essentials-tools' StatCountersExtension) may strip fields from for
     * unrelated reasons - that silently downgrades the column to read-only
     * instead of throwing, so the previous behaviour looked "fine" but
     * quietly lost editability. setDisplayFields() bypasses that lookup.
     */
    public function testTitleColumnIsEditableForSavedStat()
    {
        $element = $this->objFromFixture(ElementStatCounters::class, 'one');
        $fields = $element->getCMSFields();
        $stats = $fields->dataFieldByName('Stats');
        $config = $stats->getConfig();
        /** @var GridFieldEditableColumns $columns */
        $columns = $config->getComponentByType(GridFieldEditableColumns::class);

        // Wire the grid into a real Form context, as the CMS would.
        $form = Form::create(Controller::curr(), 'Form', FieldList::create($stats), FieldList::create());
        $stats->setForm($form);

        $record = $this->objFromFixture(StatCounter::class, 'one');
        $html = $columns->getColumnContent($stats, $record, 'Title');

        $this->assertStringContainsString(
            '<input',
            $html,
            'Title column for a saved Stat should render as an editable input, not read-only text'
        );
    }
}
