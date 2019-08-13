<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use Elasticsearch\Client as BaseClient;
use Exception;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use LoyaltyCorp\Search\Exceptions\SearchCheckerException;
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
     * @throws \LoyaltyCorp\Search\Exceptions\BulkFailureException If there is at least one record with an error
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
            $responses = $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchDeleteException('An error occurred while performing bulk delete on backend', 0, $exception);
        }

        // Check responses for error
        $this->checkBulkResponsesForErrors($responses, 'delete');
    }

    /**
     * @inheritdoc
     *
     * @throws \LoyaltyCorp\Search\Exceptions\BulkFailureException If there is at least one record with an error
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
            $responses = $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchUpdateException('An error occurred while performing bulk update on backend', 0, $exception);
        }

        // Check responses for error
        $this->checkBulkResponsesForErrors($responses, 'update');
    }

    /**
     * {@inheritdoc}
     */
    public function createAlias(string $indexName, string $aliasName): void
    {
        try {
            $this->elastic->indices()->updateAliases(
                ['body' => ['actions' => [['add' => ['index' => $indexName, 'alias' => $aliasName]]]]]
            );
        } catch (Exception $exception) {
            throw new SearchUpdateException('Unable to add alias', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(
        string $name,
        ?array $mappings = null,
        ?array $settings = null
    ): void {
        try {
            $this->elastic->indices()->create([
                'index' => $name,
                'body' => \array_filter(\compact('settings', 'mappings'))
            ]);
        } catch (Exception $exception) {
            throw new SearchUpdateException('Unable to create new index', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAlias(array $aliases): void
    {
        $actions = [];
        foreach ($aliases as $alias) {
            $actions[] = ['remove' => ['index' => '_all', 'alias' => $alias]];
        }

        try {
            $this->elastic->indices()->updateAliases(
                ['body' => ['actions' => $actions]]
            );
        } catch (Exception $exception) {
            throw new SearchDeleteException('Unable to delete alias', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(string $name): void
    {
        try {
            $this->elastic->indices()->delete([
                'index' => $name
            ]);
        } catch (Exception $exception) {
            throw new SearchDeleteException('Unable to delete index', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(?string $name = null): array
    {
        try {
            $aliases = [];

            foreach ($this->elastic->cat()->aliases(\array_filter(['name' => $name])) as $alias) {
                $aliases[$alias['alias']] = [
                    'name' => $alias['alias'],
                    'index' => $alias['index']
                ];
            }

            return \array_values($aliases);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error ocurred obtaining a list of aliases', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIndices(?string $name = null): array
    {
        try {
            $indices = [];

            foreach ($this->elastic->cat()->indices(\array_filter(['index' => $name])) as $index) {
                // Key as index name just for local ease of mapping
                $indices[$index['index']] = [
                    'name' => $index['index']
                ];
            }

            // Reset keys to numerical indexes & remove aliases key
            return \array_values($indices);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error ocurred obtaining a list of indices', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAlias(string $name): bool
    {
        try {
            return $this->elastic->indices()->existsAlias(['index' => '*', 'name' => $name]);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error occurred checking if alias exists', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isIndex(string $name): bool
    {
        try {
            return $this->elastic->indices()->exists(['index' => $name]);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error occurred checking if index exists', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function moveAlias(array $aliases): void
    {
        $actions = [];

        foreach ($aliases as $operation) {
            $actions[] = ['remove' => ['index' => '_all', 'alias' => $operation['alias']]];
            $actions[] = ['add' => ['index' => $operation['index'], 'alias' => $operation['alias']]];
        }

        try {
            $this->elastic->indices()->updateAliases(
                [
                    'body' => [
                        'actions' => $actions
                    ]
                ]
            );
        } catch (Exception $exception) {
            throw new SearchUpdateException('Unable to atomically swap alias', 0, $exception);
        }
    }

    /**
     * Check a bulk response array for errors, throw an exception if errors are found
     *
     * @param mixed $response The response from the bulk action
     * @param string $type The bulk action that was performed
     *
     * @return void
     *
     * @throws \LoyaltyCorp\Search\Exceptions\BulkFailureException If there is at least one record with an error
     */
    private function checkBulkResponsesForErrors($response, string $type): void
    {
        $responses = $this->extractBulkResponseItems($response, $type);

        $errors = [];

        /**
         * @var mixed[] $item
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 typehint required until PhpStorm recognises === check
         */
        foreach ($responses as $item) {
            // If item isn't the right type or it's not an error, skip
            if (isset($item[$type]) === false ||
                \is_array($item[$type]) === false ||
                isset($item[$type]['error']) === false ||
                $item[$type]['error'] === false) {
                continue;
            }

            // Get error
            $errors[] = $item[$type]['error'];
        }

        // If there are no errors, return
        if (\count($errors) === 0) {
            return;
        }

        // Throw bulk exception
        throw new BulkFailureException(
            $errors,
            \sprintf('At least one record returned an error during bulk %s', $type)
        );
    }

    /**
     * Extract items from bulk response
     *
     * @param mixed $response The response from the bulk action
     * @param string $type The bulk action that was performed
     *
     * @return mixed[]
     */
    private function extractBulkResponseItems($response, string $type): array
    {
        // If response is callable, wait until we have the final response
        if (($response instanceof FutureArrayInterface) === true) {
            $response = $this->unwrapPromise($response);
        }

        // If final response isn't an array, throw exception
        if (\is_array($response) === false) {
            throw new BulkFailureException([], \sprintf('Invalid response received from bulk %s', $type));
        }

        // If the top level indicates no errors or items, return nothing
        if (isset($response['errors']) === false ||
            $response['errors'] === false ||
            isset($response['items']) === false ||
            \is_array($response['items']) === false) {
            return [];
        }

        return $response['items'];
    }

    /**
     * Wait for a promise to resolve and unwrap the response
     *
     * @param \GuzzleHttp\Ring\Future\FutureArrayInterface $promise
     *
     * @return mixed
     */
    private function unwrapPromise(FutureArrayInterface $promise)
    {
        do {
            $promise = $promise->wait();
        } while (($promise instanceof FutureArrayInterface) === true);

        return $promise;
    }
}
