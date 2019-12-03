<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\ClusterHealth;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\ClusterHealth
 */
class ClusterHealthTest extends TestCase
{
    /**
     * Tests the DTO getters.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $data = [
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
            'active_shards_percent_as_number' => 50.0
        ];

        $instance = new ClusterHealth($data);

        self::assertSame('testcluster', $instance->getName());
        self::assertSame('yellow', $instance->getStatus());
        self::assertFalse($instance->hasTimedOut());
        self::assertSame(1, $instance->getNumberOfNodes());
        self::assertSame(2, $instance->getNumberOfDataNodes());
        self::assertSame(3, $instance->getNumberOfActivePrimaryShards());
        self::assertSame(4, $instance->getNumberOfActiveShards());
        self::assertSame(5, $instance->getNumberOfRelocatingShards());
        self::assertSame(6, $instance->getNumberOfInitializingShards());
        self::assertSame(7, $instance->getNumberOfUnassignedShards());
        self::assertSame(8, $instance->getNumberOfDelayedUnassignedShards());
        self::assertSame(9, $instance->getNumberOfPendingTasks());
        self::assertSame(10, $instance->getNumberOfInFlightFetch());
        self::assertSame(11, $instance->getTaskMaxWaitingInQueueMillis());
        self::assertSame(50, $instance->getActiveShardsPercent());
    }
}
