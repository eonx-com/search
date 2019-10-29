<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use LoyaltyCorp\EasyEntityChange\Events\EntityChangeEvent;
use LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener;
use LoyaltyCorp\Search\Workers\EntityUpdateWorker;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Listeners\EntityUpdateListener
 */
final class EntityUpdateListenerTest extends TestCase
{
    /**
     * Test handle.
     *
     * @return void
     */
    public function testHandle(): void
    {
        $entityManager = new EntityManagerStub();
        $searchManager = new ManagerStub();
        $worker = new EntityUpdateWorker($entityManager, $searchManager);
        $listener = new EntityUpdateListener($worker);

        $entityManager->addFindByIds([
            $entity1 = new EntityStub(),
            $entity2 = new EntityStub(),
        ]);

        $listener->handle(new EntityChangeEvent([], [
            EntityStub::class => ['id1', 'id2'],
            \stdClass::class => [],
            self::class => ['id1'],
        ]));

        self::assertInstanceOf(ShouldQueue::class, $listener);

        $updates = $searchManager->getUpdateObjects();
        self::assertCount(1, $updates);
        self::assertSame([$entity1, $entity2], $updates[0]['objects']);
    }
}
