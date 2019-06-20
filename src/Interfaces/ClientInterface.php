<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface ClientInterface
{
    /**
     * Does a bulk delete action for all ids provided. Expects
     * the format to be :
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
     * @param string $index
     * @param string[][] $documents
     *
     * @return void
     */
    public function bulkUpdate(string $index, array $documents): void;

    /**
     * Create a new alias for specified index
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function createAlias(string $indexName, string $aliasName): void;

    /**
     * Create a new index
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
     * Delete an existing alias
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function deleteAlias(string $indexName, string $aliasName): void;

    /**
     * Delete an existing index
     *
     * @param string $name
     *
     * @return void
     */
    public function deleteIndex(string $name): void;

    /**
     * Determine if alias exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isAlias(string $name): bool;

    /**
     * Determine if index exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isIndex(string $name): bool;

    /**
     * List all existing indexes
     *
     * @return mixed[]
     */
    public function listIndices(): array;
}
