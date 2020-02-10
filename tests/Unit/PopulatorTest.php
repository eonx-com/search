<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit;

use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use LoyaltyCorp\Search\DataTransferObjects\IndexAction;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use LoyaltyCorp\Search\Populator;
use LoyaltyCorp\Search\Transformers\DefaultIndexNameTransformer;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\LegacyClientStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Populator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) required to test
 */
final class PopulatorTest extends UnitTestCase
{
    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testBigBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2];

        $documentUpdate1 = new DocumentUpdate('search1', 'document');
        $documentUpdate2 = new DocumentUpdate('search2', 'document');

        $expected = [
            [
                'actions' => [new IndexAction($documentUpdate1, 'valid_suffix')],
            ],
            [
                'actions' => [new IndexAction($documentUpdate2, 'valid_suffix')],
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [$objects],
            'transform' => [
                $documentUpdate1,
                $documentUpdate2,
            ],
        ]);

        $expectedPrefillCalls = [
            [
                'changes' => [new ObjectForUpdate(stdClass::class, ['id' => 'search1'], null)],
            ],
            [
                'changes' => [new ObjectForUpdate(stdClass::class, ['id' => 'search2'], null)],
            ],
        ];

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $client->getCalls('bulk'));
        self::assertEquals($expectedPrefillCalls, $handler->getCalls('prefill'));
    }

    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testSkippingUpdates(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2];

        $expected = [];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [$objects],
            'transform' => [
                null,
                null,
            ],
        ]);

        $expectedPrefillCalls = [
            [
                'changes' => [new ObjectForUpdate(stdClass::class, ['id' => 'search1'], null)],
            ],
            [
                'changes' => [new ObjectForUpdate(stdClass::class, ['id' => 'search2'], null)],
            ],
        ];

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $client->getCalls('bulk'));
        self::assertEquals($expectedPrefillCalls, $handler->getCalls('prefill'));
    }

    /**
     * Tests the populator when the iterator is empty.
     *
     * @return void
     */
    public function testEmptyIterator(): void
    {
        $expected = [];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [[]],
        ]);

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 1);

        self::assertEquals($expected, $client->getCalls('bulk'));
        self::assertEquals([], $handler->getCalls('prefill'));
    }

    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testExactBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2];

        $documentUpdate1 = new DocumentUpdate('search1', 'document');
        $documentUpdate2 = new DocumentUpdate('search2', 'document');

        $expected = [
            [
                'actions' => [
                    new IndexAction($documentUpdate1, 'valid_suffix'),
                    new IndexAction($documentUpdate2, 'valid_suffix'),
                ],
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [$objects],
            'transform' => [
                $documentUpdate1,
                $documentUpdate2,
            ],
        ]);

        $expectedPrefillCalls = [
            [
                'changes' => [
                    new ObjectForUpdate(stdClass::class, ['id' => 'search1'], null),
                    new ObjectForUpdate(stdClass::class, ['id' => 'search2'], null),
                ],
            ],
        ];

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getCalls('bulk'));
        self::assertEquals($expectedPrefillCalls, $handler->getCalls('prefill'));
    }

    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testOddBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);
        $update2 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);
        $update3 = new ObjectForUpdate(stdClass::class, ['id' => 'search2']);

        $objects = [$update1, $update2, $update3];

        $documentUpdate1 = new DocumentUpdate('search1', 'document');
        $documentUpdate2 = new DocumentUpdate('search2', 'document');
        $documentUpdate3 = new DocumentUpdate('search3', 'document');

        $expected = [
            [
                'actions' => [
                    new IndexAction($documentUpdate1, 'valid_suffix'),
                    new IndexAction($documentUpdate2, 'valid_suffix'),
                ],
            ],
            [
                'actions' => [
                    new IndexAction($documentUpdate3, 'valid_suffix'),
                ],
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [$objects],
            'transform' => [
                $documentUpdate1,
                $documentUpdate2,
                $documentUpdate3,
            ],
        ]);

        $expectedPrefillCalls = [
            [
                'changes' => [
                    new ObjectForUpdate(stdClass::class, ['id' => 'search1'], null),
                    new ObjectForUpdate(stdClass::class, ['id' => 'search2'], null),
                ],
            ],
            [
                'changes' => [
                    new ObjectForUpdate(stdClass::class, ['id' => 'search2'], null),
                ],
            ],
        ];

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getCalls('bulk'));
        self::assertEquals($expectedPrefillCalls, $handler->getCalls('prefill'));
    }

    /**
     * Tests the populator when there is more than one batch.
     *
     * @return void
     */
    public function testSmallBatch(): void
    {
        $update1 = new ObjectForUpdate(stdClass::class, ['id' => 'search1']);

        $objects = [$update1];

        $documentUpdate1 = new DocumentUpdate('search1', 'document');

        $expected = [
            [
                'actions' => [
                    new IndexAction($documentUpdate1, 'valid_suffix'),
                ],
            ],
        ];

        $handler = new TransformableHandlerStub('valid', [
            'getFillIterable' => [$objects],
            'transform' => [
                $documentUpdate1,
            ],
        ]);

        $expectedPrefillCalls = [
            [
                'changes' => [
                    new ObjectForUpdate(stdClass::class, ['id' => 'search1'], null),
                ],
            ],
        ];

        $client = new ClientStub();
        $populator = $this->getPopulator($client);

        $populator->populate($handler, '_suffix', 2);

        self::assertEquals($expected, $client->getCalls('bulk'));
        self::assertEquals($expectedPrefillCalls, $handler->getCalls('prefill'));
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
            $client ?? new LegacyClientStub(),
            $nameTransformer ?? new DefaultIndexNameTransformer()
        );
    }
}
