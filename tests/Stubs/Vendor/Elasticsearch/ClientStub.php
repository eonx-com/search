<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

use Elasticsearch\Client;
use RuntimeException;
use Tests\LoyaltyCorp\Search\Stubs\CatStub;

/**
 * This stub overloads methods within the elasticsearch client as it doesn't implement an interface so
 * can't be stubbed properly, this stub will not pass anything to the actual elasticsearch client.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases
 *
 * @coversNothing
 */
final class ClientStub extends Client
{
    /**
     * @var mixed[]|null
     */
    private $bulk;

    /**
     * @var bool
     */
    private $throwException;

    /**
     * @noinspection PhpMissingParentConstructorInspection Parent is intentionally ignored as per class comment
     *
     * Create stub
     *
     * @param bool|null $throwException Whether calls should throw an exception or not
     */
    public function __construct(?bool $throwException = null)
    {
        $this->throwException = $throwException ?? false;
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection ReturnTypeCanBeDeclaredInspection
     *
     * @inheritdoc
     */
    public function bulk($params = null)
    {
        // If an exception should be thrown, throw it
        if ($this->throwException === true) {
            throw new RuntimeException('An error occured');
        }

        $this->bulk = $params;

        // This must return an array to be compatible with base client
        return [];
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection ReturnTypeCanBeDeclaredInspection
     *
     * @inheritdoc
     */
    public function cat()
    {
        return new CatStub($this->throwException);
    }

    /**
     * Get bulk parameters used when calling bulk().
     *
     * @return mixed[]|null
     */
    public function getBulkParameters(): ?array
    {
        return $this->bulk;
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent is intentionally ignored as per class comment
     *
     * @inheritdoc
     */
    public function indices()
    {
        // If an exception should be thrown, throw it
        if ($this->throwException === true) {
            throw new RuntimeException('An error occured');
        }
    }
}
