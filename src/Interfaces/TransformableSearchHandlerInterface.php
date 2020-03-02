<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;

/**
 * @template T
 */
interface TransformableSearchHandlerInterface extends SearchHandlerInterface
{
    /**
     * Returns an iterable that contains ObjectForChange DTOs that will be sent into the job queue
     * for reindexing or filling the index.
     *
     * @phpstan-return array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>>
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
     */
    public function getFillIterable(): iterable;

    /**
     * Returns a unique string to identify the handler. This function is intentionally not static
     * as there can be more than one instance of a handler defined that needs to be uniquely
     * identified.
     *
     * @return string
     */
    public function getHandlerKey(): string;

    /**
     * Returns an array of ChangeSubscription objects that indicate what this handler
     * would like to be subscribed to, and how to transform that data when passed into
     * the getSearchId() and transform() methods.
     *
     * @phpstan-return array<int, \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription<mixed>>
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription[]
     */
    public function getSubscriptions(): iterable;

    /**
     * Prefills the ObjectForChange objects with references to the real object
     * that they reference. This is an optional step - if not performed the
     * transform method will need to query for the object it wants to update.
     *
     * @phpstan-param array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>> $changes
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[] $changes
     *
     * @return void
     */
    public function prefill(iterable $changes): void;

    /**
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @phpstan-param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T> $change
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange $change
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\DocumentAction|null
     */
    public function transform(ObjectForChange $change): ?DocumentAction;
}
