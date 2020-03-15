<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\TestCases\Bridge\Symfony;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @coversNothing
 */
abstract class SearchIndexCommandTestCase extends UnitTestCase
{
    /**
     * bootstrap I/O for a command and execute it.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @param \Symfony\Component\Console\Input\InputInterface|null $input
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @param string[]|null $options Signature command option definitions
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function runCommand(
        Command $command,
        ?InputInterface $input = null,
        ?OutputInterface $output = null,
        ?array $options = null
    ): void {
        $inputDefinitions = [];

        foreach ($options ?? [] as $option) {
            $inputDefinitions[] = new InputOption($option);
        }

        // Set input/output property values
        $input = $input ?? new ArrayInput([], new InputDefinition($inputDefinitions));
        $output = $output ?? new NullOutput();

        $command->run($input, $output);
    }
}
