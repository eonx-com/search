<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects\Populators;

use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

/**
 * An internal DTO used to keep track of the handler that emitted the ObjectForUpdate DTO.
 *
 * @internal
 */
final class HandlerObjectForUpdate
{
    /**
     * @var string
     */
    private $handlerKey;

    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate
     */
    private $objectForUpdate;

    /**
     * Constructor
     *
     * @param string $handlerKey
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate $objectForUpdate
     */
    public function __construct(string $handlerKey, ObjectForUpdate $objectForUpdate)
    {
        $this->objectForUpdate = $objectForUpdate;
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
     * Returns the object for update dto.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate
     */
    public function getObjectForUpdate(): ObjectForUpdate
    {
        return $this->objectForUpdate;
    }
}
