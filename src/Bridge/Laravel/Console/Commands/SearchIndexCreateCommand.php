<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Console\Command;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;

final class SearchIndexCreateCommand extends Command
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
     * SearchIndexCreate constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $searchHandlers
     */
    public function __construct(IndexerInterface $indexer, RegisteredSearchHandlerInterface $searchHandlers)
    {
        $this->description = 'Create date-based indices for all registered search handlers';
        $this->signature = 'search:index:create';

        $this->indexer = $indexer;
        $this->searchHandlers = $searchHandlers;

        parent::__construct();
    }

    /**
     * Create fresh indices for all search handlers
     *
     * @return void
     */
    public function handle(): void
    {
        $allSearchHandlers = $this->searchHandlers->getAll();
        $totalHandlers = \count($allSearchHandlers);

        foreach ($this->searchHandlers->getAll() as $iteration => $searchHandler) {
            $this->output->write(
                \sprintf(
                    '[%d/%d] Creating index for \'%s\'... ',
                    $iteration,
                    $totalHandlers,
                    \get_class($searchHandler)
                )
            );

            $this->indexer->create($searchHandler);

            /**
             * @noinspection DisconnectedForeachInstructionInspection
             * ✓
             */
            $this->output->writeln("\xE2\x9C\x93");
        }
    }
}
