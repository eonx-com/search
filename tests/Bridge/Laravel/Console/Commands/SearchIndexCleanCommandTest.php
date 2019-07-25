<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCleanCommand;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\OtherHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCommand
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCleanCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexCleanCommandTest extends TestCase
{
    /**
     * Ensure the number of indices cleaned matches the number of registered search handlers via container tagging
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testIndexerHandlesAllTaggedSearchHandlers(): void
    {
        $indexer = new IndexerStub();
        $container = new Container();
        $container->tag([
            HandlerStub::class,
            OtherHandlerStub::class
        ], ['search_handler']);
        $command = $this->createInstance([], new NullOutput(), $indexer, $container);

        $command->handle();

        // Two search handlers registered should result in 2 indices passed to clean method
        $result = \array_map('\get_class', $indexer->getCleanedSearchHandlers());

        self::assertSame([HandlerStub::class, OtherHandlerStub::class], $result);
    }

    /**
     * Create command instance
     *
     * @param mixed[] $options Options to pass to the command
     * @param \Symfony\Component\Console\Output\OutputInterface $output The interface to output the result to
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface|null $indexer
     * @param \Illuminate\Contracts\Container\Container|null $container
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCleanCommand
     *
     * @throws \ReflectionException If class being reflected does not exist
     */
    private function createInstance(
        array $options,
        OutputInterface $output,
        ?IndexerInterface $indexer = null,
        ?ContainerInterface $container = null
    ): SearchIndexCleanCommand {
        // Use reflection to access input and output properties as these are protected
        // and derived from the application/console input/output
        $class = new \ReflectionClass(SearchIndexCleanCommand::class);
        $inputProperty = $class->getProperty('input');
        $outputProperty = $class->getProperty('output');

        // Set properties to public
        $inputProperty->setAccessible(true);
        $outputProperty->setAccessible(true);

        // Create instance
        $instance = new SearchIndexCleanCommand(
            $container ?? new Container(),
            $indexer ?? new IndexerStub()
        );

        // Set input/output property values
        $inputProperty->setValue($instance, new ArrayInput($options));
        $outputProperty->setValue($instance, $output);

        return $instance;
    }
}
