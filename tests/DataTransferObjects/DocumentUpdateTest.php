<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate
 */
final class DocumentUpdateTest extends TestCase
{
    /**
     * Tests DTO methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $update = new DocumentUpdate('index', 'id', 'document');

        self::assertSame('index', $update->getIndex());
        self::assertSame('id', $update->getDocumentId());
        self::assertSame('document', $update->getDocument());
    }
}
