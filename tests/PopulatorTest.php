<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\Populator;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;

/**
 * @covers \LoyaltyCorp\Search\Populator
 */
class PopulatorTest extends TestCase
{
    /**
     * Tests the handler returning an iterable with more than batch size.
     *
     * @return void
     */
    public function testBigBatch(): void
    {
        $objects = [
            ['object' => 'purple'],
            ['object' => 'green']
        ];

        $expected = [
            [
                ['object' => 'purple']
            ],
            [
                ['object' => 'green']
            ]
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $populator = new Populator();

        $result = $populator->getBatchedIterable($handler, 1);

        static::assertSame($expected, \iterator_to_array($result));
    }

    /**
     * Tests the handler returning an empty iterable.
     *
     * @return void
     */
    public function testEmptyIterable(): void
    {
        $objects = [];
        $handler = new TransformableSearchHandlerStub($objects);

        $populator = new Populator();

        $result = $populator->getBatchedIterable($handler, 1);

        static::assertSame([], \iterator_to_array($result));
    }

    /**
     * Tests the handler returning an iterable with less than batch size.
     *
     * @return void
     */
    public function testExactBatch(): void
    {
        $objects = [
            ['object' => 'purple'],
            ['object' => 'green']
        ];

        $expected = [
            [
                ['object' => 'purple'],
                ['object' => 'green']
            ]
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $populator = new Populator();

        $result = $populator->getBatchedIterable($handler, 2);

        static::assertSame($expected, \iterator_to_array($result));
    }

    /**
     * Tests the handler returning an iterable with extras over the batch size.
     *
     * @return void
     */
    public function testOddBatch(): void
    {
        $objects = [
            ['object' => 'purple'],
            ['object' => 'green'],
            ['object' => 'orange']
        ];

        $expected = [
            [
                ['object' => 'purple'],
                ['object' => 'green']
            ],
            [
                ['object' => 'orange']
            ]
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $populator = new Populator();

        $result = $populator->getBatchedIterable($handler, 2);

        static::assertSame($expected, \iterator_to_array($result));
    }

    /**
     * Tests the handler returning an iterable with less than batch size.
     *
     * @return void
     */
    public function testSmallBatch(): void
    {
        $objects = [
            ['object' => 'purple']
        ];

        $expected = [
            [
                ['object' => 'purple']
            ]
        ];

        $handler = new TransformableSearchHandlerStub($objects);

        $populator = new Populator();

        $result = $populator->getBatchedIterable($handler, 2);

        static::assertSame($expected, \iterator_to_array($result));
    }
}
