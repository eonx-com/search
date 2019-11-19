<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Workers;

use LoyaltyCorp\Search\Interfaces\ManagerInterface;

final class EntityDeleteWorker
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ManagerInterface
     */
    private $searchManager;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ManagerInterface $searchManager
     */
    public function __construct(ManagerInterface $searchManager)
    {
        $this->searchManager = $searchManager;
    }

    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param string[][] $searchData
     *
     * @return void
     */
    public function handle(array $searchData): void
    {
        if (\count($searchData) === 0) {
            return;
        }

        $this->searchManager->handleDeletes($searchData);
    }
}
