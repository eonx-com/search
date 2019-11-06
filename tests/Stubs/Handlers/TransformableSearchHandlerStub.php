<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;

/**
 * @coversNothing
 */
final class TransformableSearchHandlerStub implements TransformableSearchHandlerInterface
{
    /**
     * @var mixed[]|null
     */
    private $objects;

    /**
     * @var string
     */
    private $indexName;

    /**
     * Constructor.
     *
     * @param mixed[]|null $objects
     * @param string|null $indexName
     */
    public function __construct(?array $objects = null, ?string $indexName = null)
    {
        $this->indexName = $indexName ?? 'valid';
        $this->objects = $objects;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return [
            'doc' => [
                'dynamic' => 'strict',
                'properties' => [
                    'createdAt' => [
                        'type' => 'date',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSettings(): array
    {
        return [
            'number_of_replicas' => 1,
            'number_of_shards' => 1,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFillIterable(): iterable
    {
        return $this->objects ?? [];
    }

    /**
     * @inheritdoc
     */
    public function getHandledClasses(): array
    {
        return [SearchableStub::class];
    }

    /**
     * @inheritdoc
     */
    public function getIndexName(): string
    {
        return $this->indexName;
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
    public function transform($object = null): ?array
    {
        return \method_exists($object, 'toArray') ? $object->toArray() : null;
    }
}
