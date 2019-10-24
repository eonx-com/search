<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Workers;

use LoyaltyCorp\Search\Workers\EntityDeleteDataWorker;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Workers\EntityDeleteDataWorker
 */
final class EntityDeleteDataWorkerTest extends TestCase
{
    /**
     * Test handle.
     *
     * @return void
     */
    public function testHandle(): void
    {
        $searchManager = new ManagerStub();

        $worker = new EntityDeleteDataWorker($searchManager);

        $result = $worker->handle([]);
        self::assertSame(['search' => []], $result);

        $searchManager->addSearchMeta(['index' => 'id']);
        $searchManager->addSearchMeta(['purple' => 'id', 'green' => 'id2']);
        $searchManager->addSearchMeta(['purple' => 'id2']);

        $result = $worker->handle([
            new stdClass(),
            new stdClass(),
            new stdClass(),
        ]);

        self::assertSame([
            'search' => [
                'index' => ['id'],
                'purple' => ['id', 'id2'],
                'green' => ['id2'],
            ],
        ], $result);
    }
}
