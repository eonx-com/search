<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use ReflectionClass;
use Symfony\Component\Console\Output\NullOutput;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\OtherHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexCreateCommandTest extends TestCase
{
    /**
     * Ensure the number of indices created matches the number of registered search handlers via container tagging
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testIndicesCreated(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new HandlerStub(), new OtherHandlerStub()];
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        // Two search handlers registered should result in 2 'created' calls

        $command->handle();

        self::assertSame($handlers, $indexer->getCreatedHandlers());
    }

    /**
     * Create command instance
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
     *
     * @throws \ReflectionException If class being reflected does not exist
     */
    private function createInstance(
        IndexerInterface $indexer,
        RegisteredSearchHandlerInterface $registeredHandlers
    ): SearchIndexCreateCommand {
        // Use reflection to access input and output properties as these are protected
        // and derived from the application/console input/output
        $outputProperty = (new ReflectionClass(SearchIndexCreateCommand::class))->getProperty('output');

        // Set properties to public
        $outputProperty->setAccessible(true);

        // Create instance
        $instance = new SearchIndexCreateCommand(
            $indexer,
            $registeredHandlers
        );

        // Set input/output property values
        $outputProperty->setValue($instance, new NullOutput());

        return $instance;
    }
}
