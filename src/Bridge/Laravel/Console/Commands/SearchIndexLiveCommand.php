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
     * Swap root alias to point to newest index created on a per-search-handler basis.
     *
     * @return void
     */
    public function handle(): void
    {
        $dryRun = $this->isDryRun();

        if ($dryRun === true) {
            $this->info(\sprintf('Dry run mode - No changes will be executed'));
        }

        $results = $this->indexer->indexSwap($this->searchHandlers->getAll(), $dryRun);

        $this->table(...$results->getTableData());
    }

    /**
     * Determine if command is in dry-run mode.
     *
     * @return bool
     */
    private function isDryRun(): bool
    {
        return (bool)$this->option('dry-run');
    }
}
