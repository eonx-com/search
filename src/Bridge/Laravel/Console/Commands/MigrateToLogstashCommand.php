<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Console\Command;
use LoyaltyCorp\Search\Interfaces\Logstash\MigratorInterface;

final class MigrateToLogstashCommand extends Command
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Logstash\MigratorInterface
     */
    private $migrator;

    public function __construct(MigratorInterface $migrator)
    {
        $this->description = 'Migrate search handlers to logstash and curator configuration';
        $this->signature = 'search:migrate:logstash {providerClass : Provider class path}';

        parent::__construct();
        $this->migrator = $migrator;
    }

    /**
     * Create fresh indices for all search handlers.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->migrator->migrate($this->output, $this->input->getArgument('providerClass'));
    }
}
