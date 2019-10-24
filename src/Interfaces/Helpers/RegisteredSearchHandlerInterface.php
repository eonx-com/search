<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

interface RegisteredSearchHandlerInterface
{
    /**
     * Get all search handlers that have been registered in the container.
     *
     * @return \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    public function getAll(): array;

    /**
     * Get search handlers that support object transformations.
     *
     * @return \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface[]
     */
    public function getTransformableHandlers(): array;
}
