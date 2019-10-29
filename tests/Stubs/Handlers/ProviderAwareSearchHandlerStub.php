<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers;

use LoyaltyCorp\Search\Interfaces\ProviderAwareInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\SearchableStub;

/**
 * @coversNothing
 */
final class ProviderAwareSearchHandlerStub implements TransformableSearchHandlerInterface, ProviderAwareInterface
{
    /**
     * Index name.
     *
     * @var string|null
     */
    private $indexName;

    /**
     * Provider id.
     *
     * @var string|null
     */
    private $providerId;

    /**
     * ProviderAwareSearchHandlerStub constructor.
     *
     * @param string|null $providerId
     * @param string|null $indexName
     */
    public function __construct(
        ?string $providerId = null,
        ?string $indexName = null
    ) {
        $this->indexName = $indexName;
        $this->providerId = $providerId;
    }

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
    public function getFillIterable(): iterable
    {
        return [];
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
        return $this->indexName ?? 'provider-aware-index';
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderId(object $object): string
    {
        return $this->providerId ?? 'providerId';
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
