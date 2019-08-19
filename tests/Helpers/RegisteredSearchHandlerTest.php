<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\EntityHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Helpers\RegisteredSearchHandler
 */
class RegisteredSearchHandlerTest extends TestCase
{
    /**
     * Ensure the supplied search handlers matches the returned handlers from the same class
     *
     * @return void
     */
    public function testGettingAllHandlersMatchesSuppliedHandlers(): void
    {
        $entitySearchHandler = new EntityHandlerStub();
        $otherSearchHandler = new NonDoctrineHandlerStub();

        $expected = [
            $entitySearchHandler,
            $otherSearchHandler
        ];

        $registeredHandlers = $this->createInstance([
            $entitySearchHandler,
            $otherSearchHandler
        ]);

        $result = $registeredHandlers->getAll();

        self::assertSame($expected, $result);
    }

    /**
     * Test getting only entity search handlers.
     *
     * @return void
     */
    public function testGettingEntitySearchHandlersOnly(): void
    {
        $entitySearchHandler = new EntityHandlerStub();
        $otherSearchHandler = new NonDoctrineHandlerStub();

        $expected = [
            $entitySearchHandler
        ];

        $registeredHandlers = $this->createInstance([
            $entitySearchHandler,
            $otherSearchHandler
        ]);

        $result = $registeredHandlers->getEntityHandlers();

        self::assertSame($expected, $result);
    }

    /**
     * Create an instance of RegisteredSearchHandler
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
