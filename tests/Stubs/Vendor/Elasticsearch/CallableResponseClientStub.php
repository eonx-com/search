<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

use Elasticsearch\Client;
use GuzzleHttp\Ring\Future\FutureArray;
use React\Promise\Deferred;

/**
 * This stub returns a callable response which will eventually resolve to an array when calling bulk().
 *
 * @coversNothing
 */
final class CallableResponseClientStub extends Client
{
    /**
     * @var mixed[]
     */
    private $errors;

    /**
     * Create deferred response stub.
     *
     * @noinspection PhpMissingParentConstructorInspection Parent is intentionally ignored
     *
     * @param mixed[] $errors Errors to return after resolution
     */
    public function __construct(?array $errors = null)
    {
        $this->errors = $errors ?? [];
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent is intentionally ignored as per class comment
     *
     * {@inheritdoc}
     */
    public function bulk($params = null)
    {
        $outer = new Deferred();

        /** @noinspection PhpMethodParametersCountMismatchInspection Constructor exists in trait */
        return new FutureArray(
            $outer->promise(),
            function () use ($outer): void {
                $outer->resolve($this->errors);
            }
        );
    }
}
