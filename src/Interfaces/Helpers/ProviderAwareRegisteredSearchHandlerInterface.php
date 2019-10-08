<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

interface ProviderAwareRegisteredSearchHandlerInterface
{
    /**
     * Get all provider ids.
     *
     * @return string[]
     */
    public function getAllProviderIds(): array;
}
