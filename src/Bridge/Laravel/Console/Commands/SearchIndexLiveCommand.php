<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexLiveCommand extends SearchIndexCommand
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * SearchIndexLiveCommand constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     */
    public function __construct(ContainerInterface $container, IndexerInterface $indexer)
    {
        $this->description = 'Atomically switches root aliases from search handlers to the latest index';
        $this->signature = 'search:index:live';

        $this->indexer = $indexer;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleSearchHandler(HandlerInterface $handler): void
    {
        $this->info(\sprintf('Swapping index for \'%s\'', \get_class($handler)));

        $this->indexer->indexSwap([$handler]);
    }
}
