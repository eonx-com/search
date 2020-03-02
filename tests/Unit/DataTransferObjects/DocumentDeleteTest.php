<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\DocumentDelete
 */
final class DocumentDeleteTest extends UnitTestCase
{
    /**
     * Tests DTO methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $update = new DocumentDelete('id');

        self::assertSame('delete', $update::getAction());
    }
}
