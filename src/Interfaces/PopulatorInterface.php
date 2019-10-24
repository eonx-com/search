<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface PopulatorInterface
{
    /**
     * Populates a handler's index with the handlers fill iterable.
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

    /**
     * Populates a handler's index with an array of objects.
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param string $indexSuffix
     * @param object[] $objects
     *
     * @return void
     */
    public function populateWith(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        iterable $objects
    ): void;
}
