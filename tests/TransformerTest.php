<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use LoyaltyCorp\Search\Transformer;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoDocumentBodyStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NoSearchIdStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableSearchHandlerStub;

class TransformerTest extends TestCase
{
    /**
     * Tests the transformer correctly transforms a document.
     *
     * @return void
     */
    public function testTransform(): void
    {
        $handler = new TransformableSearchHandlerStub();
        $transformer = new Transformer();

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
