<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Listeners;

use LoyaltyCorp\Search\Bridge\Symfony\Listeners\BatchOfUpdatesListener;
use LoyaltyCorp\Search\Bridge\Symfony\Messages\BatchOfUpdatesMessage;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use Tests\LoyaltyCorp\Search\Stubs\Bridge\Symfony\MessageBusStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Listeners\BatchOfUpdatesListener
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
        $messageBus = new MessageBusStub();

        $listener = new BatchOfUpdatesListener($messageBus);

        $expectedProcessCalls = [
            [
                'message' => new BatchOfUpdatesMessage('suffix', []),
                'stamps' => []
            ]
        ];

        $listener(new BatchOfUpdatesEvent('suffix', []));

        self::assertEquals($expectedProcessCalls, $messageBus->getCalls('dispatch'));
    }
}
