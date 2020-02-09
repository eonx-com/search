<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexFillCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlersStub;
use Tests\LoyaltyCorp\Search\Stubs\PopulatorStub;
use Tests\LoyaltyCorp\Search\TestCases\Unit\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexFillCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
final class SearchIndexFillCommandTest extends SearchIndexCommandTestCase
{
    /**
     * Ensure the registered search handlers are passed through to the populate method on indexer.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testIndexerPopulateCalled(): void
    {
        $populator = new PopulatorStub();
        $handlerStub = new TransformableHandlerStub();
        $otherHandler = new TransformableHandlerStub('other');
        $handlers = [$handlerStub, $otherHandler];

        $registeredHandlers = new RegisteredSearchHandlersStub([
            'getTransformableHandlers' => [
                $handlers,
            ],
        ]);

        $command = $this->createInstance($populator, $registeredHandlers);
        $this->bootstrapCommand($command);

        $expectedCalls  = [
            'Tests\LoyaltyCorp\Search\Stubs\PopulatorStub::populate' => [
                [
                    'handler' => $handlerStub,
                    'indexSuffix' => '_new',
                    'batchSize' => 200,
                ],
                [
                    'handler' => $otherHandler,
                    'indexSuffix' => '_new',
                    'batchSize' => 200,
                ],
            ],
        ];

        $command->handle();

        self::assertSame($expectedCalls, $populator->getCalls());
    }

    /**
     * Instantiate a command class.
     *
     * @param \LoyaltyCorp\Search\Interfaces\PopulatorInterface|null $populator
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface|null $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexFillCommand
     */
    private function createInstance(
        ?PopulatorInterface $populator = null,
        ?RegisteredSearchHandlersInterface $registeredHandlers = null
    ): SearchIndexFillCommand {
        return new SearchIndexFillCommand(
            $populator ?? new PopulatorStub(),
            $registeredHandlers ?? new RegisteredSearchHandlersStub([])
        );
    }
}
