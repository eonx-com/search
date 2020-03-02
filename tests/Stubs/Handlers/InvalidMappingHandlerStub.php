<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

/**
 * @coversNothing
 *
 * @template T
 *
 * @extends TransformableHandlerStub<T>
 */
final class InvalidMappingHandlerStub extends TransformableHandlerStub
{
    /**
     * {@inheritdoc}
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public static function getMappings(): array
    {
        return ['doc' => [], 'not-doc' => []];
    }
}
