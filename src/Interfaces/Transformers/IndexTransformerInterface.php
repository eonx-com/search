<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Transformers;

use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

interface IndexTransformerInterface
{
    /**
     * Transform index name.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $handler
     * @param object $object
     *
     * @return string
     */
    public function transformIndexName(SearchHandlerInterface $handler, object $object): string;

    /**
     * Transform index names.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler
     *
     * @return mixed[]
     */
    public function transformIndexNames(SearchHandlerInterface $searchHandler): array;
}
