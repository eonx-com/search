<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use LoyaltyCorp\Search\Interfaces\Helpers\ClientBulkResponseHelperInterface;

class ClientBulkResponseHelper implements ClientBulkResponseHelperInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\BulkFailureException If there is at least one record with an error
     */
    public function checkBulkResponsesForErrors($response, string $type): void
    {
        $responses = $this->extractBulkResponseItems($response, $type);

        $errors = [];

        /**
         * @var mixed[] $item
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 typehint required until PhpStorm recognises === check
         */
        foreach ($responses as $item) {
            // If item isn't the right type or it's not an error, skip
            if (isset($item[$type]) === false ||
                \is_array($item[$type]) === false ||
                isset($item[$type]['error']) === false ||
                $item[$type]['error'] === false) {
                continue;
            }

            // Get error
            $errors[] = $item[$type]['error'];
        }

        // If there are no errors, return
        if (\count($errors) === 0) {
            return;
        }

        // Throw bulk exception
        throw new BulkFailureException(
            $errors,
            \sprintf('At least one record returned an error during bulk %s', $type)
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
     * Extract items from bulk response
     *
     * @param mixed $response The response from the bulk action
     * @param string $type The bulk action that was performed
     *
     * @return mixed[]
     */
    private function extractBulkResponseItems($response, string $type): array
    {
        // If response is callable, wait until we have the final response
        if (($response instanceof FutureArrayInterface) === true) {
            $response = $this->unwrapPromise($response);
        }

        // If final response isn't an array, throw exception
        if (\is_array($response) === false) {
            throw new BulkFailureException([], \sprintf('Invalid response received from bulk %s', $type));
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
