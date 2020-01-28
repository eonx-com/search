<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use LoyaltyCorp\Search\Bridge\Doctrine\Exceptions\InvalidRepositoryException;
use LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * This is a base DoctrineSearchHandler that performs most boilerplate work that
 * would normally be repeated by multiple search handlers.
 *
 * This handler only supports entities that have single value identifiers defined-
 * if the entity has multiple primary keys you must override the getFillIterable and
 * prefill methods.
 *
 * @template T
 *
 * @implements \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface<T>
 */
abstract class DoctrineSearchHandler implements TransformableSearchHandlerInterface
{
    /**
     * Stores the primary entity class for this handler.
     *
     * @phpstan-var class-string<T>
     *
     * @var string
     */
    private $entityClass;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * Constructor.
     *
     * @phpstan-param class-string<T> $entityClass
     *
     * @param string $entityClass
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(string $entityClass, EntityManagerInterface $entityManager)
    {
        $this->entityClass = $entityClass;
        $this->entityManager = $entityManager;
    }

    /**
     * Defines a basic fill iterable for entities. Override to provide custom functionality
     * like ordering of the iterable or custom properties to exclude specific objects.
     *
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Bridge\Doctrine\Exceptions\InvalidRepositoryException
     */
    public function getFillIterable(): iterable
    {
        $repository = $this->getRepository();

        yield from $repository->getFillIterable();
    }

    /**
     * By default, we define a change subscription only for the entity.
     *
     * Override this to provide additional subscriptions or to customise
     * the properties that the handler will react to.
     *
     * {@inheritdoc}
     */
    public function getSubscriptions(): iterable
    {
        yield new ChangeSubscription($this->entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function prefill(iterable $changes): void
    {
        $repository = $this->getRepository();
        $repository->prefillSearch($changes);
    }

    /**
     * Retrieves a repository, or throw if it isnt a FillableRepositoryInterface.
     *
     * @phpstan-return \LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface<T>
     *
     * @return \LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface
     */
    private function getRepository(): FillableRepositoryInterface
    {
        $repository = $this->entityManager->getRepository($this->entityClass);

        if ($repository instanceof FillableRepositoryInterface === false) {
            throw new InvalidRepositoryException(
                'A repository used for DoctrineSearchHandler must implement FillableRepositoryInterface.'
            );
        }

        return $repository;
    }
}
