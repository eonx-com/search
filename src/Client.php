<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use Elasticsearch\Client as BaseClient;
use Exception;
use LoyaltyCorp\Search\Exceptions\SearchDeleteException;
use LoyaltyCorp\Search\Exceptions\SearchUpdateException;
use LoyaltyCorp\Search\Interfaces\ClientInterface;

final class Client implements ClientInterface
{
    /**
     * @var \Elasticsearch\Client
     */
    private $elastic;

    /**
     * Create elastic search instance
     *
     * @param \Elasticsearch\Client $elastic
     */
    public function __construct(BaseClient $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * @inheritdoc
     *
     * @throws \LoyaltyCorp\Search\Exceptions\SearchDeleteException If backend client throws an exception via bulk()
     */
    public function bulkDelete(array $searchIds): void
    {
        $bulk = [];

        foreach ($searchIds as $index => $indexIds) {
            // Skip non-interable items
            if (\is_iterable($indexIds) === false) {
                continue;
            }

            /**
             * @var mixed[] $indexIds
             *
             * @see https://youtrack.jetbrains.com/issue/WI-37859 typehint required until PhpStorm recognises === check
             */
            foreach ($indexIds as $indexId) {
                $bulk[] = ['delete' => ['_index' => $index, '_id' => $indexId]];
            }
        }

        try {
            $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchDeleteException('An error occured while performing bulk delete on backend', 0, $exception);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \LoyaltyCorp\Search\Exceptions\SearchUpdateException If backend client throws an exception via bulk()
     */
    public function bulkUpdate(string $index, array $documents): void
    {
        $bulk = [];

        foreach ($documents as $documentId => $document) {
            $bulk[] = ['index' => ['_index' => $index, '_id' => $documentId]];
            $bulk[] = $document;
        }

        try {
            $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchUpdateException('An error occured while performing bulk update on backend', 0, $exception);
        }
    }
}
