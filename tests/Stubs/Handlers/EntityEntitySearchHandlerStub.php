<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\EntitySearchHandlerHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;

class EntityEntitySearchHandlerStub implements EntitySearchHandlerHandlerInterface
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
     * {@inheritdoc}
     */
    public function getHandledClasses(): array
    {
        return [EntityStub::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return 'entity_stub';
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchId(object $object)
    {
        return \method_exists($object, 'getSearchId') ? $object->getSearchId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($object = null): ?array
    {
        return [];
    }
}
