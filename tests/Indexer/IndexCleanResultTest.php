<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Indexer;

use LoyaltyCorp\Search\Indexer\IndexCleanResult;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Indexer\IndexCleanResult
 */
final class IndexCleanResultTest extends TestCase
{
    /**
     * Ensure supplied indices match the getter method return.
     *
     * @return void
     */
    public function testIndicesPassthrough(): void
    {
        $indices = ['index_new', 'second_index_new'];
        $result = new IndexCleanResult($indices);

        self::assertSame($indices, $result->getIndicesCleaned());
    }
}
