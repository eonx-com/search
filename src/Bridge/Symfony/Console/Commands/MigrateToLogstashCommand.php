<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Console\Commands;

use LoyaltyCorp\Search\Interfaces\Logstash\MigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateToLogstashCommand extends Command
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'search:migrate:logstash';

    /**
     * @var \LoyaltyCorp\Search\Interfaces\IndexerInterface
     */
    private $indexer;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Logstash\MigratorInterface
     */
    private $migrator;

    public function __construct(MigratorInterface $migrator)
    {
        $this->migrator = $migrator;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Migrate search handlers to logstash and curator configuration');
        $this->addArgument('providerClass', InputArgument::REQUIRED, 'Provider class path');
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
        $this->migrator->migrate($output, $input->getArgument('providerClass'));

        return 0;
    }
}
