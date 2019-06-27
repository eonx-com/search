<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use EoneoPay\Utils\DateTime;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\HandlerInterface;

final class SearchIndexCreateCommand extends SearchIndexCommand
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
     */
    public function __construct(
        ClientInterface $elasticClient,
        ContainerInterface $container
    ) {
        $this->description = 'Create date-based indices for all registered search handlers';
        $this->signature = 'search:index:create';

        $this->elasticClient = $elasticClient;

        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function handleSearchHandler(HandlerInterface $searchHandler): void
    {
        $index = $searchHandler->getIndexName();

        $this->info(\sprintf('Processing search handler \'%s\'', \get_class($searchHandler)));

        $dateStamp = (new DateTime())->format('Ymdhis');

        $newIndex = \sprintf('%s_%s', $index, $dateStamp);
        $tempAlias = \sprintf('%s_new', $index);

        $this->elasticClient->createIndex($newIndex);
        $this->info(\sprintf('> Created new index \'%s\'', $newIndex));

        // Remove _new alias if already exists
        if ($this->elasticClient->isAlias($tempAlias) === true) {
            $this->elasticClient->deleteAlias($index, $tempAlias);
            $this->info(\sprintf('> Removed old alias \'%s\'', $tempAlias));
        }

        $this->elasticClient->createAlias($index, $tempAlias);
        $this->info(\sprintf('> Created alias \'%s\'', $tempAlias));
    }
}
