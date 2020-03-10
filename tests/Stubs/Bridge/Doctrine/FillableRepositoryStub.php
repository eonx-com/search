<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Bridge\Doctrine;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface;

/**
 * @coversNothing
 *
 * @template T
 *
 * @implements \LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface<T>
 */
class FillableRepositoryStub extends BaseStub implements FillableRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFillIterable(): iterable
    {
        return $this->doStubCall(__FUNCTION__, \get_defined_vars(), []);
    }

    /**
     * {@inheritdoc}
     */
    public function prefillSearch(iterable $changes): void
    {
        $this->doStubCall(__FUNCTION__, \get_defined_vars(), null);
    }
}
