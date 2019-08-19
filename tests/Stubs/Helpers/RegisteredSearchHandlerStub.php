<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;

/**
 * This stub extends from the original class, as the functionality it provides
 * is no different from the real class. This can be changed in future if more logic
 * is added to the real class.
 *
 * @noinspection EmptyClassInspection Class intentionally left empty for tests
 *
 * @coversNothing
 */
class RegisteredSearchHandlerStub extends RegisteredSearchHandler
{
    /**
     * RegisteredSearchHandlerStub constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface[]|null $searchHandlers
     */
    public function __construct(?array $searchHandlers = null)
    {
        parent::__construct($searchHandlers ?? []);
    }
}
