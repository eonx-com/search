<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('search');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('use_listeners')->defaultFalse()
            ->end();

        return $treeBuilder;
    }
}
