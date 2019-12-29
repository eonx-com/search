<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;

interface TransformableSearchHandlerInterface extends SearchHandlerInterface
{
    /**
     * Returns an iterable that will be used to fill the index when doing a full
     * index fill.
     *
     * @return iterable|mixed[]
     */
    public function getFillIterable(): iterable;

    /**
     * Get the class this search handler will support.
     *
     * @return string[] Fully Qualified Class Names that implement the Search Handler interface
     */
    public function getHandledClasses(): array;

    /**
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param mixed $object
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\DocumentAction|null
     */
    public function transform($object): ?DocumentAction;
}
