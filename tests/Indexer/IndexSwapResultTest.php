<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Indexer;

use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Indexer\IndexSwapResult
 */
class IndexSwapResultTest extends TestCase
{
    /**
     * Ensure array structure is generated with nested actions for table output
     *
     * @return void
     */
    public function testTableFormatting(): void
    {
        $table = new IndexSwapResult([['alias' => 'greatIndex', 'index' => 'greatIndex_20190101']], ['greatIndex_new']);

        $expected = [
            ['Alias', 'Index', 'Action'], // Headers
            [
                ['greatIndex', 'greatIndex_20190101', 'Point alias to index'], // Rows of actions
                ['greatIndex_new', '', 'Remove alias']
            ]
        ];

        self::assertSame($expected, $table->getTableData());
    }
}
