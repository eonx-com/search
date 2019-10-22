<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Transformers;

use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexTransformerInterface;

final class CustomIndexTransformerStub implements IndexTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transformIndexName(SearchHandlerInterface $handler, object $object): string
    {
        return \sprintf('%s_%s', $handler->getIndexName(), 'customId');
    }

    /**
     * {@inheritdoc}
     */
    public function transformIndexNames(SearchHandlerInterface $searchHandler): array
    {
        return [
            \sprintf('%s_%s', $searchHandler->getIndexName(), 'customId')
        ];
    }
}
