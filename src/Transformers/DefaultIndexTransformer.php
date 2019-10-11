<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Transformers;

use LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface;

final class DefaultIndexTransformer implements IndexTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transformIndexName(EntitySearchHandlerInterface $handler, object $object): string
    {
        return $handler->getIndexName();
    }

    /**
     * {@inheritdoc}
     */
    public function transformIndexNames(SearchHandlerInterface $searchHandler): array
    {
        return [
            $searchHandler->getIndexName()
        ];
    }
}
