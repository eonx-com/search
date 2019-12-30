<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

/**
 * @coversNothing
 */
final class InvalidMappingHandlerStub extends TransformableHandlerStub
{
    /**
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return ['doc' => [], 'not-doc' => []];
    }
}
