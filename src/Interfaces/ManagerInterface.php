<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface ManagerInterface
{
    /**
     * Returns an array of any indexes that this object is primary.
     * The key is the index name and the value is the identifier used
     * for that index.
     *
     * [
     *   'index-name' => 'SEARCH ID',
     *   'index-name-2' => 'SEARCH ID',
     * ]
     *
     * @param object $object
     *
     * @return string[]
     */
    public function getSearchMeta(object $object): array;

    /**
     * Accepts a multi dimensional array of search ids keyed by the index
     * they should be removed from.
     *
     * @param string[] $ids
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

    /**
     * Indicates if the class is used as part of the search
     * index update process.
     *
     * @param string $class
     *
     * @return bool
     */
    public function isSearchable(string $class): bool;
}
