<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

/**
 * This stub returns a null/invalid response when calling bulk()
 */
class NullResponseClientStub extends ClientStub
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent is intentionally ignored as per class comment
     *
     * @inheritdoc
     */
    public function bulk($params = null)
    {
        return null;
    }
}
