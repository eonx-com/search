<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Console\Command;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexFillCommand extends Command
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
     * SearchIndexFill constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $searchHandlers
     */
    public function __construct(IndexerInterface $indexer, RegisteredSearchHandlerInterface $searchHandlers)
    {
        $this->description = 'Populate all search handler indices with their corresponding data';
        $this->signature = 'search:index:fill {--batchSize=20}';

        $this->indexer = $indexer;
        $this->searchHandlers = $searchHandlers;

        parent::__construct();
    }


    /**
     * Populate data for all indices
     *
     * @return void
     */
    public function handle(): void
    {
        $allSearchHandlers = $this->searchHandlers->getAll();
        $totalHandlers = \count($allSearchHandlers);

        // Fill only handles entity search handlers.
        foreach ($this->searchHandlers->getEntityHandlers() as $iteration => $searchHandler) {
            $this->output->write(
                \sprintf(
                    '[%d/%d] Populating documents for \'%s\'... ',
                    $iteration,
                    $totalHandlers,
                    \get_class($searchHandler)
                )
            );

            $this->indexer->populate(
                $searchHandler,
                '_new',
                \is_numeric($this->option('batchSize')) ? (int)$this->option('batchSize') : 20
            );

            /**
             * @noinspection DisconnectedForeachInstructionInspection
             * ✓
             */
            $this->output->writeln("\xE2\x9C\x93");
        }
    }
}
