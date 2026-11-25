<?php

namespace Dynamic\Elements\StatCounters\Test\Model;

use Dynamic\Elements\StatCounters\Elements\ElementStatCounters;
use Dynamic\Elements\StatCounters\Model\StatCounter;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBPercentage;
use SilverStripe\ORM\Queries\SQLDelete;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverStripe\Versioned\Versioned;

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

    public function testIsVersioned()
    {
        $this->assertTrue(StatCounter::singleton()->hasExtension(Versioned::class));
    }

    public function testElementOwnsStats()
    {
        $this->assertContains('Stats', ElementStatCounters::singleton()->config()->get('owns'));
    }

    /**
     * Editing a stat on a published block in draft must not change the Live
     * stage until the block is published again.
     */
    public function testDraftEditDoesNotChangeLiveUntilPublishRecursive()
    {
        /** @var ElementStatCounters $element */
        $element = $this->objFromFixture(ElementStatCounters::class, 'one');
        $stat = StatCounter::create(['Title' => 'Original', 'StatType' => 'Int']);
        $stat->ElementStatCountersID = $element->ID;
        $stat->write();

        $element->publishRecursive();
        $this->assertSame('Original', $this->liveStat($stat->ID)?->Title);

        $stat->Title = 'Edited';
        $stat->write();

        $this->assertSame('Original', $this->liveStat($stat->ID)?->Title, 'Draft edit must not reach Live');
        $this->assertSame('Edited', $this->draftStat($stat->ID)->Title);

        $element = ElementStatCounters::get()->byID($element->ID);
        $element->publishRecursive();

        $this->assertSame('Edited', $this->liveStat($stat->ID)?->Title, 'publishRecursive() publishes the stat');
    }

    /**
     * A stat that predates Versioned (no Live row, never written through
     * Versioned) is published to Live by the build step.
     */
    public function testBuildPublishesStatsThatPredateVersioned()
    {
        $stat = $this->objFromFixture(StatCounter::class, 'one');
        $this->makeUnversioned($stat->ID);
        $this->assertNull($this->liveStat($stat->ID));

        StatCounter::singleton()->requireDefaultRecords();

        $live = $this->liveStat($stat->ID);
        $this->assertNotNull($live, 'Pre-Versioned stat should be on Live after the build step');
        $this->assertSame($stat->Title, $live->Title);

        $draft = $this->draftStat($stat->ID);
        $this->assertSame($draft->Version, $live->Version);
        $this->assertFalse($draft->stagesDiffer(), 'Published stat should not show as modified');
    }

    /**
     * Publishing only touches the stat itself, never the parent block.
     */
    public function testBuildDoesNotPublishParentBlock()
    {
        /** @var ElementStatCounters $element */
        $element = $this->objFromFixture(ElementStatCounters::class, 'one');
        $stat = StatCounter::create(['Title' => 'Legacy', 'StatType' => 'Int']);
        $stat->ElementStatCountersID = $element->ID;
        $stat->write();
        $this->makeUnversioned($stat->ID);

        StatCounter::singleton()->requireDefaultRecords();

        $this->assertNotNull($this->liveStat($stat->ID));
        $liveElement = Versioned::get_by_stage(ElementStatCounters::class, Versioned::LIVE)->byID($element->ID);
        $this->assertNull($liveElement, 'The parent block must not be published');
    }

    public function testBuildIsIdempotent()
    {
        $stat = $this->objFromFixture(StatCounter::class, 'one');
        $this->makeUnversioned($stat->ID);

        StatCounter::singleton()->requireDefaultRecords();
        $liveVersion = $this->liveStat($stat->ID)->Version;
        $versionRows = $this->versionRowCount($stat->ID);
        $liveRows = Versioned::get_by_stage(StatCounter::class, Versioned::LIVE)->count();

        StatCounter::singleton()->requireDefaultRecords();

        $this->assertSame($liveVersion, $this->liveStat($stat->ID)->Version);
        $this->assertSame($versionRows, $this->versionRowCount($stat->ID), 'A second build must write no new version');
        $this->assertSame(
            $liveRows,
            Versioned::get_by_stage(StatCounter::class, Versioned::LIVE)->count()
        );
    }

    /**
     * A row that already has a Live version keeps it, even when the draft
     * differs and looks pre-Versioned.
     */
    public function testBuildDoesNotOverwriteExistingLiveVersion()
    {
        $stat = $this->objFromFixture(StatCounter::class, 'one');
        $stat->Title = 'Published title';
        $stat->write();
        $stat->publishSingle();

        $stat->Title = 'Unpublished draft change';
        $stat->write();
        // Make the draft row look like a pre-Versioned one while keeping its Live row.
        SQLUpdate::create('"StatCounterObject"', ['"Version"' => 0], ['"ID"' => $stat->ID])->execute();

        StatCounter::singleton()->requireDefaultRecords();

        $this->assertSame('Published title', $this->liveStat($stat->ID)->Title);
        $this->assertSame('Unpublished draft change', $this->draftStat($stat->ID)->Title);
    }

    /**
     * A stat added in draft after the upgrade is not published by dev/build.
     */
    public function testBuildLeavesDraftOnlyStatsAlone()
    {
        $stat = $this->objFromFixture(StatCounter::class, 'one');
        $this->assertNull($this->liveStat($stat->ID));

        StatCounter::singleton()->requireDefaultRecords();

        $this->assertNull($this->liveStat($stat->ID), 'A draft-only stat must stay off Live');
    }

    /**
     * Make a stat look like a row from before Versioned was added: no Live
     * row, no version history, Version = 0.
     */
    private function makeUnversioned(int $id): void
    {
        SQLDelete::create('"StatCounterObject_Live"', ['"ID"' => $id])->execute();
        SQLDelete::create('"StatCounterObject_Versions"', ['"RecordID"' => $id])->execute();
        SQLUpdate::create('"StatCounterObject"', ['"Version"' => 0], ['"ID"' => $id])->execute();
    }

    private function versionRowCount(int $id): int
    {
        return (int) SQLSelect::create('COUNT(*)', '"StatCounterObject_Versions"', ['"RecordID"' => $id])
            ->execute()
            ->value();
    }

    private function liveStat(int $id): ?StatCounter
    {
        return Versioned::get_by_stage(StatCounter::class, Versioned::LIVE)->byID($id);
    }

    private function draftStat(int $id): ?StatCounter
    {
        return Versioned::get_by_stage(StatCounter::class, Versioned::DRAFT)->byID($id);
    }
}
