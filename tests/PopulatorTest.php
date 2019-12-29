<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\Access\AnonymousAccessPopulator;
use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Populator;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoDocumentBodyStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoSearchIdStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;

/**
 * @covers \LoyaltyCorp\Search\Populator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) required to test
 */
final class PopulatorTest extends TestCase
{
    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testBigBatch(): void
    {
        $objects = [
            new SearchableStub('search1'),
            new SearchableStub('search2'),
        ];

        $expected = [
            [
                new IndexAction(
                    new DocumentUpdate(
                        'search1',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                ),
            ],
            [
                new IndexAction(
                    new DocumentUpdate(
                        'search2',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                )
            ],
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $client->getUpdatedIndices());
    }

    /**
     * Tests when the handler has an empty iterable.
     *
     * @return void
     */
    public function testEmptyIterable(): void
    {
        $objects = [];

        $expected = [];

        $handler = new TransformableSearchHandlerStub($objects);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $client->getUpdatedIndices());
    }

    /**
     * Tests the handler returning an iterable with less than batch size.
     *
     * @return void
     */
    public function testExactBatch(): void
    {
        $objects = [
            new SearchableStub('search1'),
            new SearchableStub('search2'),
        ];

        $expected = [
            [
                new IndexAction(
                    new DocumentUpdate(
                        'search1',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                ),
                new IndexAction(
                    new DocumentUpdate(
                        'search2',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                ),
            ],
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getUpdatedIndices());
    }

    /**
     * Tests the handler returning an iterable with less than batch size.
     *
     * @return void
     */
    public function testSkippedObjects(): void
    {
        $objects = [
            new NotSearchableStub(),
            new NoDocumentBodyStub(),
            new NoSearchIdStub(),
        ];

        $expected = [
            [
                new IndexAction(
                    new DocumentDelete('nobody'),
                    'valid_suffix'
                ),
            ]
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getUpdatedIndices());
    }

    /**
     * Tests the handler returning an iterable with extras over the batch size.
     *
     * @return void
     */
    public function testOddBatch(): void
    {
        $objects = [
            new SearchableStub('search1'),
            new SearchableStub('search2'),
            new SearchableStub('search3'),
        ];

        $expected = [
            [
                new IndexAction(
                    new DocumentUpdate(
                        'search1',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                ),
                new IndexAction(
                    new DocumentUpdate(
                        'search2',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                )
            ],
            [
                new IndexAction(
                    new DocumentUpdate(
                        'search3',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                ),
            ],
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getUpdatedIndices());
    }

    /**
     * Tests the handler returning an iterable with less than batch size.
     *
     * @return void
     */
    public function testSmallBatch(): void
    {
        $objects = [
            new SearchableStub('search1'),
        ];

        $expected = [
            [
                new IndexAction(
                    new DocumentUpdate(
                        'search1',
                        ['search' => 'body', '_access_tokens' => ['anonymous']]
                    ),
                    'valid_suffix'
                ),
            ],
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getUpdatedIndices());
    }

    /**
     * Returns the populator under test.
     *
     * @param \LoyaltyCorp\Search\Interfaces\ClientInterface|null $client
     * @param \LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface|null $nameTransformer
     *
     * @return \LoyaltyCorp\Search\Populator
     */
    private function getPopulator(
        ?ClientInterface $client = null,
        ?IndexNameTransformerInterface $nameTransformer = null
    ): Populator {
        return new Populator(
            new AnonymousAccessPopulator(),
            $client ?? new ClientStub(),
            $nameTransformer ?? new DefaultIndexNameTransformer()
        );
    }
}
