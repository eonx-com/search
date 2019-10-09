<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Transformers;

use LoyaltyCorp\Search\Transformers\DefaultIndexTransformer;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Transformers\DefaultIndexTransformer
 */
class DefaultIndexTransformerTest extends TestCase
{
    /**
     * Test that transforming index names will return expected array of index
     * names.
     *
     * @return void
     */
    public function testTransformIndexNames(): void
    {
        $handler = new NonDoctrineHandlerStub();
        $transformer = $this->getTransformer();

        $expectedIndexNames = [
            'non-doctrine-index'
        ];

        $actualIndexNames = $transformer->transformIndexNames($handler);

        self::assertSame($expectedIndexNames, $actualIndexNames);
    }

    /**
     * Get default index transformer.
     *
     * @return \LoyaltyCorp\Search\Transformers\DefaultIndexTransformer
     */
    private function getTransformer(): DefaultIndexTransformer
    {
        return new DefaultIndexTransformer();
    }
}
