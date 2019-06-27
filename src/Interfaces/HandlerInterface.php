<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface HandlerInterface
{
    /**
     * Get the class this search handler will support
     *
     * @return string Fully Qualified Class Name
     */
    public function getHandledClass(): string;

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
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param mixed $object
     *
     * @return mixed[][]
     */
    public function transform($object): ?array;
}
