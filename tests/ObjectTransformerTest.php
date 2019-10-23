<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\Transformers\ObjectTransformer;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoDocumentBodyStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoSearchIdStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;

/**
 * @covers \LoyaltyCorp\Search\Transformers\ObjectTransformer
 */
class ObjectTransformerTest extends TestCase
{
    /**
     * Tests the transformer correctly transforms a document.
     *
     * @return void
     */
    public function testTransform(): void
    {
        $handler = new TransformableSearchHandlerStub();
        $transformer = new ObjectTransformer();

        $objects = [
            new NoSearchIdStub(),
            new NoDocumentBodyStub(),
            new SearchableStub()
        ];

        $expected = [
            'searchable' => [
                'search' => 'body'
            ]
        ];

        $result = $transformer->bulkTransform($handler, $objects);

        static::assertSame($expected, \iterator_to_array($result));
    }
}
