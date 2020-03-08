<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Unit\Bridge\Symfony\DependencyInjection;

use LoyaltyCorp\Search\Bridge\Symfony\DependencyInjection\Configuration;
use Tests\LoyaltyCorp\Search\TestCases\UnitTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Symfony\DependencyInjection\Configuration
 */
final class ConfigurationTest extends UnitTestCase
{
    /**
     * Test getConfigTreeBuilder returns correct tree builder.
     *
     * @return void
     */
    public function testGetConfigTreeBuilder(): void
    {
        $treeBuilder = (new Configuration())->getConfigTreeBuilder();

        self::assertArrayHasKey('use_listeners', $treeBuilder->getRootNode()->getChildNodeDefinitions());
    }
}
