<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Bridge\Doctrine;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface;

class FillableRepositoryStub extends BaseStub implements FillableRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFillIterable(): iterable
    {
        return $this->returnOrThrowResponse(__FUNCTION__);
    }

    /**
     * {@inheritdoc}
     */
    public function prefillSearch(iterable $changes): void
    {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }
}
