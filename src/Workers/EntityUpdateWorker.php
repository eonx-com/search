<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Workers;

use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;

final class EntityUpdateWorker implements EntityUpdateWorkerInterface
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
     * {@inheritdoc}
     */
    public function handle(array $changes): void
    {
        foreach ($changes as $change) {
            $entity = $this->entityManager->getRepository($change->getClass())
                ->findOneBy($change->getIds());

            $this->searchManager->handleUpdates($change->getClass(), '', [$entity]);
        }
    }
}
