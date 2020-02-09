<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration\Fixtures\Repositories;

use Doctrine\ORM\EntityRepository;
use LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface;
use LoyaltyCorp\Search\Bridge\Doctrine\SearchRepositoryTrait;

class FillableRepository extends EntityRepository implements FillableRepositoryInterface
{
    use SearchRepositoryTrait;

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getFillIterable(): iterable
    {
        return $this->doGetFillIterable(
            $this->createQueryBuilder('e'),
            $this->_class,
            $this->_entityName
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function prefillSearch(iterable $changes): void
    {
        $this->doPrefillSearch(
            $this->createQueryBuilder('e'),
            $this->_class,
            $this->_entityName,
            $changes
        );
    }
}
