<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Transformers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;

final class DefaultIndexNameTransformer implements IndexNameTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transformIndexName(SearchHandlerInterface $handler, ObjectForChange $object): string
    {
        return \mb_strtolower($handler->getIndexName());
    }

    /**
     * {@inheritdoc}
     */
    public function transformIndexNames(SearchHandlerInterface $searchHandler): array
    {
        return [
            \mb_strtolower($searchHandler->getIndexName()),
        ];
    }
}
