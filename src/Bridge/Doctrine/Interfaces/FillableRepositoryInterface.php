<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Doctrine\Interfaces;

/**
 * @template T
 */
interface FillableRepositoryInterface
{
    /**
     * Returns an iterable that is used to fill search indices with the entity
     * that the repository belongs to.
     *
     * @phpstan-return array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>>
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
     */
    public function getFillIterable(): iterable;

    /**
     * Prefills changes with their entities. The method will call setObject() on each
     * ObjectForChange that it can preload an object for.
     *
     * @phpstan-param array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>> $changes
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[] $changes
     *
     * @return void
     */
    public function prefillSearch(iterable $changes): void;
}
