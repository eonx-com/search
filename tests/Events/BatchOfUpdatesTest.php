<?php declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Events;

use LoyaltyCorp\Search\Events\BatchOfUpdates;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Events\BatchOfUpdates
 */
final class BatchOfUpdatesTest extends TestCase
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
