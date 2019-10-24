<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteListener;
use LoyaltyCorp\Search\Workers\EntityDeleteWorker;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityDeleteListener
 */
final class EntityDeleteListenerTest extends TestCase
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
        $listener = new EntityDeleteListener($worker);

        $listener->handle(new EntityChangeEvent(['search' => ['id1']], []));

        $result = $searchManager->getDeletes();
        self::assertSame([['id1']], $result);

        $listener->handle(new EntityChangeEvent([], []));

        $secondResult = $searchManager->getDeletes();
        self::assertSame($result, $secondResult);
    }
}
