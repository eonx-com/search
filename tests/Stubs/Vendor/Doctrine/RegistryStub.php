<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

/**
 * This file emulates a class Laravel Doctrine providers via the 'registry' alias in Laravel application container
 */
class RegistryStub
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * Registry constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get registry manager
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
