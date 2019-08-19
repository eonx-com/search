<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
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
        $handlers = [];
        $registeredHandlers = $this->createInstance();

        $result = $registeredHandlers->getAll();

        self::assertSame($handlers, $result);
    }

    /**
     * Create an instance of RegisteredSearchHandler
     *
     * @param \LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface[]|null $searchHandlers
     *
     * @return \LoyaltyCorp\Search\Helpers\RegisteredSearchHandler
     */
    private function createInstance(?array $searchHandlers = null): RegisteredSearchHandler
    {
        return new RegisteredSearchHandler($searchHandlers ?? []);
    }
}
