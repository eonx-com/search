<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\Interfaces\ClientInterface;

/**
 * @coversNothing
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases
 */
class ClientStub implements ClientInterface
{
    /**
     * @var mixed[]
     */
    private $aliases;

    /**
     * @var int[]
     */
    private $count;

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
     * @var mixed[]
     */
    private $updatedIndices = [];

    /**
     * ClientStub constructor.
     *
     * @param bool|null $isAlias
     * @param bool|null $isIndex
     * @param mixed[]|null $indices
     * @param mixed[]|null $aliases
     * @param int[]|null $count
     */
    public function __construct(
        ?bool $isAlias = null,
        ?bool $isIndex = null,
        ?array $indices = null,
        ?array $aliases = null,
        ?array $count = null
    ) {
        $this->aliases = $aliases ?? [];
        $this->indices = $indices ?? [];
        $this->isAlias = $isAlias ?? false;
        $this->isIndex = $isIndex ?? false;
        $this->count = \array_reverse($count ?? []);
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
        $this->updatedIndices[] = \compact('index', 'documents');
    }

    /**
     * {@inheritdoc}
     */
    public function count(string $index): int
    {
        return \array_pop($this->count) ?? 0;
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
        $this->createdIndices[] = \compact('name', 'mappings', 'settings');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAlias(array $aliases): void
    {
        foreach ($aliases as $alias) {
            $this->deletedAliases[] = $alias;
        }
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
     * @return string[] Alias => Index
     */
    public function getSwappedAliases(): array
    {
        return $this->swappedAliases;
    }

    /**
     * Get list if indices updated.
     *
     * @return mixed[]
     */
    public function getUpdatedIndices(): array
    {
        return $this->updatedIndices;
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
    public function moveAlias(array $aliases): void
    {
        foreach ($aliases as $alias) {
            $this->swappedAliases[$alias['alias']] = $alias['index'];
        }
    }
}
