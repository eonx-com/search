<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;

class EntityManagerHelper implements EntityManagerHelperInterface
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * EntityManagerHelper constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function iterateAllIds(string $entityClass): iterable
    {
        $entityManager = $this->getDoctrineEntityManager();

        $primaryKeyField = $entityManager->getClassMetadata($entityClass)->getSingleIdentifierFieldName();

        $iterableResult = $entityManager->createQuery(
            \sprintf('SELECT e.%s FROM %s e', $primaryKeyField, $entityClass)
        )->iterate();

        foreach ($iterableResult as $iteration => $row) {
            yield $row[$iteration][$primaryKeyField];
        }
    }

    /**
     * Resolve the non-decorated doctrine entity manager
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     *
     * @throws \Exception
     */
    private function getDoctrineEntityManager(): EntityManagerInterface
    {
        $registry = $this->container->get('registry');

        if (\method_exists($registry, 'getManager') &&
            ($registry->getManager() instanceof EntityManagerInterface) === true) {
            return $registry->getManager();
        }

        throw new BindingResolutionException('Could not resolve Doctrine EntityManager');
    }
}
