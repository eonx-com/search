<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Interfaces;

use LoyaltyCorp\Search\Interfaces\ClientInterface;

interface ClientFactoryInterface
{
    /**
     * Create search client.
     *
     * @return \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    public function create(): ClientInterface;
}
