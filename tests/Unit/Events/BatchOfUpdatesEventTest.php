<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Events;

use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Events\BatchOfUpdatesEvent
 */
final class BatchOfUpdatesEventTest extends UnitTestCase
{
    /**
     * Test `getUpdates` returns iterable.
     *
     * @return void
     */
    public function testGetUpdates(): void
    {
        $batchOfUpdates = new BatchOfUpdatesEvent([]);

        self::assertSame([], $batchOfUpdates->getUpdates());
    }
}
