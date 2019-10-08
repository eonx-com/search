<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use LoyaltyCorp\Search\Interfaces\Helpers\IndexHelperInterface;
use LoyaltyCorp\Search\Interfaces\ProviderAwareInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

final class IndexHelper implements IndexHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIndexName(SearchHandlerInterface $searchHandler): string
    {
        if (($searchHandler instanceof ProviderAwareInterface) === true) {
            return \sprintf('%s_%s', $searchHandler->getIndexName(), $searchHandler->getProviderId());
        }

        return $searchHandler->getIndexName();
    }
}
