<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface IndexerInterface
{
    /**
     * Remove any indices unused by a root alias that are/were applicable to search handlers
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface[] $searchHandlers
     *
     * @return void
     */
    public function clean(array $searchHandlers): void;

    /**
     * Create a new index for the search handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $searchHandler
     *
     * @return void
     */
    public function create(HandlerInterface $searchHandler): void;

    /**
     * Atomically swap the root alias for a search handler, with the latest index created
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $searchHandler
     *
     * @return void
     */
    public function indexSwap(HandlerInterface $searchHandler): void;

    /**
     * Populate a search handler with relevant documents
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $searchHandler
     * @param int|null $batchSize
     *
     * @return void
     */
    public function populate(HandlerInterface $searchHandler, ?int $batchSize = null): void;
}
