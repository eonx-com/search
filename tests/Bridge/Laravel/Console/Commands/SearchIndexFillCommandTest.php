<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexFillCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\OtherTransformableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexFillCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexFillCommandTest extends SearchIndexCommandTestCase
{
    /**
     * Ensure the registered search handlers are passed through to the populate method on indexer
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testIndexerPopulateCalled(): void
    {
        $indexer = new IndexerStub();

        $handlerStub = new TransformableSearchHandlerStub();
        $otherHandler = new OtherTransformableSearchHandlerStub();
        $handlers = [$handlerStub, $otherHandler];
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        $this->bootstrapCommand($command);

        $expectedPopulated = [
            [
                'handler' => $handlerStub,
                'indexSuffix' => '_new',
                'batchSize' => 200
            ],
            [
                'handler' => $otherHandler,
                'indexSuffix' => '_new',
                'batchSize' => 200
            ]
        ];

        $command->handle();

        self::assertSame($expectedPopulated, $indexer->getPopulatedHandlers());
    }

    /**
     * Instantiate a command class
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface|null $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface|null $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexFillCommand
     */
    private function createInstance(
        ?IndexerInterface $indexer = null,
        ?RegisteredSearchHandlerInterface $registeredHandlers = null
    ): SearchIndexFillCommand {
        return new SearchIndexFillCommand(
            $indexer ?? new IndexerStub(),
            $registeredHandlers ?? new RegisteredSearchHandlerStub()
        );
    }
}
