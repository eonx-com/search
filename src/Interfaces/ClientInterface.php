<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use LoyaltyCorp\Search\DataTransferObjects\ClusterHealth;

interface ClientInterface
{
    /**
     * Does a bulk delete action for all ids provided. Expects
     * the format to be :.
     *
     * [
     *   'index_name' => [
     *     'ID1',
     *     'ID2'
     *   ],
     *   'index_name_2' => [
     *     'ID3',
     *   ]
     * ]
     *
     * @param string[][] $searchIds
     *
     * @return void
     */
    public function bulkDelete(array $searchIds): void;

    /**
     * Upserts all documents provided into the index.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\IndexAction[] $updates
     *
     * @return void
     */
    public function bulkUpdate(array $updates): void;

    /**
     * Count the number of documents within an index.
     *
     * @param string $index
     *
     * @return int
     */
    public function count(string $index): int;

    /**
     * Create a new alias for specified index.
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function createAlias(string $indexName, string $aliasName): void;

    /**
     * Create a new index.
     *
     * @param string $name
     * @param mixed[]|null $mappings
     * @param mixed[]|null $settings
     *
     * @return void
     */
    public function createIndex(
        string $name,
        ?array $mappings = null,
        ?array $settings = null
    ): void;

    /**
     * Delete an existing alias across all indices.
     *
     * @param string[] $aliases Array of alias names to be deleted
     *
     * @return void
     */
    public function deleteAlias(array $aliases): void;

    /**
     * Delete an existing index.
     *
     * @param string $name
     *
     * @return void
     */
    public function deleteIndex(string $name): void;

    /**
     * List all existing aliases.
     *
     * @param string|null $name
     *
     * @return string[][]
     */
    public function getAliases(?string $name = null): array;

    /**
     * Gets the health of the cluster.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\ClusterHealth
     */
    public function getHealth(): ClusterHealth;

    /**
     * List all existing indexes.
     *
     * @param string|null $name
     *
     * @return mixed[]
     */
    public function getIndices(?string $name = null): array;

    /**
     * Determine if alias exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isAlias(string $name): bool;

    /**
     * Determine if index exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isIndex(string $name): bool;

    /**
     * Atomically remove/add alias.
     *
     * @param string[][] $aliases Array containing alias and index to be swapped
     *
     * @return void
     */
    public function moveAlias(array $aliases): void;
}
