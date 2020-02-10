<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use Eonx\TestUtils\Stubs\BaseStub;
use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;

/**
 * @coversNothing
 */
final class PopulatorStub extends BaseStub implements PopulatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function populate(
        TransformableSearchHandlerInterface $handler,
        string $indexSuffix,
        int $batchSize
    ): void {
        $this->saveCalls(__FUNCTION__, \get_defined_vars());
    }
}
