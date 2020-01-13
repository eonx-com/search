<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Workers;

use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface;
use LoyaltyCorp\Search\Workers\EntityUpdateWorker;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\UpdateProcessorStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Workers\EntityUpdateWorker
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class EntityUpdateWorkerTest extends TestCase
{
    /**
     * Tests that the worker ignores properties when deciding if a subscription should be notified.
     *
     * @return void
     */
    public function testDeleteIgnoresSubscriptionProperties(): void
    {
        $searchHandlers = new RegisteredSearchHandlerStub([
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

        $expectedProcess = [
            [
                'indexSuffix' => '',
                'updates' => [
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForDelete(
                            stdClass::class,
                            ['id' => 7]
                        )
                    ),
                ],
            ],
        ];

        $updateProcessor = new UpdateProcessorStub();

        $worker = $this->createWorker($updateProcessor, $searchHandlers);

        $worker->handle([
            new DeletedEntity(stdClass::class, ['id' => 7], []),
        ]);

        self::assertEquals($expectedProcess, $updateProcessor->getProcessCalls());
    }

    /**
     * Tests that nothing happens when the subscription doesnt contain an intersection of property changes.
     *
     * @return void
     */
    public function testHandlesMatchingSubscriptionNoPropertyOverlap(): void
    {
        $searchHandlers = new RegisteredSearchHandlerStub([
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

        $updateProcessor = new UpdateProcessorStub();

        $worker = $this->createWorker($updateProcessor, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['not-interesting'], stdClass::class, []),
        ]);

        self::assertSame([], $updateProcessor->getProcessCalls());
    }

    /**
     * Tests that nothing happens when the subscription doesnt contain an intersection of property changes.
     *
     * @return void
     */
    public function testHandlesMatchingSubscriptionPropertiesNoTransform(): void
    {
        $searchHandlers = new RegisteredSearchHandlerStub([
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

        $expectedProcess = [
            [
                'indexSuffix' => '',
                'updates' => [
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 7]
                        )
                    ),
                ],
            ],
        ];

        $updateProcessor = new UpdateProcessorStub();

        $worker = $this->createWorker($updateProcessor, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 7]),
        ]);

        self::assertEquals($expectedProcess, $updateProcessor->getProcessCalls());
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

        $searchHandlers = new RegisteredSearchHandlerStub([
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

        $expectedProcess = [
            [
                'indexSuffix' => '',
                'updates' => [
                    new HandlerObjectForChange(
                        'handler',
                        new ObjectForUpdate(
                            stdClass::class,
                            ['id' => 49]
                        )
                    ),
                ],
            ],
        ];

        $updateProcessor = new UpdateProcessorStub();

        $worker = $this->createWorker($updateProcessor, $searchHandlers);

        $worker->handle([
            new UpdatedEntity(['interesting'], stdClass::class, ['id' => 7]),
        ]);

        self::assertEquals($expectedProcess, $updateProcessor->getProcessCalls());
    }

    /**
     * Tests that nothing happens when the changes array is empty.
     *
     * @return void
     */
    public function testHandlesNoChanges(): void
    {
        $updateProcessor = new UpdateProcessorStub();

        $worker = $this->createWorker($updateProcessor);

        $worker->handle([]);

        self::assertSame([], $updateProcessor->getProcessCalls());
    }

    /**
     * Tests that nothing happens we have no subscriptions.
     *
     * @return void
     */
    public function testHandlesNoSubscriptions(): void
    {
        $searchHandlers = new RegisteredSearchHandlerStub([
            'getSubscriptionsGroupedByClass' => [
                // Return no subscriptions
                [],
            ],
        ]);

        $updateProcessor = new UpdateProcessorStub();

        $worker = $this->createWorker($updateProcessor, $searchHandlers);

        $worker->handle([
            new UpdatedEntity([], stdClass::class, []),
        ]);

        self::assertSame([], $updateProcessor->getProcessCalls());
    }

    /**
     * Builds worker under test.
     *
     * @param \LoyaltyCorp\Search\Interfaces\UpdateProcessorInterface $updateProcessor
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface|null $searchHandlers
     *
     * @return \LoyaltyCorp\Search\Workers\EntityUpdateWorker
     */
    private function createWorker(
        UpdateProcessorInterface $updateProcessor,
        ?RegisteredSearchHandlerInterface $searchHandlers = null
    ): EntityUpdateWorker {
        return new EntityUpdateWorker(
            $searchHandlers ?? new RegisteredSearchHandlerStub(),
            $updateProcessor ?? new UpdateProcessorStub()
        );
    }
}
