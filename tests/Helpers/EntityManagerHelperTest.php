<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManagerInterface;
use EoneoPay\Externals\ORM\EntityManager;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use LoyaltyCorp\Search\Exceptions\DoctrineException;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use Tests\LoyaltyCorp\Search\DoctrineTestCase;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub as EoneoPayEntityManagerStub;

/**
 * @covers \LoyaltyCorp\Search\Helpers\EntityManagerHelper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class EntityManagerHelperTest extends DoctrineTestCase
{
    /**
     * Ensure the iteration method catches Doctrine exceptions and decorates them
     *
     * @return void
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    public function testExceptionCatching(): void
    {
        $this->expectException(DoctrineException::class);
        $this->expectExceptionMessage(
            'Unable to iterate all primary keys of entity \'Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub\''
        );
        $entityManager = $this->getDoctrineEntityManager();
        $entityManagerHelper = $this->getInstance($entityManager);
        /**
         * PhpStan & PhpStorm do not see generators as iterables
         *
         * @link https://github.com/phpstan/phpstan/issues/1246
         *
         * @var \Traversable $result
         */
        $result = $entityManagerHelper->iterateAllIds(EntityStub::class);

        // Execute generator
        \iterator_to_array($result);
    }

    /**
     * Ensure that finding by many Ids respects the existing entity manager
     *
     * @return void
     */
    public function testFindingByManyIdsWrapper(): void
    {
        $entityManager = $this->getEntityManager();
        $entity = (new EntityStub())->setIdentifier('pk3');
        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManagerHelper = $this->getInstance($entityManager, new EntityManager($entityManager));

        $result = $entityManagerHelper->findAllIds(EntityStub::class, ['pk3']);

        self::assertSame([$entity], $result);
    }

    /**
     * Test integration of Doctrine's Entity Manager with yielding only primary keys
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testIteratingIdentifiesOnlyHasFlushedEntities(): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist((new EntityStub())->setIdentifier('pk1'));
        $entityManager->persist((new EntityStub())->setIdentifier('pk2'));
        $entityManager->persist((new EntityStub())->setIdentifier('pk3'));
        $entityManager->flush();

        // Do not flush this entity to prove only flushed data is relevant
        $entityManager->persist((new EntityStub())->setIdentifier('pk4'));

        $entityManagerHelper = $this->getInstance($entityManager);

        /**
         * PhpStan & PhpStorm do not see generators as iterables
         *
         * @link https://github.com/phpstan/phpstan/issues/1246
         *
         * @var \Traversable $result
         */
        $result = $entityManagerHelper->iterateAllIds(EntityStub::class);
        $expected = ['pk1', 'pk2', 'pk3'];

        self::assertSame($expected, \iterator_to_array($result));
    }

    /**
     * Get instantiated entity manager helper
     *
     * @param \Doctrine\ORM\EntityManagerInterface $doctrineManager
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface|null $eoneoPayManager
     *
     * @return \LoyaltyCorp\Search\Helpers\EntityManagerHelper
     */
    private function getInstance(
        DoctrineEntityManagerInterface $doctrineManager,
        ?EoneoPayEntityManagerInterface $eoneoPayManager = null
    ): EntityManagerHelper {
        return new EntityManagerHelper(
            $doctrineManager,
            $eoneoPayManager ?? new EoneoPayEntityManagerStub()
        );
    }
}
