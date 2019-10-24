<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexLiveCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
final class SearchIndexLiveCommandTest extends SearchIndexCommandTestCase
{
    /**
     * Ensure the command dryly running is only done when specified.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testDryRunModeIsNotDefault(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new TransformableSearchHandlerStub()];
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        $output = new BufferedOutput();
        $this->bootstrapCommand($command, null, $output, ['dry-run']);

        $command->handle();

        self::assertStringNotContainsString('Dry run mode - No changes will be executed', $output->fetch());
    }

    /**
     * Ensure the command dryly running is verbose to the output.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testDryRunModeOutputsMessage(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new TransformableSearchHandlerStub()];
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        $output = new BufferedOutput();
        $this->bootstrapCommand(
            $command,
            new ArrayInput(
                ['--dry-run' => true],
                new InputDefinition([new InputOption('dry-run')])
            ),
            $output
        );

        $command->handle();

        self::assertStringContainsString('Dry run mode - No changes will be executed', $output->fetch());
    }

    /**
     * Ensure search handlers are indeed passed to the indexSwap method.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testSearchHandlersPassedToIndexSwapMethod(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new TransformableSearchHandlerStub()];
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        $this->bootstrapCommand($command, null, null, ['dry-run']);

        $command->handle();

        self::assertSame(1, $indexer->getIndicesSwapped());
    }

    /**
     * Create command instance.
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
