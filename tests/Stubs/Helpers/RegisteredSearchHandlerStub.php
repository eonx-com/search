<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;

class RegisteredSearchHandlerStub implements RegisteredSearchHandlerInterface
{
    /**
     * @var \LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface[]
     */
    private $handlers;

    /**
     * RegisteredSearchHandlerStub constructor.
     *
     * @param \LoyaltyCorp\Search\Interfaces\EntitySearchHandlerInterface[]|null $handlers
     */
    public function __construct(?array $handlers = null)
    {
        $this->handlers = $handlers ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->handlers;
    }
}
