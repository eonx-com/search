<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Workers;

use LoyaltyCorp\Search\Workers\EntityUpdateWorker;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\ManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Workers\EntityUpdateWorker
 */
final class EntityUpdateWorkerTest extends TestCase
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

        $entityManager->addFindByIds([
            $entity1 = new EntityStub(),
            $entity2 = new EntityStub(),
        ]);

        $worker->handle([
            EntityStub::class => ['id1', 'id2'],
            \stdClass::class => [],
            self::class => ['id1'],
        ]);

        $updates = $searchManager->getUpdateObjects();
        self::assertCount(1, $updates);
        self::assertSame([$entity1, $entity2], $updates[0]['objects']);
    }
}
