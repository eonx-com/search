<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Exception;

/**
 * @coversNothing
 */
class DoctrineTestCase extends TestCase
{
    /**
     * SQL queries to create database schema.
     *
     * @var string
     */
    private static $sql;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * Whether the database has been seeded or not
     *
     * @var bool
     */
    private $seeded = false;

    /**
     * Get doctrine entity manager instance
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Static access to entity manager required to create instance
     */
    protected function getDoctrineEntityManager(): EntityManagerInterface
    {
        $paths = [__DIR__ . '/Stubs/Entities'];
        $setup = new Setup();
        $config = $setup::createAnnotationMetadataConfiguration($paths, true, null, null, false);
        $dbParams = ['driver' => 'pdo_sqlite', 'memory' => true];

        return EntityManager::create($dbParams, $config);
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager !== null) {
            return $this->entityManager;
        }

        // Lazy load database
        $this->createSchema();

        return $this->entityManager;
    }

    /**
     * Lazy load database schema only when required
     *
     * @return void
     */
    private function createSchema(): void
    {
        // If schema is already created, return
        if ($this->seeded === true) {
            return;
        }

        // Create schema
        try {
            $this->entityManager = $this->getDoctrineEntityManager();

            // If schema hasn't been defined, define it, this will happen once per run
            if (self::$sql === null) {
                $tool = new SchemaTool($this->entityManager);
                $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
                self::$sql = \implode(';', $tool->getCreateSchemaSql($metadata));
            }

            $this->entityManager->getConnection()->exec(self::$sql);
        } catch (Exception $exception) {
            self::fail(\sprintf('Exception thrown when creating database schema: %s', $exception->getMessage()));
        }

        $this->seeded = true;
    }
}
