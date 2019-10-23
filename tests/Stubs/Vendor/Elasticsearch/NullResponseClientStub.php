<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

/**
 * This stub returns a null/invalid response when calling bulk()
 *
 * @coversNothing
 */
class NullResponseClientStub extends ClientStub
{
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
