<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use LoyaltyCorp\Search\Helpers\IndexHelper;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\NonDoctrineHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\ProviderAwareSearchHandlerStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Helpers\IndexHelper
 */
class IndexHelperTest extends TestCase
{
    /**
     * Test that get index name will return expected name when search handler
     * is not provider aware.
     *
     * @return void
     */
    public function testGetIndexName(): void
    {
        $searchHandler = new NonDoctrineHandlerStub();
        $indexHelper = $this->getIndexHelper();
        $expectedIndexName = 'non-doctrine-index';

        $actualIndexName = $indexHelper->getIndexName($searchHandler);

        self::assertSame($expectedIndexName, $actualIndexName);
    }

    /**
     * Test that get index name will return expected name when search handler
     * is provider aware.
     *
     * @return void
     */
    public function testGetIndexNameWhenProviderAware(): void
    {
        $searchHandler = new ProviderAwareSearchHandlerStub(
            'acmeIncId',
            'provider-index'
        );
        $indexHelper = $this->getIndexHelper();
        $expectedIndexName = 'provider-index_acmeIncId';

        $actualIndexName = $indexHelper->getIndexName($searchHandler);

        self::assertSame($expectedIndexName, $actualIndexName);
    }

    /**
     * Get index helper.
     *
     * @return \LoyaltyCorp\Search\Helpers\IndexHelper
     */
    private function getIndexHelper(): IndexHelper
    {
        return new IndexHelper();
    }
}
