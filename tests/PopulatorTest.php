<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Populator;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;

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
        $documentUpdate1 = new DocumentUpdate('search1', '');
        $documentUpdate2 = new DocumentUpdate('search2', '');

        $objects = [
            $documentUpdate1,
            $documentUpdate2,
        ];

        $expected = [
            [
                new IndexAction($documentUpdate1, 'valid_suffix'),
            ],
            [
                new IndexAction($documentUpdate2, 'valid_suffix'),
            ],
        ];

        $handler = new TransformableHandlerStub($objects, null, null, null, $objects);

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

        $handler = new TransformableHandlerStub($objects, null, null, null, $objects);

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
        $documentUpdate1 = new DocumentUpdate('search1', '');
        $documentUpdate2 = new DocumentUpdate('search2', '');

        $objects = [
            $documentUpdate1,
            $documentUpdate2,
        ];

        $expected = [
            [
                new IndexAction($documentUpdate1, 'valid_suffix'),
                new IndexAction($documentUpdate2, 'valid_suffix'),
            ],
        ];

        $handler = new TransformableHandlerStub($objects, null, null, null, $objects);

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
        $documentUpdate1 = new DocumentUpdate('search1', '');
        $documentUpdate2 = new DocumentUpdate('search2', '');
        $documentUpdate3 = new DocumentUpdate('search3', '');

        $objects = [
            $documentUpdate1,
            $documentUpdate2,
            $documentUpdate3,
        ];

        $expected = [
            [
                new IndexAction($documentUpdate1, 'valid_suffix'),
                new IndexAction($documentUpdate2, 'valid_suffix'),
            ],
            [
                new IndexAction($documentUpdate3, 'valid_suffix'),
            ],
        ];

        $handler = new TransformableHandlerStub($objects, null, null, null, $objects);

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
        $documentUpdate1 = new DocumentUpdate('search1', '');

        $objects = [
            $documentUpdate1,
        ];

        $expected = [
            [
                new IndexAction($documentUpdate1, 'valid_suffix'),
            ],
        ];

        $handler = new TransformableHandlerStub($objects, null, null, null, $objects);

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
            $client ?? new ClientStub(),
            $nameTransformer ?? new DefaultIndexNameTransformer()
        );
    }
}
