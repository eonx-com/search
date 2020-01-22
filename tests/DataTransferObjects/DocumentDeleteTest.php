<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\DocumentDelete
 */
final class DocumentDeleteTest extends TestCase
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
