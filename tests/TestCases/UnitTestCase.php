<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\TestCases;

use Eonx\TestUtils\TestCases\UnitTestCase as BaseUnitTestCase;

/**
 * @coversNothing
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren) All tests extend this class
 */
abstract class UnitTestCase extends BaseUnitTestCase
{
    /**
     * Returns private property value.
     *
     * @param mixed $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getPrivatePropertyValue($object, string $property)
    {
        return (function () use ($property) {
            return $this->{$property};
        })->call($object);
    }
}
