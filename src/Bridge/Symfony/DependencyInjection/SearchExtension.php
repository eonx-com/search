<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\DependencyInjection;

use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @param mixed[] $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');
        $loader->load('services_commands.yaml');
        $loader->load('services_events.yaml');

        // Auto tag search handlers
        $container->registerForAutoconfiguration(SearchHandlerInterface::class)->addTag('search_handler');
    }
}
