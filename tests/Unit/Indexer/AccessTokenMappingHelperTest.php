<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Indexer;

use LoyaltyCorp\Search\Exceptions\InvalidMappingException;
use LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\CustomAccessHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\InvalidMappingHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\TransformableHandlerStub;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Indexer\AccessTokenMappingHelper
 */
final class AccessTokenMappingHelperTest extends UnitTestCase
{
    /**
     * Tests what happens when the AccessTokenMappingHelper is passed a handler
     * that implements CustomAccessHandlerInterface.
     *
     * @return void
     */
    public function testCustomAccess(): void
    {
        $helper = new AccessTokenMappingHelper();

        $handler = new CustomAccessHandlerStub();

        $expectedMappings = [
            'doc' => [
                'dynamic' => 'strict',
                'properties' => [
                    'createdAt' => [
                        'type' => 'date',
                    ],
                ],
            ],
        ];

        $mappings = $helper->buildIndexMappings($handler);

        self::assertSame($expectedMappings, $mappings);
    }

    /**
     * Tests that the helper adds _access_tokens key to the mapping.
     *
     * @return void
     */
    public function testAccessMappings(): void
    {
        $helper = new AccessTokenMappingHelper();

        $handler = new TransformableHandlerStub();

        $expectedMappings = [
            'doc' => [
                'dynamic' => 'strict',
                'properties' => [
                    'createdAt' => [
                        'type' => 'date',
                    ],
                    '_access_tokens' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
        ];

        $mappings = $helper->buildIndexMappings($handler);

        self::assertSame($expectedMappings, $mappings);
    }

    /**
     * Tests when the mapping the handler returns isnt valid based on the
     * narrow assumptions that the helper makes (single root key).
     *
     * @return void
     */
    public function testAccessMappingsInvalid(): void
    {
        $helper = new AccessTokenMappingHelper();

        $handler = new InvalidMappingHandlerStub();

        $this->expectException(InvalidMappingException::class);
        $this->expectExceptionMessage(
            'Unknown mapping format. Mapping must return a multidimensional array with a single key.'
        );

        $helper->buildIndexMappings($handler);
    }
}
