<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;

final class ClientBulkResponseHelperStub implements ClientBulkResponseHelperInterface
{
    /**
     * @inheritDoc
     */
    public function checkBulkResponsesForErrors($response): void
    {
    }

    /**
     * @inheritDoc
     */
    public function unwrapPromise(FutureArrayInterface $promise)
    {
        return $promise->promise();
    }
}
