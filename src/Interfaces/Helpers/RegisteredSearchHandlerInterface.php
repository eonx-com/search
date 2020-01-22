<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

interface RegisteredSearchHandlerInterface
{
    /**
     * Get all search handlers that have been registered in the container.
     *
     * @return \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]
     */
    public function getAll(): array;

    /**
     * Groups all search handler's subscriptions into an array grouped by the subscribing class.
     *
     * @phpstan-return array<string, array<\LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription>>
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription[]
     */
    public function getSubscriptionsGroupedByClass(): array;

    /**
     * Retrieves a handler by its key.
     *
     * @param string $key
     *
     * @return \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface
     */
    public function getTransformableHandlerByKey(string $key): TransformableSearchHandlerInterface;

    /**
     * Get search handlers that support object transformations.
     *
     * @return \LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface[]
     */
    public function getTransformableHandlers(): array;
}
