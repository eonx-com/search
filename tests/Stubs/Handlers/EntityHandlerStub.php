<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;

class EntityHandlerStub implements HandlerInterface
{
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
    public function transform($object): ?array
    {
        return [];
    }
}
