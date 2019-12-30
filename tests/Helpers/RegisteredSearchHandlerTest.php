<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use LoyaltyCorp\Search\Exceptions\DuplicateSearchHandlerKeyException;
use LoyaltyCorp\Search\Exceptions\HandlerDoesntExistException;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Helpers\RegisteredSearchHandler
 */
final class RegisteredSearchHandlerTest extends TestCase
{
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
     * @return \LoyaltyCorp\Search\Helpers\RegisteredSearchHandler
     */
    private function createInstance(?array $searchHandlers = null): RegisteredSearchHandler
    {
        return new RegisteredSearchHandler($searchHandlers ?? []);
    }
}
