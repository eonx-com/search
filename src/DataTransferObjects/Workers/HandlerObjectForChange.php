<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Workers;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;

/**
 * This is an internal DTO used to keep track of which handler created the ObjectForUpdate.
 *
 * This is an internal class and should not be used by consumers of the search package.
 *
 * @internal
 */
final class HandlerObjectForChange
{
    /**
     * @var string
     */
    private $handlerKey;

    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange
     */
    private $objectForChange;

    /**
     * Constructor.
     *
     * @param string $handlerKey
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange $objectForChange
     */
    public function __construct(string $handlerKey, ObjectForChange $objectForChange)
    {
        $this->objectForChange = $objectForChange;
        $this->handlerKey = $handlerKey;
    }

    /**
     * Returns the handler key.
     *
     * @return string
     */
    public function getHandlerKey(): string
    {
        return $this->handlerKey;
    }

    /**
     * Returns the change subscription.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange
     */
    public function getObjectForChange(): ObjectForChange
    {
        return $this->objectForChange;
    }
}
