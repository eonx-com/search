<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;

final class HandlerStub implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function getHandledClass(): string
    {
        return NotSearchableStub::class;
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
        return \method_exists($object, 'getSearchId') ? $object->getSearchId() : null;
    }

    /**
     * @inheritdoc
     */
    public function transform($object): ?array
    {
        return \method_exists($object, 'toArray') ? $object->toArray() : null;
    }
}
