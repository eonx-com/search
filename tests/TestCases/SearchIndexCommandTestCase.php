<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\TestCases;

use Illuminate\Console\Command;
use ReflectionClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @coversNothing
 */
abstract class SearchIndexCommandTestCase extends TestCase
{
    /**
     * Bootstrap I/O for a command instance so it is able to be executed & tested.
     *
     * @param \Illuminate\Console\Command $instance
     * @param \Symfony\Component\Console\Input\InputInterface|null $input
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     * @param string[]|null $options Signature command option definitions
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    protected function bootstrapCommand(
        Command $instance,
        ?InputInterface $input = null,
        ?OutputInterface $output = null,
        ?array $options = null
    ): void {
        // Use reflection to access input and output properties as these are protected
        // and derived from the application/console input/output
        $class = new ReflectionClass(\get_class($instance));
        $inputProperty = $class->getProperty('input');
        $outputProperty = $class->getProperty('output');

        // Set properties to public
        $inputProperty->setAccessible(true);
        $outputProperty->setAccessible(true);

        $inputDefinitions = [];
        foreach ($options ?? [] as $option) {
            $inputDefinitions[] = new InputOption($option);
        }

        // Set input/output property values
        $inputProperty->setValue($instance, $input ?? new ArrayInput([], new InputDefinition($inputDefinitions)));
        $outputProperty->setValue($instance, $output ?? new NullOutput());
    }
}
