<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Helpers;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManager;
use Doctrine\ORM\ORMException;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use LoyaltyCorp\Search\Exceptions\DoctrineException;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;

class EntityManagerHelper implements EntityManagerHelperInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $doctrineManager;

    /**
     * @var \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface
     */
    private $eoneoPayManager;

    /**
     * EntityManagerHelper constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $doctrineManager
     * @param \EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface $eoneoPayManager
     */
    public function __construct(DoctrineEntityManager $doctrineManager, EoneoPayEntityManagerInterface $eoneoPayManager)
    {
        $this->doctrineManager = $doctrineManager;
        $this->eoneoPayManager = $eoneoPayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllIds(string $class, array $ids): array
    {
        return $this->eoneoPayManager->findByIds($class, $ids);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\BindingResolutionException
     * @throws \LoyaltyCorp\Search\Exceptions\DoctrineException
     */
    public function iterateAllIds(string $entityClass): iterable
    {
        try {
            $primaryKeyField = $this->doctrineManager->getClassMetadata($entityClass)->getSingleIdentifierFieldName();

            $iterableResult = $this->doctrineManager->createQuery(
                \sprintf('SELECT e.%s FROM %s e', $primaryKeyField, $entityClass)
            )->iterate();
        } catch (ORMException | DBALException $doctrineException) {
            throw new DoctrineException(
                \sprintf('Unable to iterate all primary keys of entity \'%s\'', $entityClass),
                0,
                $doctrineException
            );
        }
        foreach ($iterableResult as $iteration => $row) {
            yield $row[$iteration][$primaryKeyField];
        }
    }
}
