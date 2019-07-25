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
        $this->description = 'Remove any indices deriving from search handlers that are unused';
        $this->signature = 'search:index:clean';

        $this->indexer = $indexer;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleSearchHandler(HandlerInterface $handler): void
    {
        $this->info(\sprintf('Swapping index for \'%s\'', \get_class($handler)));

        $this->indexer->indexSwap($handler);
    }
}
