<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;

final class SearchIndexCleanCommand extends SearchIndexCommand
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $elasticClient;

    /**
     * @var string[]
     */
    private $usedIndexes = [];

    /**
     * SearchIndexFill constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $elasticClient
     *
     * @throws \Exception
     */
    public function __construct(
        ContainerInterface $container,
        ClientInterface $elasticClient
    ) {
        $this->description = '';
        $this->signature = 'search:index:clean';

        $this->elasticClient = $elasticClient;

        parent::__construct($container);
    }

    /**
     * Remove indexes that do not belong to an alias
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->getSearchHandlers() as $searchHandler) {
            $this->handleSearchHandler($searchHandler);
        }

        $allIndexes = $this->getAllIndexNames();

        //foreach ($rootIndexes as $rootIndex) {
        $this->elasticClient->getAliases();
        $this->elasticClient->getIndices();
        //}
    }

    /**
     * Do something with all iterated seach handlers
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $handler
     *
     * @return void
     */
    protected function handleSearchHandler(HandlerInterface $handler): void
    {
    }

    /**
     * Get a one dimensional list of all index names
     *
     * @return string[]
     */
    private function getAllIndexNames(): array
    {
        $indices = [];

        foreach ($this->elasticClient->getIndices() as $index) {
            $indices[] = $index['name'];
        }

        return $indices;
    }

    /**
     *
     *
     * @return string[]
     */
    private function getAllIndexesUsedByAliases(): array
    {
        $indices = [];
        foreach ($this->elasticClient->getAliases() as $alias) {

        }

        return $indices;
    }
}
