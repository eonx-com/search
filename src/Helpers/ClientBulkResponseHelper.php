<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;

final class ClientBulkResponseHelper implements ClientBulkResponseHelperInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\BulkFailureException If there is at least one record with an error
     */
    public function checkBulkResponsesForErrors($response): void
    {
        $responses = $this->extractBulkResponseItems($response);

        $errors = [];

        /**
         * @var mixed[] $item
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 typehint required until PhpStorm recognises === check
         */
        foreach ($responses as $item) {
            foreach ($item as $action) {
                $itemErrors = $action['error'] ?? false;

                // The item had no errors
                if ($itemErrors === false ||
                    (\is_array($itemErrors) === true && \count($itemErrors) === 0)) {
                    continue;
                }

                $errors[] = $itemErrors;
            }
        }

        // If there are no errors, return
        if (\count($errors) === 0) {
            return;
        }

        // Throw bulk exception
        throw new BulkFailureException(
            $errors,
            'At least one record returned an error during bulk request.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unwrapPromise(FutureArrayInterface $promise)
    {
        do {
            $promise = $promise->wait();
        } while (($promise instanceof FutureArrayInterface) === true);

        return $promise;
    }

    /**
     * Extract items from bulk response.
     *
     * @param mixed $response The response from the bulk action
     *
     * @return mixed[]
     */
    private function extractBulkResponseItems($response): array
    {
        // If response is callable, wait until we have the final response
        if (($response instanceof FutureArrayInterface) === true) {
            $response = $this->unwrapPromise($response);
        }

        // If final response isn't an array, throw exception
        if (\is_array($response) === false) {
            throw new BulkFailureException([], 'Invalid response received from bulk request.');
        }

        // If the top level indicates no errors or items, return nothing
        if (isset($response['errors']) === false ||
            $response['errors'] === false ||
            isset($response['items']) === false ||
            \is_array($response['items']) === false) {
            return [];
        }

        return $response['items'];
    }
}
