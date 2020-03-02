<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Transformers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

interface IndexNameTransformerInterface
{
    /**
     * Transform index name for a single update.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $handler
     * @param ObjectForChange<mixed> $object
     *
     * @return string
     */
    public function transformIndexName(SearchHandlerInterface $handler, ObjectForChange $object): string;

    /**
     * Transform index names.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler
     *
     * @return mixed[]
     */
    public function transformIndexNames(SearchHandlerInterface $searchHandler): array;
}
