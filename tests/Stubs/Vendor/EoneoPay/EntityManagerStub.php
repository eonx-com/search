<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay;

use EoneoPay\Externals\ORM\Interfaces\EntityInterface;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use EoneoPay\Externals\ORM\Interfaces\Query\FilterCollectionInterface;
use EoneoPay\Externals\ORM\Interfaces\RepositoryInterface;

class EntityManagerStub implements EntityManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByIds(string $class, array $ids): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): FilterCollectionInterface
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(string $class): RepositoryInterface
    {
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function merge(EntityInterface $entity): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function persist(EntityInterface $entity): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove(EntityInterface $entity): void
    {
    }
}
