<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;

final class SearchIndexFillCommand extends SearchIndexCommand
{
    /**
     * @var mixed $batchSize
     */
    private $batchSize;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $manager;

    /**
     * SearchIndexFill constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $manager
     *
     * @throws \Exception
     */
    public function __construct(
        ContainerInterface $container,
        ManagerInterface $manager
    ) {
        $this->description = '';
        $this->signature = 'search:index:fill {--batchsize=1000}';

        $this->batchSize = $this->option('batchsize');
        $this->entityManager = $this->getDoctrineEntityManager();
        $this->manager = $manager;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function handleSearchHandler(HandlerInterface $handler): void
    {
        $documents = [];
        $iteration = 0;
        foreach ($this->iterateAllIds($this->entityManager, $handler->getHandledClass()) as $identifier) {
            $documents[] = $identifier;

            // Create documents in batches to avoid overloading memory & request size
            if (($iteration % $this->batchSize) === 0) {
                $this->manager->handleUpdates($handler->getHandledClass(), $documents);
                $documents = [];
            }

            $iteration++;
        }

        // Handle creation of remaining documents that were not batched because the loop finished
        if (\count($documents) > 0) {
            $this->manager->handleUpdates(
                $handler->getHandledClass(),
                $documents
            );
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

        if (\is_object($registry) && \method_exists($registry, 'getManager')) {
            return $registry->getManager();
        }

        // Missing LaravelDoctrine?
        throw new \Exception('Could not resolve Doctrine EntityManager');
    }

    /**
     * Yield all primary keys from the given entity
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param string $entityClass
     *
     * @return iterable|string[]
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function iterateAllIds(EntityManagerInterface $entityManager, string $entityClass): iterable
    {
        $primaryKeyField = $entityManager->getClassMetadata($entityClass)->getSingleIdentifierFieldName();
        // @todo Validate for SQL Safety?
        $iterableResult = $entityManager->createQuery(
            \sprintf('SELECT e.%s FROM %s e', $primaryKeyField, $entityClass)
        )->iterate();

        foreach ($iterableResult as $row) {
            yield $row[0];

            $entityManager->detach($row[0]);
        }
    }
}
