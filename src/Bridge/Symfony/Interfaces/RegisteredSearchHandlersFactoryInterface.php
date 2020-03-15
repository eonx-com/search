<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Interfaces;

use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;

interface RegisteredSearchHandlersFactoryInterface
{
    /**
     * Create search RegisteredSearchHandlersInterface.
     *
     * @return \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface
     */
    public function create(): RegisteredSearchHandlersInterface;
}
