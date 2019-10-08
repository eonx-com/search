<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

interface IndexHelperInterface
{
    /**
     * Get provider specific index if search handler is provider aware.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler Search handler
     *
     * @return string Index name
     */
    public function getIndexName(SearchHandlerInterface $searchHandler): string;
}
