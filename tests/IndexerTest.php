<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use EoneoPay\Utils\DateTime;
use LoyaltyCorp\Search\Exceptions\AliasNotFoundException;
use LoyaltyCorp\Search\Indexer;
use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;

/**
 * @covers \LoyaltyCorp\Search\Indexer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases.
 */
final class IndexerTest extends TestCase
{
    /**
     * Ensure the search handler index + '_new' index gets created.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testAliasGetsCreated(): void
    {
        $elasticClient = new ClientStub();
        $indexer = $this->createInstance($elasticClient);

        $expectedAlias = 'valid_new';
        $expectedIndexCreate = [
            'name' => 'valid_20190102030405',
            'mappings' => [
                'doc' => [
                    'properties' => [
                        'createdAt' => [
                            'type' => 'date',
                        ],
                    ],
                ],
            ],
            'settings' => [
                'number_of_replicas' => 1,
                'number_of_shards' => 1,
            ],
        ];

        $now = new DateTime('2019-01-02T03:04:05');
        $indexer->create(new TransformableSearchHandlerStub(), $now);

        self::assertSame([$expectedAlias], $elasticClient->getCreatedAliases());
        self::assertSame([$expectedIndexCreate], $elasticClient->getCreatedIndices());
    }

    /**
     * Ensure the cleaning process only disregards indices unrelated to search handlers.
     *
     * @return void
     */
    public function testCleaningHandlesMultipleHandlers(): void
    {
        $client = new ClientStub(
            null,
            null,
            [
                ['name' => 'valid-123'],
                ['name' => 'other-index-with-suffix'],
            ]
        );

        $expected = ['valid-123', 'other-index-with-suffix'];

        $indexer = $this->createInstance($client);

        $indexer->clean([
            new TransformableSearchHandlerStub(),
            new TransformableSearchHandlerStub(null, 'other-index'),
        ]);

        self::assertSame($expected, $client->getDeletedIndices());
    }

    /**
     * Ensure the cleaning process only disregards indices unrelated to search handlers.
     *
     * @return void
     */
    public function testCleaningIndicesDoesNotRemoveUnrelatedIndices(): void
    {
        $client = new ClientStub(
            null,
            null,
            // unrelated-index and irrelevant-index should not be touched, because they are unrelated to search handlers
            [['name' => 'unrelated-index'], ['name' => 'irrelevant-index'], ['name' => 'valid-123']]
        );
        $indexer = $this->createInstance($client);
        $expected = ['valid-123'];

        $indexer->clean([new TransformableSearchHandlerStub()]);

        self::assertSame($expected, $client->getDeletedIndices());
    }

    /**
     * Ensure the cleaning process does not execute if dry run is true.
     *
     * @return void
     */
    public function testCleaningIndicesRespectsDryOption(): void
    {
        $client = new ClientStub(
            null,
            null,
            [['name' => 'unrelated-index'], ['name' => 'irrelevant-index'], ['name' => 'valid-123']]
        );
        $indexer = $this->createInstance($client);

        $indexer->clean([new TransformableSearchHandlerStub()], true);

        self::assertSame([], $client->getDeletedIndices());
    }

    /**
     * Ensure the cleaning process only cares about indices that are related to search handlers.
     *
     * @return void
     */
    public function testCleaningIndicesRespectsIndicesFromAliases(): void
    {
        $client = new ClientStub(
            null,
            null,
            [['name' => 'unrelated-index'], ['name' => 'valid-unused']],
            [['index' => 'valid', 'name' => 'anything']]
        );
        $indexer = $this->createInstance($client);
        $expected = ['valid-unused'];

        $indexer->clean([new TransformableSearchHandlerStub()]);

        self::assertSame($expected, $client->getDeletedIndices());
    }

    /**
     * Ensure that skipping an index to be swapped happens when appropriate.
     *
     * @return void
     */
    public function testIndexSwapWithNoSwap(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']],
            [0, 10] // index_new alias has 0 documents, root alias has 10
        );
        $indexer = $this->createInstance($elasticClient);

        $result = $indexer->indexSwap(
            [new TransformableSearchHandlerStub()],
            true
        );

        self::assertEquals(new IndexSwapResult([], ['valid_new'], ['valid_201900502']), $result);
    }

    /**
     * Ensure dry running the index swap method does not call anything from elastic client.
     *
     * @return void
     */
    public function testIndexSwapperDryRun(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']]
        );
        $indexer = $this->createInstance($elasticClient);
        $expected = ['valid_new'];

        $indexer->indexSwap([new TransformableSearchHandlerStub()]);

        self::assertSame($expected, $elasticClient->getDeletedAliases());
    }

    /**
     * Ensure the swap method removes the _new alias.
     *
     * @return void
     */
    public function testIndexSwapperRemovesNewAlias(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']]
        );
        $indexer = $this->createInstance($elasticClient);

        $indexer->indexSwap([new TransformableSearchHandlerStub()], true);

        self::assertSame([], $elasticClient->getSwappedAliases());
        self::assertSame([], $elasticClient->getDeletedAliases());
    }

    /**
     * Ensure the index<->alias swap does indeed happen.
     *
     * @return void
     */
    public function testIndexSwapperSwapsAlias(): void
    {
        $elasticClient = new ClientStub(
            true,
            null,
            null,
            [['name' => 'valid_new', 'index' => 'valid_201900502']]
        );
        $indexer = $this->createInstance($elasticClient);
        // alias => index
        $expected = ['valid' => 'valid_201900502'];

        $indexer->indexSwap([new TransformableSearchHandlerStub()]);

        self::assertSame($expected, $elasticClient->getSwappedAliases());
    }

    /**
     * Ensure the index swap method throws an Exception if no *_new alias can be found.
     *
     * @return void
     */
    public function testIndexSwapperThrowsExceptionIfAliasNotFound(): void
    {
        $this->expectException(AliasNotFoundException::class);
        $this->expectExceptionMessage('Could not find expected alias \'valid_new\'');

        $elasticClient = new ClientStub(true);
        $indexer = $this->createInstance($elasticClient);

        $indexer->indexSwap([new TransformableSearchHandlerStub()]);
    }

    /**
     * Ensure the search handler index + '_new' alias is deleted so it can be re-created, when it pre-exists.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testTemporaryAliasDeleted(): void
    {
        $elasticClient = new ClientStub(true);
        $indexer = $this->createInstance($elasticClient);
        $expected = ['valid_new'];

        $indexer->create(new TransformableSearchHandlerStub());

        // No deleted aliases because *_new was not existing already
        self::assertSame($expected, $elasticClient->getDeletedAliases());
    }

    /**
     * Instantiate an Indexer.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface|null $client
     *
     * @return \LoyaltyCorp\Search\Indexer
     */
    private function createInstance(
        ?ClientInterface $client = null
    ): Indexer {
        return new Indexer(
            $client ?? new ClientStub(),
            new DefaultIndexNameTransformer()
        );
    }
}
