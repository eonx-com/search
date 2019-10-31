<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Factories;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManagerInterface;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\EntityManagerHelperFactoryInterface;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;

final class EntityManagerHelperFactory implements EntityManagerHelperFactoryInterface
{
    /**
     * @var \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface
     */
    private $eoneoEntityManager;

    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * EntityManagerHelperFactory constructor.
     *
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $eoneoEntityManager
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry
     */
    public function __construct(EoneoPayEntityManagerInterface $eoneoEntityManager, ManagerRegistry $registry)
    {
        $this->eoneoEntityManager = $eoneoEntityManager;
        $this->registry = $registry;
    }

    /**
     * Create entity manager helper.
     *
     * @return \LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface
     */
    public function create(): EntityManagerHelperInterface
    {
        $entityManager = $this->registry->getManager();

        if (($entityManager instanceof DoctrineEntityManagerInterface) === true) {
            /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

            return new EntityManagerHelper($entityManager, $this->eoneoEntityManager);
        }

        throw new BindingResolutionException('Could not resolve Entity Manager from application container');
    }
}
