<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
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
        $handlers = [new HandlerStub()];
        $command = $this->createInstance([], new NullOutput(), $indexer, new RegisteredSearchHandlerStub($handlers));

        $command->handle();

        self::assertSame(1, $indexer->getIndicesSwapped());
    }

    /**
     * Create command instance
     *
     * @param mixed[] $options Options to pass to the command
     * @param \Symfony\Component\Console\Output\OutputInterface $output The interface to output the result to
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface|null $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface|null $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand
     *
     * @throws \ReflectionException If class being reflected does not exist
     */
    private function createInstance(
        array $options,
        OutputInterface $output,
        ?IndexerInterface $indexer = null,
        ?RegisteredSearchHandlerInterface $registeredHandlers = null
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
            $indexer ?? new IndexerStub(),
            $registeredHandlers ?? new RegisteredSearchHandlerStub()
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
