<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\EntitySearchHandlerHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;

/**
 * @coversNothing
 */
final class NotSearchableEntitySearchHandlerStub implements EntitySearchHandlerHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSettings(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getHandledClasses(): array
    {
        return [NotSearchableStub::class];
    }

    /**
     * @inheritdoc
     */
    public function getIndexName(): string
    {
        return 'valid';
    }

    /**
     * @inheritdoc
     */
    public function getSearchId(object $object)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function transform($object = null): ?array
    {
        return \method_exists($object, 'toArray') ? $object->toArray() : null;
    }
}
