<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Workers;

use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;

final class EntityUpdateWorker
{
    /**
     * @var \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $searchManager;

    /**
     * Constructor.
     *
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $entityManager
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $searchManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ManagerInterface $searchManager
    ) {
        $this->entityManager = $entityManager;
        $this->searchManager = $searchManager;
    }

    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param mixed[] $updates
     *
     * @return void
     */
    public function handle(array $updates): void
    {
        foreach ($updates as $class => $ids) {
            if (\count($ids) === 0) {
                continue;
            }

            $entities = $this->entityManager->findByIds($class, \array_values($ids));

            if (\count($entities) === 0) {
                continue;
            }

            $this->searchManager->handleUpdates($class, '', $entities);
        }
    }
}
