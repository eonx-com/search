<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

abstract class BaseDoctrineSearchHandler implements TransformableSearchHandlerInterface
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface
     */
    private $entityManager;

    /**
     * BaseDoctrineSearchHandler constructor.
     *
     * @param string $entityClass
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $entityManager
     */
    public function __construct(string $entityClass, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveObjects(array $changes): array
    {
        $ids = [];
        foreach ($changes as $objectForChange) {
            $ids[] = $objectForChange->getIds();
        }

        /** @var \EoneoPay\Externals\ORM\Interfaces\RepositoryInterface $respository */
        $respository = $this->entityManager->getRepository($this->entityClass);

        // todo: how about composite keys?
        return $respository->findBy($ids);
    }
}
