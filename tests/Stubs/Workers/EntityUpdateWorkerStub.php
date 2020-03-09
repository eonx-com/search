<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Workers;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Interfaces\Workers\EntityUpdateWorkerInterface;

/**
 * @coversNothing
 */
final class EntityUpdateWorkerStub extends BaseStub implements EntityUpdateWorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $changes): void
    {
        $this->doStubCall(__FUNCTION__, \get_defined_vars(), null);
    }
}
