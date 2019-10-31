<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Interfaces;

use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;

interface EntityManagerHelperFactoryInterface
{
    /**
     * Create entity manager helper.
     *
     * @return \LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface
     */
    public function create(): EntityManagerHelperInterface;
}
