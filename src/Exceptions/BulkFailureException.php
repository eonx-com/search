<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Exceptions;

use Throwable;

final class BulkFailureException extends SearchException
{
    /**
     * @var mixed[]
     */
    private $errors;

    /**
     * Create bulk failure exception
     *
     * @param mixed[] $errors Bulk failures
     * @param string|null $message Error message
     * @param int|null $code Error code
     * @param \Throwable|null $previous Chained exception
     */
    public function __construct(array $errors, ?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? '', $code ?? 0, $previous);

        $this->errors = $errors;
    }

    /**
     * Get errors
     *
     * @return mixed[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
