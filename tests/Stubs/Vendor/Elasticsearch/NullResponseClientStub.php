<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

use Elasticsearch\Client;

/**
 * This stub returns a null/invalid response when calling bulk().
 *
 * @coversNothing
 */
final class NullResponseClientStub extends Client
{
    /**
     * Create null response stub.
     *
     * @noinspection PhpMissingParentConstructorInspection Parent is intentionally ignored
     */
    public function __construct()
    {
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection ReturnTypeCanBeDeclaredInspection
     *
     * @inheritdoc
     */
    public function bulk($params = null)
    {
        return null;
    }
}
