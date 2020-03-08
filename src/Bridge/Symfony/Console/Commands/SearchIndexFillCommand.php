<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Console\Commands;

use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SearchIndexFillCommand extends Command
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\PopulatorInterface
     */
    private $populator;

    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface
     */
    private $searchHandlers;

    /**
     * SearchIndexFill constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\PopulatorInterface $populator
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface $searchHandlers
     */
    public function __construct(PopulatorInterface $populator, RegisteredSearchHandlersInterface $searchHandlers)
    {
        $this->populator = $populator;
        $this->searchHandlers = $searchHandlers;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('search:index:fill')
            ->setDescription('Populate all search handler indices with their corresponding data');
    }

    /**
     * Create fresh indices for all search handlers.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $allSearchHandlers = $this->searchHandlers->getTransformableHandlers();
        $totalHandlers = \count($allSearchHandlers);

        $batchSize = 200;

        foreach ($allSearchHandlers as $iteration => $handler) {
            $output->write(
                \sprintf(
                    '[%d/%d] Populating documents for \'%s\'... ',
                    $iteration,
                    $totalHandlers,
                    \get_class($handler)
                )
            );

            $this->populator->populate($handler, '_new', $batchSize);

            // ✓
            /** @noinspection DisconnectedForeachInstructionInspection */
            $output->writeln("\xE2\x9C\x93");
        }
    }
}
