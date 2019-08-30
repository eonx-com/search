<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use Traversable;

interface TransformerInterface
{
    /**
     * Returns an array of transformed documents to be indexed into the search indexes.
     *
     * @param \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface $handler
     * @param mixed[] $objects
     *
     * @return mixed[][]|\Traversable
     */
    public function bulkTransform(TransformableSearchHandlerInterface $handler, iterable $objects): Traversable;
}
