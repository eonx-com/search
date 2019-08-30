<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Traversable;

class Populator implements PopulatorInterface
{
    /**
     * Batches a search handler's iterable into batch sizes.
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param int $batchSize
     *
     * @return \Traversable
     */
    public function getBatchedIterable(TransformableSearchHandlerInterface $handler, int $batchSize): Traversable
    {
        $batch = [];

        foreach ($handler->getFillIterable() as $item) {
            $batch[] = $item;

            if (\count($batch) >= $batchSize) {
                yield $batch;

                $batch = [];
            }
        }

        if (\count($batch) > 0) {
            yield $batch;
        }
    }
}
