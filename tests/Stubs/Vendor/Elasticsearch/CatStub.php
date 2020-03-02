<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

use Elasticsearch\Namespaces\CatNamespace;
use RuntimeException;

/**
 * This stub overloads methods within the elasticsearch client as it doesn't implement an interface so
 * can't be stubbed properly, this stub will not pass anything to the actual elasticsearch client.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Well tested code for all the cases
 *
 * @coversNothing
 */
final class CatStub extends CatNamespace
{
    /**
     * @var bool
     */
    private $throwException;

    /**
     * @noinspection PhpMissingParentConstructorInspection Parent is intentionally ignored as per class comment
     *
     * Create stub
     *
     * @param bool|null $throwException Whether a call to bulk() should throw an exception or not
     */
    public function __construct(?bool $throwException = null)
    {
        $this->throwException = $throwException ?? false;
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection ReturnTypeCanBeDeclaredInspection
     *
     * {@inheritdoc}
     */
    public function count($params = [])
    {
        // If an exception should be thrown, throw it
        if ($this->throwException === true) {
            throw new RuntimeException('An error occured');
        }

        return [['count' => 0]];
    }
}
