<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;

interface TransformableSearchHandlerInterface extends SearchHandlerInterface
{
    /**
     * Returns an iterable that will be used to fill the index when doing a full
     * index fill.
     *
     * @return iterable|mixed[]
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
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange $object
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\DocumentAction|null
     */
    public function transform(ObjectForChange $object): ?DocumentAction;
}
