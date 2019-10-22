<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface ProviderAwareInterface
{
    /**
     * Get provider id for given object.
     *
     * @param object $object
     *
     * @return string
     */
    public function getProviderId(object $object): string;
}
