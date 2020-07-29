<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Logstash;

use Symfony\Component\Console\Output\OutputInterface;

interface MigratorInterface
{
    public function migrate(OutputInterface $output, string $providerClass): void;
}
