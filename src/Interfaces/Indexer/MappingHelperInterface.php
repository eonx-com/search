<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Indexer;

use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

interface MappingHelperInterface
{
    /**
     * Builds mappings from a search handler which may optionally modify the
     * mappings.
     *
     * @param \LoyaltyCorp\Search\Interfaces\SearchHandlerInterface $searchHandler
     *
     * @return mixed[]
     */
    public function buildIndexMappings(SearchHandlerInterface $searchHandler): array;
}
