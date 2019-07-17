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
                // The _type parameter is being deprecated, and in Elasticsearch 6.0+ means
                // nothing, but still must be provided. As a standard, anything using this
                // library will need to define the type as "doc" in any schema mappings until
                // we reach Elasticsearch 7.0.
                //
                // See: https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html

                $bulk[] = ['delete' => ['_index' => $index, '_type' => 'doc', '_id' => $indexId]];
            }
        }

        try {
            $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchDeleteException('An error occurred while performing bulk delete on backend', 0, $exception);
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
            // The _type parameter is being deprecated, and in Elasticsearch 6.0+ means
            // nothing, but still must be provided. As a standard, anything using this
            // library will need to define the type as "doc" in any schema mappings until
            // we reach Elasticsearch 7.0.
            //
            // See: https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html

            $bulk[] = ['index' => ['_index' => $index, '_type' => 'doc', '_id' => $documentId]];
            $bulk[] = $document;
        }

        try {
            $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchUpdateException('An error occurred while performing bulk update on backend', 0, $exception);
        }
    }
}
