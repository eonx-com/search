<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;

final class SearchIndexLiveCommand extends SearchIndexCommand
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\ClientInterface
     */
    private $elasticClient;

    /**
     * SearchIndexCreate constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface $elasticClient
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @throws \Exception
     */
    public function __construct(
        ClientInterface $elasticClient,
        ContainerInterface $container
    ) {
        $this->description = 'Configure on a per-search handler basis which' .
            'index should be used as the root named alias';
        $this->signature = 'search:index:live';

        $this->elasticClient = $elasticClient;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function handleSearchHandler(HandlerInterface $handler): void
    {
        $temporaryIndex = \sprintf('%s_new', $handler->getIndexName());
        // @todo RENAME INTERFACe?
        $rootAlias = $handler->getIndexName();

        $latestIndex = null;

        // @todo refactor
        foreach ($this->elasticClient->getIndices() as $index) {
            if (\in_array($temporaryIndex, $index['aliases'], true) === true) {
                $latestIndex = $index['name'];
            }
        }

        if ($latestIndex === null) {
            throw new \Exception('');
        }

        // Switch root alias over to resolved '_new' aliased index
        $this->elasticClient->deleteAlias('*', $rootAlias);
        $this->elasticClient->createAlias($latestIndex, $rootAlias);
    }
}
