<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Console\Command;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexLiveCommand extends Command
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface
     */
    private $searchHandlers;

    /**
     * SearchIndexLiveCommand constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $searchHandlers
     */
    public function __construct(IndexerInterface $indexer, RegisteredSearchHandlerInterface $searchHandlers)
    {
        $this->description = 'Atomically switches root aliases from search handlers to the latest index';
        $this->signature = 'search:index:live {--dry-run}';

        $this->indexer = $indexer;
        $this->searchHandlers = $searchHandlers;

        parent::__construct();
    }

    /**
     * Swap root alias to point to newest index created on a per-seach-handler basis
     *
     * @return void
     */
    public function handle(): void
    {
        $results = $this->indexer->indexSwap($this->searchHandlers->getAll(), $this->hasOption('dry-run'));

        $this->table(...$results->getTableData());
    }
}
