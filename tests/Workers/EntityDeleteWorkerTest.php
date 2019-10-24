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

        $worker->handle(['id1']);

        $result = $searchManager->getDeletes();
        self::assertSame([['id1']], $result);

        $worker->handle([]);

        $secondResult = $searchManager->getDeletes();
        self::assertSame($result, $secondResult);
    }
}
