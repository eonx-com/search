<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCommand
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexLiveCommandTest extends TestCase
{
    /**
     * Ensure search handlers are indeed passed to the indexSwap method
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testSearchHandlersPassedToIndexSwapMethod(): void
    {
        $indexer = new IndexerStub();
        $container = new Container();
        $container->tag([
            HandlerStub::class
        ], ['search_handler']);
        $command = $this->createInstance([], new NullOutput(), $indexer, $container);

        $command->handle();

        self::assertSame(1, $indexer->getIndicesSwapped());
    }

    /**
     * Create command instance
     *
     * @param mixed[] $options Options to pass to the command
     * @param \Symfony\Component\Console\Output\OutputInterface $output The interface to output the result to
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface|null $indexer
     * @param \Illuminate\Contracts\Container\Container|null $container
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand
     *
     * @throws \ReflectionException If class being reflected does not exist
     */
    private function createInstance(
        array $options,
        OutputInterface $output,
        ?IndexerInterface $indexer = null,
        ?ContainerInterface $container = null
    ): SearchIndexLiveCommand {
        // Use reflection to access input and output properties as these are protected
        // and derived from the application/console input/output
        $class = new \ReflectionClass(SearchIndexLiveCommand::class);
        $inputProperty = $class->getProperty('input');
        $outputProperty = $class->getProperty('output');

        // Set properties to public
        $inputProperty->setAccessible(true);
        $outputProperty->setAccessible(true);

        // Create instance
        $instance = new SearchIndexLiveCommand(
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
