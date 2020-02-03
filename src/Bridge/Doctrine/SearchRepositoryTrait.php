<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;

/**
 * A base repository trait that implements basic functionality required for the
 * DoctrineSearchHandler. You will need to annotate your class with the template
 * and implements annotations.
 */
trait SearchRepositoryTrait
{
    /**
     * Returns an iterable that is used to fill search indices with the entity
     * that the repository belongs to.
     *
     * @template T
     *
     * @phpstan-param class-string<T> $searchClass
     *
     * @phpstan-return array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>>
     *
     * @param \Doctrine\ORM\QueryBuilder $builder
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     * @param string $searchClass
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     *
     * @codeCoverageIgnore This function cannot be simply unit tested. The Query object is final
     *   which means a large number of EntityManager functions must be mocked to reach the iterate()
     *   call.
     */
    protected function doGetFillIterable(
        QueryBuilder $builder,
        ClassMetadata $classMetadata,
        string $searchClass
    ): iterable {
        // We only want to select the primary key of the entities to be filled,
        // since this is a generic function calling getSingleIdentifierFieldName
        // will work, but throws an exception if the entity has a composite
        // primary key.
        $field = $classMetadata->getSingleIdentifierFieldName();

        $root = $builder->getRootAliases()[0] ?? 'e';
        $builder->select(\sprintf('%s.%s', $root, $field));

        // Doctrine has weird behaviour where the data index for the result is
        // in an incrementing index for each row
        $index = 0;
        foreach ($builder->getQuery()->iterate() as $result) {
            // For each result, create an ObjectForUpdate object.
            yield new ObjectForUpdate($searchClass, [
                $field => $result[$index][$field],
            ]);
        }
    }

    /**
     * Prefills changes with their entities. The method will call setObject() on each
     * ObjectForChange that it can preload an object for.
     *
     * PHPDoc copied from FillableRepositoryInterface because traits cant implement
     * interfaces.
     *
     * @template T
     *
     * @phpstan-param class-string<T> $searchClass
     * @phpstan-param array<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<T>> $changes
     *
     * @param \Doctrine\ORM\QueryBuilder $builder
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     * @param string $searchClass
     * @param \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[] $changes
     *
     * @return void
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     *
     * @codeCoverageIgnore This function cannot be simply unit tested. The Query object is final
     *   which means a large number of EntityManager functions must be mocked to reach the iterate()
     *   call.
     */
    protected function doPrefillSearch(
        QueryBuilder $builder,
        ClassMetadata $classMetadata,
        string $searchClass,
        iterable $changes
    ): void {
        // Get the Id field name - this function only supports single identifiers and
        // will throw a MappingException if the entity is a composite primary key.
        $field = $classMetadata->getSingleIdentifierFieldName();

        $ids = [];
        $reverseIds = [];

        foreach ($changes as $change) {
            // The change didnt have an id field we expect, skip prefilling that
            // change.
            if (\array_key_exists($field, $change->getIds()) === false ||
                $change->getClass() !== $searchClass) {
                continue;
            }

            $lookupId = $change->getIds()[$field];
            $ids[] = $lookupId;
            $reverseIds[$lookupId] = $change;
        }

        $expr = $builder->expr();

        $root = $builder->getRootAliases()[0] ?? 'e';
        $builder->where($expr->in(\sprintf('%s.%s', $root, $field), $ids));

        foreach ($builder->getQuery()->iterate() as $entity) {
            $entityId = $classMetadata->getFieldValue($entity, $field);

            // We somehow got back an entity that wasn't in the reverseId map.
            if (isset($reverseIds[$entityId]) === false) {
                continue;
            }

            // We got back an entity that doesnt implement the changeClass
            $changeClass = $reverseIds[$entityId]->getClass();
            if ($entity instanceof $changeClass === false) {
                continue;
            }

            $reverseIds[$entityId]->setObject($entity);
        }
    }
}
