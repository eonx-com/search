<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface IndexerInterface
{
    /**
     * Create a new index for the search handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $searchHandler
     *
     * @return void
     */
    public function create(HandlerInterface $searchHandler): void;

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
