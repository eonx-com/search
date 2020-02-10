<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange;
use LoyaltyCorp\Search\Events\BatchOfUpdatesEvent;
use LoyaltyCorp\Search\Populator;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\EventDispatcherStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Populator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) required to test
 */
final class PopulatorTest extends UnitTestCase
{
    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testBigBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2];

        $expected = [
            [
                'event' => new BatchOfUpdatesEvent(
                    '_suffix',
                    [
                        new HandlerObjectForChange('handlerKey', $update1),
                    ]
                ),
            ],
            [
                'event' => new BatchOfUpdatesEvent(
                    '_suffix',
                    [
                        new HandlerObjectForChange('handlerKey', $update2),
                    ]
                ),
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getHandlerKey' => 'handlerKey',
            'getFillIterable' => [$objects],
        ]);

        $dispatcher = new EventDispatcherStub();
        $populator = new Populator($dispatcher);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $dispatcher->getCalls('dispatch'));
    }

    /**
     * Tests the populator when the iterator is empty.
     *
     * @return void
     */
    public function testEmptyIterator(): void
    {
        $expected = [];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [[]],
        ]);

        $dispatcher = new EventDispatcherStub();
        $populator = new Populator($dispatcher);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $dispatcher->getCalls('dispatch'));
    }

    /**
     * Tests the populator when the batch is exactly the size of the batch count.
     *
     * @return void
     */
    public function testExactBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2];

        $expected = [
            [
                'event' => new BatchOfUpdatesEvent(
                    '_suffix',
                    [
                        new HandlerObjectForChange('handlerKey', $update1),
                        new HandlerObjectForChange('handlerKey', $update2),
                    ]
                ),
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getHandlerKey' => 'handlerKey',
            'getFillIterable' => [$objects],
        ]);

        $dispatcher = new EventDispatcherStub();
        $populator = new Populator($dispatcher);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $dispatcher->getCalls('dispatch'));
    }

    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testOddBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);
        $update3 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2, $update3];

        $expected = [
            [
                'event' => new BatchOfUpdatesEvent(
                    '_suffix',
                    [
                        new HandlerObjectForChange('handlerKey', $update1),
                        new HandlerObjectForChange('handlerKey', $update2),
                    ]
                ),
            ],
            [
                'event' => new BatchOfUpdatesEvent(
                    '_suffix',
                    [
                        new HandlerObjectForChange('handlerKey', $update3),
                    ]
                ),
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getHandlerKey' => 'handlerKey',
            'getFillIterable' => [$objects],
        ]);

        $dispatcher = new EventDispatcherStub();
        $populator = new Populator($dispatcher);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $dispatcher->getCalls('dispatch'));
    }

    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testSmallBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);

        $objects = [$update1];

        $expected = [
            [
                'event' => new BatchOfUpdatesEvent(
                    '_suffix',
                    [
                        new HandlerObjectForChange('handlerKey', $update1),
                    ]
                ),
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getHandlerKey' => 'handlerKey',
            'getFillIterable' => [$objects],
        ]);

        $dispatcher = new EventDispatcherStub();
        $populator = new Populator($dispatcher);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $dispatcher->getCalls('dispatch'));
    }
}
