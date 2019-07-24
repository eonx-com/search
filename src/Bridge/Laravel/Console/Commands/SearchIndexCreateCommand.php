<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexCreateCommand extends SearchIndexCommand
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * SearchIndexCreate constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     */
    public function __construct(
        ContainerInterface $container,
        IndexerInterface $indexer
    ) {
        $this->description = 'Create date-based indices for all registered search handlers';
        $this->signature = 'search:index:create';

        $this->indexer = $indexer;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function handleSearchHandler(HandlerInterface $searchHandler): void
    {
        $this->info(\sprintf('Processing search handler \'%s\'', \get_class($searchHandler)));

        $this->indexer->create($searchHandler);
    }
}
