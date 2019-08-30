<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use Traversable;

interface PopulatorInterface
{
    /**
     * Returns a batch iterable result used for population of a search index.
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param int $batchSize
     *
     * @return \Traversable
     */
    public function getBatchedIterable(
        TransformableSearchHandlerInterface $handler,
        int $batchSize
    ): Traversable;
}
