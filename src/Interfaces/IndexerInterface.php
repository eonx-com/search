<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use DateTime;
use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;

interface IndexerInterface
{
    /**
     * Remove any indices unused by a root alias that are/were applicable to search handlers
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface[] $searchHandlers
     * @param bool|null $dryRun Do not execute if true
     *
     * @return \LoyaltyCorp\Search\Indexer\IndexCleanResult
     */
    public function clean(array $searchHandlers, ?bool $dryRun = null): IndexCleanResult;

    /**
     * Create a new index for the search handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $searchHandler
     * @param \DateTime|null $now
     *
     * @return void
     */
    public function create(HandlerInterface $searchHandler, ?DateTime $now = null): void;

    /**
     * Atomically swap the root alias for a search handler, with the latest index created
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface[] $searchHandlers
     * @param bool|null $dryRun Do not execute if true
     *
     * @return \LoyaltyCorp\Search\Indexer\IndexSwapResult
     */
    public function indexSwap(array $searchHandlers, ?bool $dryRun = null): IndexSwapResult;

    /**
     * Populate a search handler with relevant documents
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $searchHandler
     * @param string $indexSuffix
     * @param int|null $batchSize
     *
     * @return void
     */
    public function populate(HandlerInterface $searchHandler, string $indexSuffix, ?int $batchSize = null): void;
}
