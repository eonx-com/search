<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\OtherHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCommand
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexCreateCommandTest extends TestCase
{
    /**
     * Ensure a tagged search handler that does not implement the interface is not actually attempted execution on
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testNonSearchHandlerRegisteredIsSkipped(): void
    {
        $indexer = new IndexerStub();
        $container = new Container();
        $container->tag([
            IndexerStub::class
        ], ['search_handler']);
        $command = $this->createInstance([], new NullOutput(), $indexer, $container);

        $command->handle();

        // No indices because the tagged search handler does not have the HandlerInterface
        self::assertSame(0, $indexer->getCreatedCount());
    }

    /**
     * Ensure the number of indices created matches the number of registered search handlers via container tagging
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testNumberOfIndicesCreated(): void
    {
        $indexer = new IndexerStub();
        $container = new Container();
        $container->tag([
            HandlerStub::class,
            OtherHandlerStub::class
        ], ['search_handler']);
        $command = $this->createInstance([], new NullOutput(), $indexer, $container);

        $command->handle();

        // Two search handlers registered should result in 2 indices
        self::assertSame(2, $indexer->getCreatedCount());
    }

    /**
     * Create command instance
     *
     * @param mixed[] $options Options to pass to the command
     * @param \Symfony\Component\Console\Output\OutputInterface $output The interface to output the result to
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface|null $indexer
     * @param \Illuminate\Contracts\Container\Container|null $container
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
     *
     * @throws \ReflectionException If class being reflected does not exist
     */
    private function createInstance(
        array $options,
        OutputInterface $output,
        ?IndexerInterface $indexer = null,
        ?ContainerInterface $container = null
    ): SearchIndexCreateCommand {
        // Use reflection to access input and output properties as these are protected
        // and derived from the application/console input/output
        $class = new \ReflectionClass(SearchIndexCreateCommand::class);
        $inputProperty = $class->getProperty('input');
        $outputProperty = $class->getProperty('output');

        // Set properties to public
        $inputProperty->setAccessible(true);
        $outputProperty->setAccessible(true);

        // Create instance
        $instance = new SearchIndexCreateCommand(
            $container ?? new Container(),
            $indexer ?? new IndexerStub()
        );

        // Set input/output property values
        $inputProperty->setValue($instance, new ArrayInput(
            $options,
            new InputDefinition([new InputOption('batchSize')])
        ));
        $outputProperty->setValue($instance, $output);

        return $instance;
    }
}
