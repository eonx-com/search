<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Messages;

final class EntityChangeMessage
{
    /**
     * Contains an array of ChangedEntity DTO objects that indicate any
     * changes that may have occurred during the flush process.
     *
     * @var \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[]
     */
    private $changes;

    /**
     * Constructor
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[] $changes
     */
    public function __construct(array $changes)
    {
        $this->changes = $changes;
    }

    /**
     * @return \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }
}
