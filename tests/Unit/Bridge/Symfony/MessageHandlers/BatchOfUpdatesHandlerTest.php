<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\MessageHandlers;

use LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers\BatchOfUpdatesHandler;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use Tests\LoyaltyCorp\Search\Stubs\UpdateProcessorStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers\BatchOfUpdatesHandler
 */
final class BatchOfUpdatesHandlerTest extends UnitTestCase
{
    /**
     * Tests that the listener calls the update processor with batch of updates.
     *
     * @return void
     */
    public function testHandle(): void
    {
        $updateProcessor = new UpdateProcessorStub();

        $handler = new BatchOfUpdatesHandler($updateProcessor);

        $expectedProcessCalls = [
            [
                'indexSuffix' => 'suffix',
                'updates' => [],
            ],
        ];

        $handler(new BatchOfUpdatesEvent('suffix', []));

        self::assertSame($expectedProcessCalls, $updateProcessor->getCalls('process'));
    }
}
