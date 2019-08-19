<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface EntitySearchHandlerInterface extends SearchHandlerInterface
{
    /**
     * Get the class this search handler will support
     *
     * @return string[] Fully Qualified Class Names that implement the Search Handler interface
     */
    public function getHandledClasses(): array;

    /**
     * Returns the identifier used externally for the object.
     *
     * @param object $object
     *
     * @return mixed|null
     */
    public function getSearchId(object $object);
}
