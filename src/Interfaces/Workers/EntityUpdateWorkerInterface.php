<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces\Workers;

interface EntityUpdateWorkerInterface
{
    /**
     * Handles entity change event and updates ES indexes.
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[] $changes
     *
     * @return void
     */
    public function handle(array $changes): void;
}
