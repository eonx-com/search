<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexLiveCommandTest extends SearchIndexCommandTestCase
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
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        $this->bootstrapCommand($command);

        $command->handle();

        self::assertSame(1, $indexer->getIndicesSwapped());
    }

    /**
     * Create command instance
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand
     */
    private function createInstance(
        IndexerInterface $indexer,
        RegisteredSearchHandlerInterface $registeredHandlers
    ): SearchIndexLiveCommand {
        return new SearchIndexLiveCommand(
            $indexer,
            $registeredHandlers
        );
    }
}
