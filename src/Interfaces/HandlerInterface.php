<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface HandlerInterface
{
    /**
     * Returns the index name that this handler is responsible for.
     *
     * @return string
     */
    public function getIndexName(): string;

    /**
     * Returns the identifier used externally for the object.
     *
     * @param object $object
     *
     * @return mixed|null
     */
    public function getSearchId(object $object);

    /**
     * Indicates if this handler accepts updates or deletes
     * related to the supplied class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function handles(string $class): bool;

    /**
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param mixed $object
     *
     * @return mixed[][]
     */
    public function transform($object): ?array;
}
