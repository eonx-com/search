<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use DateTime;
use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;

interface IndexerInterface
{
    /**
     * Remove any indices unused by a root alias that are/were applicable to search handlers.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[] $searchHandlers
     * @param bool|null $dryRun Do not execute if true
     *
     * @return \LoyaltyCorp\Search\Indexer\IndexCleanResult
     */
    public function clean(array $searchHandlers, ?bool $dryRun = null): IndexCleanResult;

    /**
     * Create a new index for the search handler.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler
     * @param \DateTime|null $now
     *
     * @return void
     */
    public function create(SearchHandlerInterface $searchHandler, ?DateTime $now = null): void;

    /**
     * Atomically swap the root alias for a search handler, with the latest index created.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[] $searchHandlers
     * @param bool|null $dryRun Do not execute if true
     *
     * @return \LoyaltyCorp\Search\Indexer\IndexSwapResult
     */
    public function indexSwap(array $searchHandlers, ?bool $dryRun = null): IndexSwapResult;
}
