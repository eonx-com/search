<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Console\Commands;

use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SearchIndexCreateCommand extends Command
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'search:index:create';

    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface
     */
    private $searchHandlers;

    /**
     * SearchIndexCreate constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface $searchHandlers
     */
    public function __construct(IndexerInterface $indexer, RegisteredSearchHandlersInterface $searchHandlers)
    {
        $this->indexer = $indexer;
        $this->searchHandlers = $searchHandlers;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Create date-based indices for all registered search handlers');
    }

    /**
     * Create fresh indices for all search handlers.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allSearchHandlers = $this->searchHandlers->getAll();
        $totalHandlers = \count($allSearchHandlers);

        foreach ($allSearchHandlers as $iteration => $searchHandler) {
            $output->write(
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
            $output->writeln("\xE2\x9C\x93");
        }

        return 0;
    }
}
