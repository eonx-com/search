<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches;

/**
 * @coversNothing
 */
final class NoDocumentBodyStub
{
    /**
     * Get search id for this stub
     *
     * @return string|null
     */
    public function getSearchId(): ?string
    {
        return 'nobody';
    }
}
