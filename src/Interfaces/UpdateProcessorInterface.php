<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

/**
 * This processor will process arrays of ObjectForUpdate and ObjectForDelete DTOs and call the original
 * handlers that generated the DTOs to transform them into.
 */
interface UpdateProcessorInterface
{
    /**
     * Handles updates for changes.
     *
     * @param string $indexSuffix
     * @param \LoyaltyCorp\Search\DataTransferObjects\Workers\HandlerObjectForChange[] $updates
     *
     * @return void
     */
    public function process(string $indexSuffix, array $updates): void;
}
