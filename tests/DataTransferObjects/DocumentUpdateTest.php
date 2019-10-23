<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\DataTransferObjects;

use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate
 */
class DocumentUpdateTest extends TestCase
{
    /**
     * Tests DTO methods.
     *
     * @return void
     */
    public function testMethods(): void
    {
        $update = new DocumentUpdate('index', 'id', 'document');

        static::assertSame('index', $update->getIndex());
        static::assertSame('id', $update->getDocumentId());
        static::assertSame('document', $update->getDocument());
    }
}
