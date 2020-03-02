<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Helpers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerChangeSubscription;
use LoyaltyCorp\Search\Exceptions\DuplicateSearchHandlerKeyException;
use LoyaltyCorp\Search\Exceptions\HandlerDoesntExistException;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers
 */
final class RegisteredSearchHandlerTest extends UnitTestCase
{
    /**
     * Tests that the helper returns nothing when there are no handlers.
     *
     * @return void
     */
    public function testGetSubscriptionsGroupedByClassNoHandlers(): void
    {
        $registered = $this->createInstance([]);

        $result = $registered->getSubscriptionsGroupedByClass();

        self::assertSame([], $result);
    }

    /**
     * Tests that the helper returns subscriptions grouped by class.
     *
     * @return void
     */
    public function testGetSubscriptionsGroupedByClass(): void
    {
        $subscription1 = new ChangeSubscription(stdClass::class, ['prop1']);
        $subscription2 = new ChangeSubscription(stdClass::class, ['prop2']);
        $subscription3 = new ChangeSubscription(EntityStub::class, ['prop7']);

        $expected = [
            stdClass::class => [
                new HandlerChangeSubscription('handler1', $subscription1),
                new HandlerChangeSubscription('handler1', $subscription2),
            ],
            EntityStub::class => [
                new HandlerChangeSubscription('handler2', $subscription3),
            ],
        ];

        $handler1 = new TransformableHandlerStub('', [
            'getHandlerKey' => [
                'handler1',
                'handler1',
            ],
            'getSubscriptions' => [
                [$subscription1, $subscription2],
            ],
        ]);
        $handler2 = new TransformableHandlerStub('', [
            'getHandlerKey' => [
                'handler2',
            ],
            'getSubscriptions' => [
                [$subscription3],
            ],
        ]);

        $registered = $this->createInstance([
            $handler1,
            $handler2,
        ]);

        $result = $registered->getSubscriptionsGroupedByClass();

        self::assertEquals($expected, $result);
    }

    /**
     * Tests retrieving a handler by its key.
     *
     * @return void
     */
    public function testGetByHandlerKey(): void
    {
        $handler1 = new TransformableHandlerStub(null, [
            'getHandlerKey' => ['handler1', 'handler1'],
        ]);

        $registered = $this->createInstance([$handler1]);

        $result = $registered->getTransformableHandlerByKey('handler1');

        self::assertSame($handler1, $result);
    }

    /**
     * Tests exception when duplicate handler keys.
     *
     * @return void
     */
    public function testGetByHandlerKeyDuplicateKeys(): void
    {
        $handler1 = new TransformableHandlerStub(null, [
            'getHandlerKey' => ['handler1', 'handler1'],
        ]);
        $handler2 = new TransformableHandlerStub(null, [
            'getHandlerKey' => ['handler1', 'handler1'],
        ]);

        $registered = $this->createInstance([$handler1, $handler2]);

        $this->expectException(DuplicateSearchHandlerKeyException::class);
        $this->expectExceptionMessage('The handler key "handler1" is duplicated and must be unique.');

        $registered->getTransformableHandlerByKey('handler1');
    }

    /**
     * Tests exception when duplicate handler keys.
     *
     * @return void
     */
    public function testGetNonExistentHandlerByKey(): void
    {
        $registered = $this->createInstance([]);

        $this->expectException(HandlerDoesntExistException::class);
        $this->expectExceptionMessage('The handler with key "handler1" does not exist.');

        $registered->getTransformableHandlerByKey('handler1');
    }

    /**
     * Ensure the supplied search handlers matches the returned handlers from the same class.
     *
     * @return void
     */
    public function testGettingAllHandlersMatchesSuppliedHandlers(): void
    {
        $entitySearchHandler = new TransformableHandlerStub();
        $otherSearchHandler = new NonDoctrineHandlerStub();

        $expected = [
            $entitySearchHandler,
            $otherSearchHandler,
        ];

        $registeredHandlers = $this->createInstance([
            $entitySearchHandler,
            $otherSearchHandler,
        ]);

        $result = $registeredHandlers->getAll();

        self::assertSame($expected, $result);
    }

    /**
     * Test getting only transformable search handlers.
     *
     * @return void
     */
    public function testGettingTransformableSearchHandlersOnly(): void
    {
        $entitySearchHandler = new TransformableHandlerStub();
        $otherSearchHandler = new NonDoctrineHandlerStub();

        $expected = [
            $entitySearchHandler,
        ];

        $registeredHandlers = $this->createInstance([
            $entitySearchHandler,
            $otherSearchHandler,
        ]);

        $result = $registeredHandlers->getTransformableHandlers();

        self::assertSame($expected, $result);
    }

    /**
     * Create an instance of RegisteredSearchHandler.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]|null $searchHandlers
     *
     * @return \LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers
     */
    private function createInstance(?array $searchHandlers = null): RegisteredSearchHandlers
    {
        return new RegisteredSearchHandlers($searchHandlers ?? []);
    }
}
