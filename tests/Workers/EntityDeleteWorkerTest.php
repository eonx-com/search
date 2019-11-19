<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Workers;

use LoyaltyCorp\Search\Workers\EntityDeleteWorker;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Workers\EntityDeleteWorker
 */
final class EntityDeleteWorkerTest extends TestCase
{
    /**
     * Test handle.
     *
     * @return void
     */
    public function testHandle(): void
    {
        $searchManager = new ManagerStub();
        $worker = new EntityDeleteWorker($searchManager);

        $expectedResult = ['index' => ['id1']];

        $worker->handle(['index' => ['id1']]);

        self::assertSame([$expectedResult], $searchManager->getDeletes());
    }

    /**
     * Test handle empty deletes.
     *
     * @return void
     */
    public function testHandleEmptyDeletes(): void
    {
        $searchManager = new ManagerStub();
        $worker = new EntityDeleteWorker($searchManager);

        $worker->handle([]);

        self::assertSame([], $searchManager->getDeletes());
    }
}
