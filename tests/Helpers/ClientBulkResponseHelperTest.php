<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Helpers;

use GuzzleHttp\Ring\Future\CompletedFutureValue;
use GuzzleHttp\Ring\Future\FutureArray;
use LoyaltyCorp\Search\Exceptions\BulkFailureException;
use LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper
 */
final class ClientBulkResponseHelperTest extends TestCase
{
    /**
     * Ensure the checking in bulk results allows succession if there is no errors.
     *
     * @return void
     */
    public function testCheckingResponseForErrorsCanSucceed(): void
    {
        $bulkResponseHelper = $this->createInstance();

        $bulkResponseHelper->checkBulkResponsesForErrors(
            [
                'errors' => true,
                'items' => [['update' => ['_id' => 'nice-id']], ['delete' => []]],
            ]
        );

        /**
         * The method under test returns void
         * This test just ensures success is possible when errors = false & error key is missing.
         */
        $this->addToAssertionCount(1);
    }

    /**
     * Test bulk failures with an actual failure response from Elasticsearch.
     *
     * @return void
     */
    public function testBulkFailures(): void
    {
        $bulkResponseHelper = $this->createInstance();

        $response = [
            'took' => 0,
            'errors' => true,
            'items' => [
                [
                    'index' => [
                        '_index' => 'subscriptions_133e6cd4-cfcd-11e9-9ccc-06d3b5ba179c_20191121121030',
                        '_type' => 'doc',
                        '_id' => 'PFB363UWVN',
                        'status' => 400,
                        'error' => [
                        'type' => 'strict_dynamic_mapping_exception',
                            'reason' => 'mapping set to strict, dynamic introduction of [name] within [payment_source.metadata] is not allowed' // phpcs:ignore
                        ]
                    ]
                ]
            ]
        ];

        $this->expectException(BulkFailureException::class);
        $this->expectExceptionMessage('At least one record returned an error during bulk request.');

        $bulkResponseHelper->checkBulkResponsesForErrors($response);
    }

    /**
     * Ensure the checking in bulk results allows succession if there is no errors and a promise is used.
     *
     * @return void
     */
    public function testCheckingResponseForErrorsCanSucceedWithPromise(): void
    {
        $value = new CompletedFutureValue([
            'items' => [['update' => ['_id' => 'nice-id']], ['delete' => []]],
        ]);
        $array = new FutureArray($value);

        $bulkResponseHelper = $this->createInstance();

        $bulkResponseHelper->checkBulkResponsesForErrors($array);

        /**
         * The method under test returns void
         * This test just ensures success is possible when errors = false & error key is missing.
         */
        $this->addToAssertionCount(1);
    }

    /**
     * Ensure the checking in bulk results throws an exception if there is an error.
     *
     * @return void
     */
    public function testCheckingResponseForErrorsThrowsExceptionOnError(): void
    {
        $bulkResponseHelper = $this->createInstance();

        $this->expectException(BulkFailureException::class);
        $this->expectExceptionMessage('At least one record returned an error during bulk request.');

        $bulkResponseHelper->checkBulkResponsesForErrors(
            [
                'errors' => true,
                'items' => [['update' => ['error' => 'big error']]],
            ]
        );
    }

    /**
     * Ensure when the response payload is not an array, an exception is thrown.
     *
     * @return void
     */
    public function testExceptionThrownWhenResponseFormatInvalid(): void
    {
        $bulkResponseHelper = $this->createInstance();

        $this->expectException(BulkFailureException::class);
        $this->expectExceptionMessage('Invalid response received from bulk request.');

        $bulkResponseHelper->checkBulkResponsesForErrors(false);
    }

    /**
     * Ensure the async promise unwrapper delivers the expected result.
     *
     * @return void
     */
    public function testUnwrappingPromise(): void
    {
        $bulkResponseHelper = $this->createInstance();
        $value = new CompletedFutureValue(['test' => true]);
        $array = new FutureArray($value);

        $unwrappedResponse = $bulkResponseHelper->unwrapPromise($array);

        self::assertSame(['test' => true], $unwrappedResponse);
    }

    /**
     * Instantiate an instance.
     *
     * @return \LoyaltyCorp\Search\Helpers\ClientBulkResponseHelper
     */
    private function createInstance(): ClientBulkResponseHelper
    {
        return new ClientBulkResponseHelper();
    }
}
