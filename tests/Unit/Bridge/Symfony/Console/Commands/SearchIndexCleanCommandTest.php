<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Console\Commands;

use LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexCleanCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlersStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\Bridge\Symfony\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexCleanCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
final class SearchIndexCleanCommandTest extends SearchIndexCommandTestCase
{
    /**
     * Ensure the number of indices cleaned matches the number of registered search handlers via container tagging.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testIndexerHandlesAllTaggedSearchHandlers(): void
    {
        $indexer = new IndexerStub();

        $handlers = [
            new TransformableHandlerStub(),
            new TransformableHandlerStub('other'),
        ];

        $registeredHandlers = new RegisteredSearchHandlersStub([
            'getAll' => [
                $handlers,
            ],
        ]);

        // Two search handlers registered should result in 2 indices passed to clean method
        $command = $this->createInstance($indexer, $registeredHandlers);
        $this->runCommand($command);

        $result = $indexer->getCleanedSearchHandlers();

        self::assertSame($handlers, $result);
    }

    /**
     * Create command instance.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexCleanCommand
     */
    private function createInstance(
        IndexerInterface $indexer,
        RegisteredSearchHandlersInterface $registeredHandlers
    ): SearchIndexCleanCommand {
        return new SearchIndexCleanCommand(
            $indexer,
            $registeredHandlers
        );
    }
}
