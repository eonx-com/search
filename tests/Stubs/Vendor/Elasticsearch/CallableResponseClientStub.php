<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Elasticsearch;

use GuzzleHttp\Ring\Future\FutureArray;
use React\Promise\Deferred;

/**
 * This stub returns a callable response which will eventually resolve to an array when calling bulk()
 *
 * @coversNothing
 */
class CallableResponseClientStub extends ClientStub
{
    /**
     * @var mixed[]
     */
    private $errors;

    /**
     * Create deferred response stub
     *
     * @param mixed[] $errors Errors to return after resolution
     */
    public function __construct(?array $errors = null)
    {
        $this->errors = $errors ?? [];

        parent::__construct(false);
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection Parent is intentionally ignored as per class comment
     *
     * @inheritdoc
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
