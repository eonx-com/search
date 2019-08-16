<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use DateTime;
use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use LoyaltyCorp\Search\Interfaces\SearchInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

/**
 * @coversNothing
 */
class IndexerStub implements IndexerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\SearchInterface[]
     */
    private $cleanedHandlers = [];

    /**
     * @var \LoyaltyCorp\Search\Interfaces\SearchInterface[]
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
    public function create(SearchInterface $searchHandler, ?DateTime $now = null): void
    {
        $this->created[] = $searchHandler;
    }

    /**
     * Spy method to look at cleaned handlers
     *
     * @return \LoyaltyCorp\Search\Interfaces\SearchInterface[]
     */
    public function getCleanedSearchHandlers(): array
    {
        return $this->cleanedHandlers;
    }

    /**
     * Get search handlers that have been passed for creation
     *
     * @return \LoyaltyCorp\Search\Interfaces\SearchInterface[]
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

        foreach ($searchHandlers as $handler) {
            $rootIndex = $handler->getIndexName();

            $aliasesToMove[] = ['alias' => $rootIndex, 'index' => \sprintf('%s_123', $rootIndex)];
            $aliasesToDelete[] = \sprintf('%s_new', $rootIndex);
        }

        return new IndexSwapResult(... [$aliasesToMove, $aliasesToDelete]);
    }

    /**
     * {@inheritdoc}
     */
    public function populate(SearchInterface $searchHandler, string $indexSuffix, ?int $batchSize = null): void
    {
        $this->populatedHandlers[] = \compact('searchHandler', 'indexSuffix', 'batchSize');
    }
}
