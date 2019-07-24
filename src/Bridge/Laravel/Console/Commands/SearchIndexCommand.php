<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;

abstract class SearchIndexCommand extends Command
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * SearchIndexCreate constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    /**
     * Iterate all tagged search handlers and pass to handle method
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->getSearchHandlers() as $searchHandler) {
            $this->handleSearchHandler($searchHandler);
        }
    }

    /**
     * Handler for console command passed through individually all registered search handlers
     *
     * @param \LoyaltyCorp\Search\Interfaces\HandlerInterface $handler
     *
     * @return void
     */
    abstract protected function handleSearchHandler(HandlerInterface $handler): void;

    /**
     * Yield all seach handlers instances that are configured in container
     *
     * @return iterable|\LoyaltyCorp\Search\Interfaces\HandlerInterface[]
     */
    protected function getSearchHandlers(): iterable
    {
        foreach ($this->container->tagged('search_handler') as $searchHandler) {
            /** @var \LoyaltyCorp\Search\Interfaces\HandlerInterface|mixed $searchHandler */
            if (($searchHandler instanceof HandlerInterface) === false) {
                continue;
            }

            yield $searchHandler;
        }
    }
}
