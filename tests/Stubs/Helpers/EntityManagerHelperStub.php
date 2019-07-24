<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Helpers;

use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;

class EntityManagerHelperStub implements EntityManagerHelperInterface
{
    /**
     * @var int
     */
    private $numberOfIds;

    /**
     * EntityManagerHelperStub constructor.
     *
     * @param int|null $numberOfIds
     */
    public function __construct(?int $numberOfIds = null)
    {
        $this->numberOfIds = $numberOfIds ?? 10;
    }

    /**
     * {@inheritdoc}
     */
    public function iterateAllIds(string $entityClass): iterable
    {
        // Generator for array values of 'abc-12*', where * equals iteration number based on $numberOfIds
        yield from \array_map(
            static function ($value) {
                return \sprintf('abc-12%d', $value);
            },
            \range(0, $this->numberOfIds)
        );
    }
}
