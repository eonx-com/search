<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This file emulates a class Laravel Doctrine providers via the 'registry' alias in Laravel application container.
 *
 * @SuppressWarnings(PHPMD.LongVariable) Doctrine defines these variables
 *
 * @coversNothing
 */
final class RegistryStub implements ManagerRegistry
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
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($name = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
        // TODO: Implement getConnectionNames() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
        // TODO: Implement getConnections() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName()
    {
        // TODO: Implement getDefaultManagerName() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getManager($name = null): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass($class)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerNames()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null)
    {
    }
}
