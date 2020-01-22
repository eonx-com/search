<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\DataTransferObjects\ClusterHealth;
use LoyaltyCorp\Search\Interfaces\ClientInterface;

/**
 * @coversNothing
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases
 */
final class ClientStub extends BaseStub implements ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function bulk(array $actions): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }

    /**
     * {@inheritdoc}
     */
    public function count(string $index): int
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function createAlias(string $indexName, string $aliasName): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(string $name, ?array $mappings = null, ?array $settings = null): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAlias(array $aliases): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex(string $name): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(?string $name = null): array
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * Returns calls to bulk().
     *
     * @return mixed[]
     */
    public function getBulkCalls(): array
    {
        return $this->getCalls('bulk');
    }

    /**
     * {@inheritdoc}
     */
    public function getHealth(): ClusterHealth
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndices(?string $name = null): array
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function isAlias(string $name): bool
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function isIndex(string $name): bool
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function moveAlias(array $aliases): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }
}
