<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Doctrine;

use Doctrine\ORM\EntityRepository;
use LoyaltyCorp\Search\Bridge\Doctrine\Interfaces\FillableRepositoryInterface;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

/**
 * A base repository that implements basic functionality required for the
 * DoctrineSearchHandler.
 *
 * If you cannot use this repository in your own project, copy and paste the
 * implementation into your base repository (or base SearchableRepository).
 *
 * @template T
 */
class SearchRepository extends EntityRepository implements FillableRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     *
     * @codeCoverageIgnore This function cannot be simply unit tested. The Query object is final
     *   which means a large number of EntityManager functions must be mocked to reach the iterate()
     *   call.
     */
    public function getFillIterable(): iterable
    {
        // We only want to select the primary key of the entities to be filled,
        // since this is a generic function calling getSingleIdentifierFieldName
        // will work, but throws an exception if the entity has a composite
        // primary key.
        $field = $this->_class->getSingleIdentifierFieldName();

        $builder = $this->createQueryBuilder('e');
        $builder->select(\sprintf('e.%s', $field));

        // Doctrine has weird behaviour where the data index for the result is
        // in an incrementing index for each row
        $index = 0;
        foreach ($builder->getQuery()->iterate() as $result) {
            // For each result, create an ObjectForUpdate object.
            yield new ObjectForUpdate($this->_entityName, [
                $field => $result[$index][$field]
            ]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     *
     * @codeCoverageIgnore This function cannot be simply unit tested. The Query object is final
     *   which means a large number of EntityManager functions must be mocked to reach the iterate()
     *   call.
     */
    public function prefillSearch(iterable $changes): void
    {
        // Get the Id field name - this function only supports single identifiers and
        // will throw a MappingException if the entity is a composite primary key.
        $field = $this->_class->getSingleIdentifierFieldName();

        $ids = [];
        $reverseIds = [];

        foreach ($changes as $change) {
            // The change didnt have an id field we expect, skip prefilling that
            // change.
            if (\array_key_exists($field, $change->getIds()) === false ||
                $change->getClass() !== $this->_entityName) {
                continue;
            }

            $lookupId = $change->getIds()[$field];
            $ids[] = $lookupId;
            $reverseIds[$lookupId] = $change;
        }

        $builder = $this->createQueryBuilder('e');

        $expr = $builder->expr();
        $builder->where($expr->in(\sprintf('e.%s', $field), $ids));

        foreach ($builder->getQuery()->iterate() as $entity) {
            $entityId = $this->_class->getFieldValue($entity, $field);

            // If we got the thing we were expecting, and it had an id, set the object
            // into the change.
            if ((($reverseIds[$entityId] ?? null) instanceof $this->_entityName) === false) {
                $reverseIds[$entityId]->setObject($entity);
            }
        }
    }
}
