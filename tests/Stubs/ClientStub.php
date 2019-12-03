<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use LoyaltyCorp\Search\DataTransferObjects\ClusterHealth;
use LoyaltyCorp\Search\Interfaces\ClientInterface;

/**
 * @coversNothing
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases
 */
final class ClientStub implements ClientInterface
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
     * @var \LoyaltyCorp\Search\DataTransferObjects\ClusterHealth|null
     */
    private $health;

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
     * @param \LoyaltyCorp\Search\DataTransferObjects\ClusterHealth|null $health
     */
    public function __construct(
        ?bool $isAlias = null,
        ?bool $isIndex = null,
        ?array $indices = null,
        ?array $aliases = null,
        ?array $count = null,
        ?ClusterHealth $health = null
    ) {
        $this->aliases = $aliases ?? [];
        $this->indices = $indices ?? [];
        $this->isAlias = $isAlias ?? false;
        $this->isIndex = $isIndex ?? false;
        $this->count = \array_reverse($count ?? []);
        $this->health = $health;
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
    public function bulkUpdate(array $updates): void
    {
        $this->updatedIndices[] = $updates;
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
     * Spy on created aliases.
     *
     * @return mixed[]
     */
    public function getCreatedAliases(): array
    {
        return $this->createdAliases;
    }

    /**
     * Spy on created indices.
     *
     * @return mixed[]
     */
    public function getCreatedIndices(): array
    {
        return $this->createdIndices;
    }

    /**
     * Spy on deleted aliases.
     *
     * @return string[]
     */
    public function getDeletedAliases(): array
    {
        return $this->deletedAliases;
    }

    /**
     * Spy on deleted indices.
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
    public function getHealth(): ClusterHealth
    {
        return $this->health ?? new ClusterHealth([
                'cluster_name' => 'testcluster',
                'status' => 'yellow',
                'timed_out' => false,
                'number_of_nodes' => 1,
                'number_of_data_nodes' => 2,
                'active_primary_shards' => 3,
                'active_shards' => 4,
                'relocating_shards' => 5,
                'initializing_shards' => 6,
                'unassigned_shards' => 7,
                'delayed_unassigned_shards' => 8,
                'number_of_pending_tasks' => 9,
                'number_of_in_flight_fetch' => 10,
                'task_max_waiting_in_queue_millis' => 11,
                'active_shards_percent_as_number' => 50.0,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndices(?string $name = null): array
    {
        return $this->indices;
    }

    /**
     * Spy on alias that had its index swapped.
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
