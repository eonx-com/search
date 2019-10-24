<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

interface EntityManagerHelperInterface
{
    /**
     * Wrapper for finding many entities from an array of ids.
     *
     * @param string $class
     * @param int[]|string[] $ids
     *
     * @return mixed[] Array of instantiated entities based on $class argument
     */
    public function findAllIds(string $class, array $ids): array;

    /**
     * Yield all primary keys from the given entity.
     *
     * @param string $entityClass
     *
     * @return iterable|string[]
     */
    public function iterateAllIds(string $entityClass): iterable;
}
