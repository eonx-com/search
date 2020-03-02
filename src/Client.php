<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use Elasticsearch\Client as BaseClient;
use Exception;
use LoyaltyCorp\Search\DataTransferObjects\ClusterHealth;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\Exceptions\SearchCheckerException;
use LoyaltyCorp\Search\Exceptions\SearchDeleteException;
use LoyaltyCorp\Search\Exceptions\SearchUpdateException;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) Decorated ES client class
 */
final class Client implements ClientInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface
     */
    private $bulkResponseHelper;

    /**
     * @var \Elasticsearch\Client
     */
    private $elastic;

    /**
     * Create elastic search instance.
     *
     * @param \Elasticsearch\Client $elastic
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface $bulkResponseHelper
     */
    public function __construct(BaseClient $elastic, ClientBulkResponseHelperInterface $bulkResponseHelper)
    {
        $this->bulkResponseHelper = $bulkResponseHelper;
        $this->elastic = $elastic;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\BulkFailureException If there is at least one record with an error
     * @throws \LoyaltyCorp\Search\Exceptions\SearchUpdateException If backend client throws an exception via bulk()
     */
    public function bulk(array $actions): void
    {
        $bulk = [];

        foreach ($actions as $action) {
            // The _type parameter is being deprecated, and in Elasticsearch 6.0+ means
            // nothing, but still must be provided. As a standard, anything using this
            // library will need to define the type as "doc" in any schema mappings until
            // we reach Elasticsearch 7.0.
            //
            // See: https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html
            $documentAction = $action->getDocumentAction();

            $bulk[] = [
                $documentAction::getAction() => [
                    '_index' => $action->getIndex(),
                    '_type' => 'doc',
                    '_id' => $documentAction->getDocumentId(),
                ],
            ];

            // When updating a document, the bulk action must be followed by the document body.
            if ($documentAction instanceof DocumentUpdate === true) {
                $extra = $documentAction->getExtra();

                // If the DocumentUpdate has extra keys, merge the document into the
                // extra array - so we dont overwrite anything the SearchHandler output.
                $document = \count($extra) > 0
                    ? \array_merge($extra, $documentAction->getDocument())
                    : $documentAction->getDocument();

                $bulk[] = $document;
            }
        }

        try {
            $responses = $this->elastic->bulk(['body' => $bulk]);
        } catch (Exception $exception) {
            throw new SearchUpdateException('An error occurred while performing bulk update on backend', 0, $exception);
        }

        // Check responses for error
        $this->bulkResponseHelper->checkBulkResponsesForErrors($responses);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\SearchCheckerException
     */
    public function count(string $index): int
    {
        try {
            $count = $this->elastic->cat()->count(['index' => $index]);

            // _cat/_count supports counting many indices, the response is an array of objects but we only need 0 index
            return (int)$count[0]['count'];
        } catch (Exception $exception) {
            throw new SearchCheckerException('Unable to count number of documents within index', 0, $exception);
        }
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
                'body' => \array_filter(\compact('settings', 'mappings')),
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
                'index' => $name,
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
                    'index' => $alias['index'],
                ];
            }

            return \array_values($aliases);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error occurred obtaining a list of aliases', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHealth(): ClusterHealth
    {
        try {
            $cluster = $this->elastic->cluster();
            $result = $cluster->health();

            return new ClusterHealth($result);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error occurred checking the cluster health', 0, $exception);
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
                    'name' => $index['index'],
                ];
            }

            // Reset keys to numerical indexes & remove aliases key
            return \array_values($indices);
        } catch (Exception $exception) {
            throw new SearchCheckerException('An error occurred obtaining a list of indices', 0, $exception);
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
                        'actions' => $actions,
                    ],
                ]
            );
        } catch (Exception $exception) {
            throw new SearchUpdateException('Unable to atomically swap alias', 0, $exception);
        }
    }
}
