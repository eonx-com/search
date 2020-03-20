<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Listeners;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Bridge\Symfony\Listeners\EntityUpdateListener;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Bridge\Symfony\MessageBusStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Listeners\EntityUpdateListener
 */
final class EntityUpdateListenerTest extends UnitTestCase
{
    /**
     * Tests that the listener calls the worker with changes..
     *
     * @return void
     */
    public function testHandle(): void
    {
        $messageBus = new MessageBusStub();
        $listener = new EntityUpdateListener($messageBus);

        $updatedEntity = new UpdatedEntity(
            ['property'],
            stdClass::class,
            ['id' => 'value']
        );

        $event = new EntityChangeEvent([$updatedEntity, ]);

        $listener($event);

        $expectedCalls = [
            [
                'message' => $event,
                'stamps' => [],
            ],
        ];

        self::assertEquals($expectedCalls, $messageBus->getCalls('dispatch'));
    }
}
