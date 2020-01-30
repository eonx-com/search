<?php declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Events;

use LoyaltyCorp\Search\Events\BatchOfUpdates;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Events\BatchOfUpdates
 */
final class BatchOfUpdatesTest extends UnitTestCase
{
    /**
     * Test `getUpdates` returns iterable.
     */
    public function testGetUpdates(): void
    {
        $batchOfUpdates = new BatchOfUpdates([]);

        self::assertSame([], $batchOfUpdates->getUpdates());
    }
}
