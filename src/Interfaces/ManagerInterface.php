<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

/**
 * This manager is used to aid in the automatic dispatching of events
 * by an application to handle search indexing in an asynchronous way.
 *
 * This manager requires TransformableSearchHandler implementations that
 * will automatically handle transformations of objects into the
 * Elasticsearch representations.
 */
interface ManagerInterface
{
    /**
     * Accepts a multi dimensional array of search ids keyed by the index
     * they should be removed from.
     *
     * @param string[][] $ids
     *
     * @return void
     */
    public function handleDeletes(array $ids): void;

    /**
     * Takes an array of entity ids that are or have been updated in
     * a Doctrine lifecycle and updates any related search entries
     * based on those entities.
     *
     * @param string $class
     * @param string $indexSuffix
     * @param object[] $objects
     *
     * @return void
     */
    public function handleUpdates(string $class, string $indexSuffix, array $objects): void;
}
