<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches;

/**
 * @coversNothing
 */
final class SearchableStub
{
    /**
     * @var string
     */
    private $searchId;

    /**
     * Constructor.
     *
     * @param string|null $searchId
     */
    public function __construct(?string $searchId = null)
    {
        $this->searchId = $searchId ?? 'searchable';
    }

    /**
     * Get search id for this stub.
     *
     * @return string|null
     */
    public function getSearchId(): ?string
    {
        return $this->searchId;
    }

    /**
     * Convert object to an array.
     *
     * @return string[]|null
     */
    public function toArray(): ?array
    {
        return ['search' => 'body'];
    }
}
