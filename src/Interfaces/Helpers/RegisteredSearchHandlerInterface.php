<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

interface RegisteredSearchHandlerInterface
{
    /**
     * Get all search handlers that have been registered in the container
     *
     * @return \LoyaltyCorp\Search\Interfaces\HandlerInterface[]
     */
    public function getAll(): array;
}
