<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Container\Container;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use Tests\LoyaltyCorp\Search\DoctrineTestCase;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine\RegistryStub;

/**
 * @covers \LoyaltyCorp\Search\Helpers\EntityManagerHelper
 */
class EntityManagerHelperTest extends DoctrineTestCase
{
    /**
     * Ensure resolution of Doctrine entity manager (undecorated) throws Exception if wrong type resolved from container
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testDoctrineEntityManagerResolutionThrowsException(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Could not resolve Doctrine EntityManager');

        $container = new Container();
        $container->singleton('registry', ClientStub::class);
        $entityManagerHelper = $this->getInstance($container);

        /**
         * PhpStan & PhpStorm do not see generators as iterables
         *
         * @link https://github.com/phpstan/phpstan/issues/1246
         *
         * @var \Traversable $result
         */
        $result = $entityManagerHelper->iterateAllIds('SomeFakeClass');

        \iterator_to_array($result);
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
        $container = new Container();
        $entityManager = $this->getEntityManager();

        $entityManager->persist((new EntityStub())->setIdentifier('pk1'));
        $entityManager->persist((new EntityStub())->setIdentifier('pk2'));
        $entityManager->persist((new EntityStub())->setIdentifier('pk3'));
        $entityManager->flush();

        // Do not flush this entity to prove only flushed data is relevant
        $entityManager->persist((new EntityStub())->setIdentifier('pk4'));

        /**
         * registry alias is used from Laravel-Doctrine to determine the actual Doctrine entity manager, as EoneoPay
         * overloads the normal Doctrine entity manager interface binding
         */
        $container->singleton('registry', static function () use ($entityManager) {
            return new RegistryStub($entityManager);
        });
        $container->singleton(
            EntityManagerInterface::class,
            static function () use ($entityManager): EntityManagerInterface {
                return $entityManager;
            }
        );

        $entityManagerHelper = $this->getInstance($container);

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
     * @param \Illuminate\Container\Container|null $container
     *
     * @return \LoyaltyCorp\Search\Helpers\EntityManagerHelper
     */
    private function getInstance(?Container $container = null): EntityManagerHelper
    {
        return new EntityManagerHelper($container ?? new Container());
    }
}
