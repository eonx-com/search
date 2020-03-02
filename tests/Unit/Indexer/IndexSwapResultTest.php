<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Indexer;

use LoyaltyCorp\Search\Indexer\IndexSwapResult;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Indexer\IndexSwapResult
 */
final class IndexSwapResultTest extends UnitTestCase
{
    /**
     * Ensure array structure is generated with nested actions for table output.
     *
     * @return void
     */
    public function testTableFormatting(): void
    {
        $table = new IndexSwapResult(
            [
                [
                    'alias' => 'greatIndex',
                    'index' => 'greatIndex_20190101',
                ],
            ],
            ['greatIndex_new'],
            ['bigIndex_20190101']
        );

        $expected = [
            ['Alias', 'Index', 'Action'], // Headers
            [
                ['greatIndex', 'greatIndex_20190101', 'Point alias to index'], // Rows of actions
                ['greatIndex_new', '', 'Remove alias'],
                [
                    '',
                    'bigIndex_20190101',
                    'Skip swapping root alias',
                ],
            ],
        ];

        self::assertSame($expected, $table->getTableData());
    }
}
