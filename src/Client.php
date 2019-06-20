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
            throw new SearchUpdateException('An error occured while performing bulk update on backend', 0, $exception);
        }
    }

    /**
     * Create a new alias for specified index
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function createAlias(string $indexName, string $aliasName): void
    {
        try {
            $this->elastic->indices()->updateAliases(
                ['body' => ['actions' => [['add' => ['index' => $indexName, 'alias' => $aliasName]]]]]
            );
        } catch (Exception $exception) {
            // @todo fix exception
            throw new SearchDeleteException('Unable to add alias', 0, $exception);
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
                'body' => \array_filter([
                    'settings' => $settings,
                    'mappings' => $mappings
                ])
            ]);
        } catch (Exception $exception) {
            // @todo fix exception type
            throw new SearchDeleteException('', 0, $exception);
        }
    }

    /**
     * Delete an existing alias
     *
     * @param string $indexName
     * @param string $aliasName
     *
     * @return void
     */
    public function deleteAlias(string $indexName, string $aliasName): void
    {
        try {
            $this->elastic->indices()->updateAliases(
                ['body' => ['actions' => [['remove' => ['index' => $indexName, 'alias' => $aliasName]]]]]
            );
        } catch (Exception $exception) {
            // @todo fix exception type
            throw new SearchDeleteException('', 0, $exception);
        }
    }

    /**
     * Delete an existing index
     *
     * @param string $name
     *
     * @return void
     */
    public function deleteIndex(string $name): void
    {
        try {
            $this->elastic->indices()->delete([
                'index' => $name
            ]);
        } catch (Exception $exception) {
            // @todo fix exception type
            throw new SearchDeleteException('', 0, $exception);
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
            // @todo fix exception type
            throw new SearchDeleteException('', 0, $exception);
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
            // @todo fix exception type
            throw new SearchDeleteException('', 0, $exception);
        }
    }

    /**
     * List all existing indexes
     *
     * @param bool|null $includeAliases
     *
     * @return mixed[]
     */
    public function listIndices(?bool $includeAliases = null): array
    {
        try {
            $indices = [];
            foreach ($this->elastic->cat()->indices() as $index) {
                // Key as index name just for local ease of mapping
                $indices[$index['index']] = [
                    'name' => $index['index']
                ];
            }

            if (($includeAliases ?? false) === false) {
                // Reset keys to numerical indexes & remove aliases key
                return \array_values(
                    \array_filter($indices)
                );
            }

            foreach ($this->elastic->cat()->aliases() as $alias) {
                if (\array_key_exists('aliases', $indices[$alias['index']]) === false) {
                    $indices[$alias['index']]['aliases'] = [];
                }

                $indices[$alias['index']]['aliases'][] = $alias['alias'];
            }

            return \array_values($indices);
        } catch (Exception $exception) {
            // @todo fix exception type
            throw new SearchDeleteException('', 0, $exception);
        }
    }
}
