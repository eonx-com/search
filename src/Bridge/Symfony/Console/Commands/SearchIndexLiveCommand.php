<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Console\Commands;

use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SearchIndexLiveCommand extends Command
{
    /**
     * @var null|string The default command name
     */
    protected static $defaultName = 'search:index:live';

    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface
     */
    private $searchHandlers;

    /**
     * SearchIndexLiveCommand constructor.
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
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Enable dry run mode.')
            ->setDescription('Atomically switches root aliases from search handlers to the latest index');
    }

    /**
     * Swap root alias to point to newest index created on a per-search-handler basis.
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
        $dryRun = $this->isDryRun($input);

        if ($dryRun === true) {
            $output->writeln(\sprintf('Dry run mode - No changes will be executed'));
        }

        $results = $this->indexer->indexSwap($this->searchHandlers->getAll(), $dryRun);

        // Display results as table.
        $tableData = $results->getTableData();

        $table = new Table($output);

        $table->setHeaders($tableData[0] ?? [])->setRows($tableData[1] ?? []);
        $table->render();

        return 0;
    }

    /**
     * Determine if command is in dry-run mode.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return bool
     */
    private function isDryRun(InputInterface $input): bool
    {
        return (bool)$input->getOption('dry-run');
    }
}
