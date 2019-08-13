<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

use GuzzleHttp\Ring\Future\FutureArray;
use React\Promise\Deferred;

/**
 * This stub returns a callable response which will eventually resolve to an array when calling bulk()
 */
class CallableResponseClientStub extends ClientStub
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent is intentionally ignored as per class comment
     *
     * @inheritdoc
     */
    public function bulk($params = null)
    {
        $outer = new Deferred();

        return new FutureArray(
            $outer->promise(),
            static function () use ($outer): void {
                $outer->resolve(['errors' => false]);
            }
        );
    }
}
