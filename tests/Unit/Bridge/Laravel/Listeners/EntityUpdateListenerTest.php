<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Laravel\Listeners;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Workers\EntityUpdateWorkerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener
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
        $worker = new EntityUpdateWorkerStub();
        $listener = new EntityUpdateListener($worker);

        $updatedEntity = new UpdatedEntity(
            ['property'],
            stdClass::class,
            ['id' => 'value']
        );

        $expectedCalls = [
            ['changes' => [$updatedEntity]],
        ];

        $listener->handle(new EntityChangeEvent([
            $updatedEntity,
        ]));

        self::assertSame($expectedCalls, $worker->getCalls('handle'));
    }
}
