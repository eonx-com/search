<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\Illuminate\Contracts\Foundation;

use ArrayAccess;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) This class is implemented from a Laravel interface
 * @SuppressWarnings(PHPMD.ExcessivePublicCount) This class is implemented from a Laravel interface
 * @SuppressWarnings(PHPMD.TooManyMethods) This class is implemented from a Laravel interface
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) This class is implemented from a Laravel interface
 *
 * @coversNothing
 *
 * @implements \ArrayAccess<string, mixed>
 */
final class ApplicationStub implements Application, ArrayAccess
{
    /**
     * Container bindings.
     *
     * @var \Illuminate\Container\Container
     */
    private $container;

    /**
     * Create container.
     */
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * {@inheritdoc}
     */
    public function addContextualBinding($concrete, $abstract, $implementation): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function afterResolving($abstract, ?Closure $callback = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function alias($abstract, $alias): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function basePath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bind($abstract, $concrete = null, $shared = null): void
    {
        $this->container->bind($abstract, $concrete, $shared ?? false);
    }

    /**
     * {@inheritdoc}
     */
    public function bindIf($abstract, $concrete = null, $shared = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function booted($callback): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function booting($callback): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapPath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrappers): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bound($abstract)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function call($callback, ?array $parameters = null, $defaultMethod = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configPath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configurationIsCached()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function databasePath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environment(...$environments)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFile()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFilePath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function environmentPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function extend($abstract, Closure $closure): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function factory($abstract)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($containerId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedConfigPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedPackagesPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedRoutesPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders($provider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function has($containerId)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function instance($abstract, $instance): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isDownForMaintenance()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loadDeferredProviders(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loadEnvironmentFrom($file)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function make($abstract, ?array $parameters = null)
    {
        if ($parameters === null) {
            $parameters = [];
        }

        return $this->callMethod('make', [$abstract, $parameters]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register($provider, $force = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerConfiguredProviders(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerDeferredProvider($provider, $service = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolveProvider($provider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolved($abstract)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resolving($abstract, ?Closure $callback = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function resourcePath($path = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function routesAreCached()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function runningInConsole()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function runningUnitTests()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipMiddleware()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function singleton($abstract, $concrete = null): void
    {
        $this->callMethod('singleton', [$abstract, $concrete]);
    }

    /**
     * {@inheritdoc}
     */
    public function singletonIf($abstract, $concrete = null)
    {
        $this->container->singletonIf($abstract, $concrete);
    }

    /**
     * {@inheritdoc}
     */
    public function storagePath()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tag($abstracts, $tags): void
    {
        $this->callMethod('tag', [$abstracts, $tags]);
    }

    /**
     * {@inheritdoc}
     */
    public function tagged($tag)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function version()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function when($concrete)
    {
    }

    /**
     * Call a container method.
     *
     * @param string $method The method to call
     * @param mixed[]|null $parameters Parameters to pass to the method
     *
     * @return mixed
     */
    private function callMethod(string $method, ?array $parameters = null)
    {
        return \call_user_func_array([$this->container, $method], $parameters ?? []);
    }
}
