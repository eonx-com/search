<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\Console\Commands;

use LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexLiveCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlersStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\Bridge\Symfony\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexLiveCommand
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
     * @throws \Exception
     */
    public function testDryRunModeIsNotDefault(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new TransformableHandlerStub()];

        $registeredHandlers = new RegisteredSearchHandlersStub([
            'getAll' => [
                $handlers,
            ],
        ]);

        $command = $this->createInstance($indexer, $registeredHandlers);
        $output = new BufferedOutput();
        $this->runCommand($command, null, $output, ['dry-run']);

        self::assertStringNotContainsString('Dry run mode - No changes will be executed', $output->fetch());
    }

    /**
     * Ensure the command dryly running is verbose to the output.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testDryRunModeOutputsMessage(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new TransformableHandlerStub()];

        $registeredHandlers = new RegisteredSearchHandlersStub([
            'getAll' => [
                $handlers,
            ],
        ]);

        $command = $this->createInstance($indexer, $registeredHandlers);
        $output = new BufferedOutput();
        $this->runCommand(
            $command,
            new ArrayInput(
                ['--dry-run' => true],
                new InputDefinition([new InputOption('dry-run')])
            ),
            $output
        );

        self::assertStringContainsString('Dry run mode - No changes will be executed', $output->fetch());
    }

    /**
     * Ensure search handlers are indeed passed to the indexSwap method.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testSearchHandlersPassedToIndexSwapMethod(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new TransformableHandlerStub()];

        $registeredHandlers = new RegisteredSearchHandlersStub([
            'getAll' => [
                $handlers,
            ],
        ]);

        $command = $this->createInstance($indexer, $registeredHandlers);
        $this->runCommand($command, null, null, ['dry-run']);

        self::assertSame(1, $indexer->getIndicesSwapped());
    }

    /**
     * Create command instance.
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Symfony\Console\Commands\SearchIndexLiveCommand
     */
    private function createInstance(
        IndexerInterface $indexer,
        RegisteredSearchHandlersInterface $registeredHandlers
    ): SearchIndexLiveCommand {
        return new SearchIndexLiveCommand(
            $indexer,
            $registeredHandlers
        );
    }
}
