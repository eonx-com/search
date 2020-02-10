<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlersStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\Unit\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
final class SearchIndexCreateCommandTest extends SearchIndexCommandTestCase
{
    /**
     * Ensure the number of indices created matches the number of registered search handlers via container tagging.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testIndicesCreated(): void
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

        // Two search handlers registered should result in 2 'created' calls
        $command = $this->createInstance($indexer, $registeredHandlers);
        $this->bootstrapCommand($command);

        $command->handle();

        self::assertSame($handlers, $indexer->getCreatedHandlers());
    }

    /**
     * Create command instance.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
     */
    private function createInstance(
        IndexerInterface $indexer,
        RegisteredSearchHandlersInterface $registeredHandlers
    ): SearchIndexCreateCommand {
        return new SearchIndexCreateCommand(
            $indexer,
            $registeredHandlers
        );
    }
}
