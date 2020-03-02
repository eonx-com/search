<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface PopulatorInterface
{
    /**
     * Populates a handler's index with the handlers fill iterable.
     *
     * @phpstan-param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface<mixed> $handler
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param string $indexSuffix
     * @param int $batchSize
     *
     * @return void
     */
    public function populate(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        int $batchSize
    ): void;
}
