<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Console\Command;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexCleanCommand extends Command
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
     * SearchIndexClean constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $searchHandlers
     */
    public function __construct(IndexerInterface $indexer, RegisteredSearchHandlerInterface $searchHandlers)
    {
        $this->description = 'Remove any indices deriving from search handlers that are unused';
        $this->signature = 'search:index:clean';

        $this->indexer = $indexer;
        $this->searchHandlers = $searchHandlers;

        parent::__construct();
    }

    /**
     * Remove all indices relating to search handlers that are no longer used by any aliases.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Removing all unused indices across search handlers');

        // Warn - prompt
        $this->indexer->clean($this->searchHandlers->getAll());
    }
}
