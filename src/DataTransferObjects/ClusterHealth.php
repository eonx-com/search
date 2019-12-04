<?php /** @noinspection PhpPropertyNamingConventionInspection Long property names to keep inline with ES API. */
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

/**
 * @SuppressWarnings(PHPMD.LongVariable) Long variable names to keep inline with Elasticsearch API.
 */
final class ClusterHealth
{
    /**
     * The number of active primary shards.
     *
     * @var int
     */
    private $activePrimaryShards;

    /**
     * The number of active shards.
     *
     * @var int
     */
    private $activeShards;

    /**
     * The percentage of active shards.
     *
     * @var int
     */
    private $activeShardsPercent;

    /**
     * The number of delayed unassigned shards.
     *
     * @var int
     */
    private $delayedUnassignedShards;

    /**
     * The number of initializing shards.
     *
     * @var int
     */
    private $initializingShards;

    /**
     * The name of the cluster.
     *
     * @var string
     */
    private $name;

    /**
     * The number of data nodes.
     *
     * @var int
     */
    private $numberOfDataNodes;

    /**
     * The number of in-flight fetches.
     *
     * @var int
     */
    private $numberOfInFlightFetch;

    /**
     * The number of nodes.
     *
     * @var int
     */
    private $numberOfNodes;

    /**
     * The number of pending tasks.
     *
     * @var int
     */
    private $numberOfPendingTasks;

    /**
     * The number of relocating shards.
     *
     * @var int
     */
    private $relocatingShards;

    /**
     * The cluster status.
     *
     * @var string
     */
    private $status;

    /**
     * The maximum waiting time (in milliseconds) for a queued task.
     *
     * @var int
     */
    private $taskMaxWaitingInQueueMillis;

    /**
     * Whether the health report timed out.
     *
     * @var bool
     */
    private $timedOut;

    /**
     * The number of unassigned shards.
     *
     * @var int
     */
    private $unassignedShards;

    /**
     * Constructs a new instance of the DTO.
     *
     * @param mixed[] $data The response data from the Elasticsearch API.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/6.8/cluster-health.html
     */
    public function __construct(array $data)
    {
        $this->activePrimaryShards = (int)$data['active_primary_shards'];
        $this->activeShards = (int)$data['active_shards'];
        $this->activeShardsPercent = (int)$data['active_shards_percent_as_number'];
        $this->delayedUnassignedShards = (int)$data['delayed_unassigned_shards'];
        $this->initializingShards = (int)$data['initializing_shards'];
        $this->name = (string)$data['cluster_name'];
        $this->numberOfDataNodes = (int)$data['number_of_data_nodes'];
        $this->numberOfInFlightFetch = (int)$data['number_of_in_flight_fetch'];
        $this->numberOfNodes = (int)$data['number_of_nodes'];
        $this->numberOfPendingTasks = (int)$data['number_of_pending_tasks'];
        $this->relocatingShards = (int)$data['relocating_shards'];
        $this->status = (string)$data['status'];
        $this->taskMaxWaitingInQueueMillis = (int)$data['task_max_waiting_in_queue_millis'];
        $this->timedOut = (bool)$data['timed_out'];
        $this->unassignedShards = (int)$data['unassigned_shards'];
    }

    /**
     * Gets the number of active shards as a percentage.
     *
     * @return int
     */
    public function getActiveShardsPercent(): int
    {
        return $this->activeShardsPercent;
    }

    /**
     * Gets the cluster name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the number of active primary shards.
     *
     * @return int
     */
    public function getNumberOfActivePrimaryShards(): int
    {
        return $this->activePrimaryShards;
    }

    /**
     * Gets the number of active shards.
     *
     * @return int
     */
    public function getNumberOfActiveShards(): int
    {
        return $this->activeShards;
    }

    /**
     * Gets the number of data nodes.
     *
     * @return int
     */
    public function getNumberOfDataNodes(): int
    {
        return $this->numberOfDataNodes;
    }

    /**
     * Gets the number of delayed unassigned shards.
     *
     * @return int
     */
    public function getNumberOfDelayedUnassignedShards(): int
    {
        return $this->delayedUnassignedShards;
    }

    /**
     * Gets the number of in-flight fetches.
     *
     * @return int
     */
    public function getNumberOfInFlightFetch(): int
    {
        return $this->numberOfInFlightFetch;
    }

    /**
     * Gets the number of initializing shards.
     *
     * @return int
     */
    public function getNumberOfInitializingShards(): int
    {
        return $this->initializingShards;
    }

    /**
     * Gets the number of nodes in the cluster.
     *
     * @return int
     */
    public function getNumberOfNodes(): int
    {
        return $this->numberOfNodes;
    }

    /**
     * Gets the number of pending tasks.
     *
     * @return int
     */
    public function getNumberOfPendingTasks(): int
    {
        return $this->numberOfPendingTasks;
    }

    /**
     * Gets the number of relocating shards.
     *
     * @return int
     */
    public function getNumberOfRelocatingShards(): int
    {
        return $this->relocatingShards;
    }

    /**
     * Gets the number of unassigned shards.
     *
     * @return int
     */
    public function getNumberOfUnassignedShards(): int
    {
        return $this->unassignedShards;
    }

    /**
     * Gets the cluster status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Gets the maximum waiting time (in milliseconds) for a queued task.
     *
     * @return int
     */
    public function getTaskMaxWaitingInQueueMillis(): int
    {
        return $this->taskMaxWaitingInQueueMillis;
    }

    /**
     * Gets whether the health report timed out.
     *
     * @return bool
     */
    public function hasTimedOut(): bool
    {
        return $this->timedOut;
    }
}
