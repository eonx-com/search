<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Laravel\Listeners;

use LoyaltyCorp\Search\Bridge\Laravel\Listeners\BatchOfUpdatesListener;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use Tests\LoyaltyCorp\Search\Stubs\UpdateProcessorStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Listeners\BatchOfUpdatesListener
 */
final class BatchOfUpdatesListenerTest extends UnitTestCase
{
    /**
     * Tests that the listener calls the update processor with batch of updates.
     *
     * @return void
     */
    public function testHandle(): void
    {
        $updateProcessor = new UpdateProcessorStub();

        $listener = new BatchOfUpdatesListener($updateProcessor);

        $expectedProcessCalls = [
            [
                'indexSuffix' => 'suffix',
                'updates' => []
            ],
        ];

        $listener->handle(new BatchOfUpdatesEvent('suffix', []));

        self::assertSame($expectedProcessCalls, $updateProcessor->getCalls('process'));
    }
}
