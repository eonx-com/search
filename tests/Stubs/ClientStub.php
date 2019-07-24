<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\ClientInterface;

class ClientStub implements ClientInterface
{
    /**
     * @var mixed[]
     */
    private $createdAliases = [];

    /**
     * @var mixed[]
     */
    private $createdIndices = [];

    /**
     * @var mixed[]
     */
    private $deletedAliases = [];

    /**
     * @var mixed[]
     */
    private $deletedIndices = [];

    /**
     * @var bool
     */
    private $isAlias;

    /**
     * @var bool
     */
    private $isIndex;

    /**
     * ClientStub constructor.
     *
     * @param bool|null $isAlias
     * @param bool|null $isIndex
     */
    public function __construct(?bool $isAlias = null, ?bool $isIndex = null)
    {
        $this->isAlias = $isAlias ?? false;
        $this->isIndex = $isIndex ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function bulkDelete(array $searchIds): void
    {
    }

    /**
     * Upserts all documents provided into the index.
     *
     * @param string $index
     * @param string[][] $documents
     *
     * @return void
     */
    public function bulkUpdate(string $index, array $documents): void
    {
    }

    /**
     * Create a new alias for specified index
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function createAlias(string $indexName, string $aliasName): void
    {
        $this->createdAliases[] = $aliasName;
    }

    /**
     * Create a new index
     *
     * @param string $name
     * @param mixed[]|null $mappings
     * @param mixed[]|null $settings
     *
     * @return void
     */
    public function createIndex(string $name, ?array $mappings = null, ?array $settings = null): void
    {
        $this->createdIndices[] = $name;
    }

    /**
     * Delete an existing alias
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function deleteAlias(string $indexName, string $aliasName): void
    {
        $this->deletedAliases[] = $aliasName;
    }

    /**
     * Delete an existing index
     *
     * @param string $name
     *
     * @return void
     */
    public function deleteIndex(string $name): void
    {
        $this->deletedIndices[] = $name;
    }

    /**
     * List all existing aliases
     *
     * @param string|null $name
     *
     * @return mixed[]
     */
    public function getAliases(?string $name = null): array
    {
        return [];
    }

    /**
     * Spy on created aliases
     *
     * @return mixed[]
     */
    public function getCreatedAliases(): array
    {
        return $this->createdAliases;
    }

    /**
     * Spy on created indices
     *
     * @return mixed[]
     */
    public function getCreatedIndices(): array
    {
        return $this->createdIndices;
    }

    /**
     * Spy on deleted aliases
     *
     * @return string[]
     */
    public function getDeletedAliases(): array
    {
        return $this->deletedAliases;
    }

    /**
     * Spy on deleted indices
     *
     * @return string[]
     */
    public function getDeletedIndices(): array
    {
        return $this->deletedIndices;
    }

    /**
     * List all existing indexes
     *
     * @param string|null $name
     *
     * @return mixed[]
     */
    public function getIndices(?string $name = null): array
    {
        return [];
    }

    /**
     * Determine if alias exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isAlias(string $name): bool
    {
        return $this->isAlias;
    }

    /**
     * Determine if index exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isIndex(string $name): bool
    {
        return $this->isIndex;
    }
}
