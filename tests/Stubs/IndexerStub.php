<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

class IndexerStub implements IndexerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\HandlerInterface[]
     */
    private $cleanedHandlers = [];

    /**
     * @var int
     */
    private $createdCount = 0;

    /**
     * @var int
     */
    private $populatedCount = 0;

    /**
     * {@inheritdoc}
     */
    public function clean(array $searchHandlers): void
    {
        $this->cleanedHandlers = $searchHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function create(HandlerInterface $searchHandler): void
    {
        $this->createdCount++;
    }

    /**
     * Spy method to look at cleaned handlers
     *
     * @return \LoyaltyCorp\Search\Interfaces\HandlerInterface[]
     */
    public function getCleanedSearchHandlers(): array
    {
        return $this->cleanedHandlers;
    }

    /**
     * Get number of created indices
     *
     * @return int
     */
    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    /**
     * Determine if indexed has caled populate
     *
     * @return int
     */
    public function getPopulatedCount(): int
    {
        return $this->populatedCount;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(HandlerInterface $searchHandler, ?int $batchSize = null): void
    {
        $this->populatedCount++;
    }
}
