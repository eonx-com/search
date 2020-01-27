<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;

interface TransformableSearchHandlerInterface extends SearchHandlerInterface
{
    /**
     * Returns an iterable that contains ObjectForChange DTOs that will be sent into the job queue
     * for reindexing or filling the index.
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
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription[]
     */
    public function getSubscriptions(): array;

    /**
     * Retrieves all objects at once.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[] $changes
     *
     * @return object[]
     */
    public function retrieveObjects(array $changes): array;

    /**
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param object $object
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\DocumentAction|null
     */
    public function transform(object $object): ?DocumentAction;
}
