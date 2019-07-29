<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\HandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;

/**
 * @coversNothing
 */
final class NotSearchableHandlerStub implements HandlerInterface
{
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
    public function transform($object): ?array
    {
        return \method_exists($object, 'toArray') ? $object->toArray() : null;
    }
}
