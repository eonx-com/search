<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\MessageHandlers;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers\EntityUpdateHandler;
use LoyaltyCorp\Search\Bridge\Symfony\Messages\EntityChangeMessage;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Workers\EntityUpdateWorkerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\MessageHandlers\EntityUpdateHandler
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Messages\EntityChangeMessage
 */
final class EntityUpdateHandlerTest extends UnitTestCase
{
    /**
     * Tests that the listener calls the worker with changes..
     *
     * @return void
     */
    public function testHandle(): void
    {
        $worker = new EntityUpdateWorkerStub();
        $listener = new EntityUpdateHandler($worker);

        $updatedEntity = new UpdatedEntity(
            ['property'],
            stdClass::class,
            ['id' => 'value']
        );

        $expectedCalls = [
            ['changes' => [$updatedEntity]],
        ];

        $listener(new EntityChangeMessage([
            $updatedEntity,
        ]));

        self::assertSame($expectedCalls, $worker->getCalls('handle'));
    }
}
