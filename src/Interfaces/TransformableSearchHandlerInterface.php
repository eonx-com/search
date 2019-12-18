<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

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
     * Returns the identifier used externally for the transformed object.
     *
     * @param object $object
     *
     * @return mixed|null
     */
    public function getSearchId(object $object);

    /**
     * Returns an array of ChangeSubscription objects that indicate what this handler
     * would like to be subscribed to, and how to transform that data when passed into
     * the getSearchId() and transform() methods.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription[]
     */
    public function getSubscriptions(): array;

    /**
     * Takes ObjectForUpdate DTOs and turns them into objects. The objects will then be
     * passed into transform or getSearchId().
     *
     * @param ObjectForUpdate[] $forUpdate
     *
     * @return object[]
     */
    public function resolveObjects(array $forUpdate): iterable;

    /**
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param object $object
     *
     * @return mixed[]|null
     */
    public function transform(object $object): ?array;
}
