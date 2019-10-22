<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Helpers;

use GuzzleHttp\Ring\Future\FutureArrayInterface;

interface ClientBulkResponseHelperInterface
{
    /**
     * Check a bulk response array for errors, throw an exception if errors are found
     *
     * @param mixed $response The response from the bulk action
     * @param string $type The bulk action that was performed
     *
     * @return void
     */
    public function checkBulkResponsesForErrors($response, string $type): void;

    /**
     * Wait for a promise to resolve and unwrap the response
     *
     * @param \GuzzleHttp\Ring\Future\FutureArrayInterface $promise
     *
     * @return mixed
     */
    public function unwrapPromise(FutureArrayInterface $promise);
}
