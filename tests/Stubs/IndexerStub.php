<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use DateTime;
use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

/**
 * @coversNothing
 */
class IndexerStub implements IndexerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    private $cleanedHandlers = [];

    /**
     * @var \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    private $created = [];

    /**
     * @var int
     */
    private $indicesSwapped = 0;

    /**
     * @var mixed[]
     */
    private $populatedHandlers = [];

    /**
     * {@inheritdoc}
     */
    public function clean(array $searchHandlers, ?bool $dryRun = null): IndexCleanResult
    {
        $this->cleanedHandlers = $searchHandlers;

        return new IndexCleanResult([]);
    }

    /**
     * {@inheritdoc}
     */
    public function create(SearchHandlerInterface $searchHandler, ?DateTime $now = null): void
    {
        $this->created[] = $searchHandler;
    }

    /**
     * Spy method to look at cleaned handlers
     *
     * @return \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    public function getCleanedSearchHandlers(): array
    {
        return $this->cleanedHandlers;
    }

    /**
     * Get search handlers that have been passed for creation
     *
     * @return \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    public function getCreatedHandlers(): array
    {
        return $this->created;
    }

    /**
     * Spy for the number of time indexSwap was called
     *
     * @return int
     */
    public function getIndicesSwapped(): int
    {
        return $this->indicesSwapped;
    }

    /**
     * Determine if indexed has called populate
     *
     * @return mixed[]
     */
    public function getPopulatedHandlers(): array
    {
        return $this->populatedHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function indexSwap(array $searchHandlers, ?bool $dryRun = null): IndexSwapResult
    {
        $this->indicesSwapped++;

        $aliasesToMove = [];
        $aliasesToDelete = [];
        $aliasesToSkip = [];

        foreach ($searchHandlers as $handler) {
            $rootIndex = $handler->getIndexName();

            $aliasesToMove[] = ['alias' => $rootIndex, 'index' => \sprintf('%s_123', $rootIndex)];
            $aliasesToDelete[] = \sprintf('%s_new', $rootIndex);
            $aliasesToSkip[] = $rootIndex;
        }

        return new IndexSwapResult(... [$aliasesToMove, $aliasesToDelete, $aliasesToSkip]);
    }

    /**
     * {@inheritdoc}
     */
    public function populate(
        EntitySearchHandlerInterface $searchHandler,
        string $indexSuffix,
        ?int $batchSize = null
    ): void {
        $this->populatedHandlers[] = \compact('searchHandler', 'indexSuffix', 'batchSize');
    }
}
