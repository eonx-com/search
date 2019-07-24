<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexFillCommand extends SearchIndexCommand
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * SearchIndexFill constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     */
    public function __construct(ContainerInterface $container, IndexerInterface $indexer)
    {
        $this->description = 'Populate all search handler indices with their corresponding data';
        $this->signature = 'search:index:fill {--batchSize=20}';

        $this->indexer = $indexer;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function handleSearchHandler(HandlerInterface $handler): void
    {
        $this->info(\sprintf('Populating documents for handler \'%s\'', \get_class($handler)));

        $this->indexer->populate(
            $handler,
            \is_numeric($this->option('batchSize')) ? (int)$this->option('batchSize') : 20
        );
    }
}
