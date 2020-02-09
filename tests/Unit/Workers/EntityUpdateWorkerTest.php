<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Workers;

use EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Workers\EntityUpdateWorker;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\EventDispatcherStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlersStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Workers\EntityUpdateWorker
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class EntityUpdateWorkerTest extends UnitTestCase
{
    /**
     * @var int
     */
    private const BATCH_SIZE = 2;

    /**
     * Tests that the worker ignores properties when deciding if a subscription should be notified.
     *
     * @return void
     */
    public function testDeleteIgnoresSubscriptionProperties(): void
    {
        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                [
                    stdClass::class => [
                        new HandlerChangeSubscription(
                            'handler',
                            new ChangeSubscription(
                                stdClass::class,
                                ['interesting']
                            )
                        ),
                    ],
                ],
            ],
        ]);

        $expectedDispatch = [
            [
                'event' => new BatchOfUpdatesEvent([
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForDelete(
                            stdClass::class,
                            ['id' => 7]
                        )
                    ),
                ]),
                'payload' => null,
                'halt' => null,
            ],
        ];

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new DeletedEntity(stdClass::class, ['id' => 7], []),
        ]);

        self::assertEquals($expectedDispatch, $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that nothing happens when the subscription doesnt contain an intersection of property changes.
     *
     * @return void
     */
    public function testHandlesMatchingSubscriptionNoPropertyOverlap(): void
    {
        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                [
                    stdClass::class => [
                        new HandlerChangeSubscription(
                            'handler',
                            new ChangeSubscription(
                                stdClass::class,
                                ['interesting']
                            )
                        ),
                    ],
                ],
            ],
        ]);

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['not-interesting'], stdClass::class, []),
        ]);

        self::assertSame([], $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that nothing happens when the subscription doesnt contain an intersection of property changes.
     *
     * @return void
     */
    public function testHandlesMatchingSubscriptionPropertiesNoTransform(): void
    {
        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                [
                    stdClass::class => [
                        new HandlerChangeSubscription(
                            'handler',
                            new ChangeSubscription(
                                stdClass::class,
                                ['interesting']
                            )
                        ),
                    ],
                ],
            ],
        ]);

        $expectedDispatch = [
            [
                'event' => new BatchOfUpdatesEvent([
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 7]
                        )
                    ),
                ]),
                'payload' => null,
                'halt' => null,
            ],
        ];

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 7]),
        ]);

        self::assertEquals($expectedDispatch, $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that nothing happens when the subscription doesnt contain an intersection of property changes.
     *
     * @return void
     */
    public function testHandlesMatchingSubscriptionPropertiesWithTransform(): void
    {
        /**
         * Transforms the changed entity into a related ObjectForUpdate.
         *
         * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $changedEntity
         *
         * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
         */
        $transform = static function (ChangedEntity $changedEntity): array {
            $originalId = $changedEntity->getIds()['id'] ?? 7;

            return [
                new ObjectForUpdate(
                    stdClass::class,
                    // Return the same class of object, but a related id instead of the id we got.
                    ['id' => $originalId ** 2]
                ),
            ];
        };

        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                [
                    stdClass::class => [
                        new HandlerChangeSubscription(
                            'handler',
                            new ChangeSubscription(
                                stdClass::class,
                                ['interesting'],
                                $transform
                            )
                        ),
                    ],
                ],
            ],
        ]);

        $expectedDispatch = [
            [
                'event' => new BatchOfUpdatesEvent([
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 49]
                        )
                    ),
                ]),
                'payload' => null,
                'halt' => null,
            ],
        ];

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 7]),
        ]);

        self::assertEquals($expectedDispatch, $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that nothing happens when the subscription doesnt contain an intersection of property changes.
     *
     * @return void
     */
    public function testHandlesMatchingSubscriptionNullProperties(): void
    {
        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                [
                    stdClass::class => [
                        new HandlerChangeSubscription(
                            'handler',
                            new ChangeSubscription(stdClass::class)
                        ),
                    ],
                ],
            ],
        ]);

        $expectedDispatch = [
            [
                'event' => new BatchOfUpdatesEvent([
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 7]
                        )
                    ),
                ]),
                'payload' => null,
                'halt' => null,
            ],
        ];

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 7]),
        ]);

        self::assertEquals($expectedDispatch, $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that HandlerObjectForChange array is dispatched by batches.
     *
     * @return void
     */
    public function testHandlesDispatchingByBatches(): void
    {
        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                [
                    stdClass::class => [
                        new HandlerChangeSubscription(
                            'handler',
                            new ChangeSubscription(stdClass::class)
                        ),
                    ],
                ],
            ],
        ]);

        $expectedDispatch = [
            [
                'event' => new BatchOfUpdatesEvent([
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 7]
                        )
                    ),
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 8]
                        )
                    ),
                ]),
                'payload' => null,
                'halt' => null,
            ],
            [
                'event' => new BatchOfUpdatesEvent([
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 9]
                        )
                    ),
                ]),
                'payload' => null,
                'halt' => null,
            ],
        ];

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 7]),
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 8]),
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 9]),
        ]);

        self::assertEquals($expectedDispatch, $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that nothing happens when the changes array is empty.
     *
     * @return void
     */
    public function testHandlesNoChanges(): void
    {
        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher);

        $worker->handle([]);

        self::assertSame([], $eventDispatcher->getDispatchCalls());
    }

    /**
     * Tests that nothing happens we have no subscriptions.
     *
     * @return void
     */
    public function testHandlesNoSubscriptions(): void
    {
        $searchHandlers = new RegisteredSearchHandlersStub([
            'getSubscriptionsGroupedByClass' => [
                // Return no subscriptions
                [],
            ],
        ]);

        $eventDispatcher = new EventDispatcherStub();

        $worker = $this->createWorker($eventDispatcher, $searchHandlers);

        $worker->handle([
            new UpdatedEntity([], stdClass::class, []),
        ]);

        self::assertSame([], $eventDispatcher->getDispatchCalls());
    }

    /**
     * Builds worker under test.
     *
     * @param \EoneoPay\Externals\EventDispatcher\Interfaces\EventDispatcherInterface $eventDispatcher
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface|null $searchHandlers
     *
     * @return \LoyaltyCorp\Search\Workers\EntityUpdateWorker
     */
    private function createWorker(
        EventDispatcherInterface $eventDispatcher,
        ?RegisteredSearchHandlersInterface $searchHandlers = null
    ): EntityUpdateWorker {
        return new EntityUpdateWorker(
            $searchHandlers ?? new RegisteredSearchHandlersStub(),
            $eventDispatcher ?? new EventDispatcherStub(),
            self::BATCH_SIZE
        );
    }
}
