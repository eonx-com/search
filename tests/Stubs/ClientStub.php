<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\ClientInterface;

/**
 * @coversNothing
 */
class ClientStub implements ClientInterface
{
    /**
     * @var mixed[]
     */
    private $aliases;

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
     * @var mixed[]
     */
    private $indices;

    /**
     * @var bool
     */
    private $isAlias;

    /**
     * @var bool
     */
    private $isIndex;

    /**
     * @var string[]
     */
    private $swappedAliases = [];

    /**
     * ClientStub constructor.
     *
     * @param bool|null $isAlias
     * @param bool|null $isIndex
     * @param mixed[]|null $indices
     * @param mixed[]|null $aliases
     */
    public function __construct(
        ?bool $isAlias = null,
        ?bool $isIndex = null,
        ?array $indices = null,
        ?array $aliases = null
    ) {
        $this->aliases = $aliases ?? [];
        $this->indices = $indices ?? [];
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
     * {@inheritdoc}
     */
    public function bulkUpdate(string $index, array $documents): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createAlias(string $indexName, string $aliasName): void
    {
        $this->createdAliases[] = $aliasName;
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(string $name, ?array $mappings = null, ?array $settings = null): void
    {
        $this->createdIndices[] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAlias(string $indexName, string $alias): void
    {
        $this->deletedAliases[] = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(string $name): void
    {
        $this->deletedIndices[] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(?string $name = null): array
    {
        return $this->aliases;
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
     * {@inheritdoc}
     */
    public function getIndices(?string $name = null): array
    {
        return $this->indices;
    }

    /**
     * Spy on alias that had its index swapped
     *
     * @return string[]
     */
    public function getSwappedAliases(): array
    {
        return $this->swappedAliases;
    }

    /**
     * {@inheritdoc}
     */
    public function isAlias(string $name): bool
    {
        return $this->isAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndex(string $name): bool
    {
        return $this->isIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function moveAlias(string $alias, string $newIndex): void
    {
        $this->swappedAliases[] = $alias;
    }
}
