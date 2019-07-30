<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

interface EntityManagerHelperInterface
{
    /**
     * Yield all primary keys from the given entity
     *
     * @param string $entityClass
     *
     * @return iterable|string[]
     */
    public function iterateAllIds(string $entityClass): iterable;
}
