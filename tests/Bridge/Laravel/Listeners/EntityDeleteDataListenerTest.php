<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use LoyaltyCorp\EasyEntityChange\Events\EntityDeleteDataEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteDataListener;
use LoyaltyCorp\Search\Workers\EntityDeleteDataWorker;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteDataListener
 */
final class EntityDeleteDataListenerTest extends TestCase
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
        $listener = new EntityDeleteDataListener($worker);

        $result = $listener->handle(new EntityDeleteDataEvent([]));

        static::assertSame(['search' => []], $result);

        $searchManager->addSearchMeta(['index' => 'id']);
        $searchManager->addSearchMeta(['purple' => 'id', 'green' => 'id2']);
        $searchManager->addSearchMeta(['purple' => 'id2']);

        $result = $listener->handle(new EntityDeleteDataEvent([
            new \stdClass(),
            new \stdClass(),
            new \stdClass()
        ]));

        static::assertSame([
            'search' => [
                'index' => ['id'],
                'purple' => ['id', 'id2'],
                'green' => ['id2']
            ]
        ], $result);
    }
}
