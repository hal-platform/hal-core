<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core;

use ProxyManager\Configuration;
use RuntimeException;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DI
{
    const PRIMARY_CONFIGURATION_FILE = 'config.yaml';
    const ENV_CACHE_DISABLED = 'HAL_DI_DISABLE_CACHE_ON';

    // When the container is built (dev-mode), this dumps and loads the cached
    // container instead of using "ContainerBuilder"
    const BUILD_AND_CACHE = false;

    const DI_EXTENSIONS = [];

    /**
     * @param array $paths
     * @param bool $resolveEnvironment
     *
     * @return ContainerBuilder
     */
    public static function buildDI(array $paths, bool $resolveEnvironment = false): ContainerBuilder
    {
        $container = new ContainerBuilder;

        $loader = static::buildConfigLoader($container, $paths);

        if (class_exists(Configuration::class) && class_exists(RuntimeInstantiator::class)) {
            $container->setProxyInstantiator(new RuntimeInstantiator);
        }

        $extensions = [];
        foreach (static::DI_EXTENSIONS as $extClass) {
            if (!class_exists($extClass)) {
                throw new RuntimeException("Symfony DI Extension not found: \"${extClass}\"");
            }

            $extensions[] = new $extClass;
        }

        foreach ($extensions as $ext) {
            $container->registerExtension($ext);
        }

        $loader->load(static::PRIMARY_CONFIGURATION_FILE);

        foreach ($extensions as $ext) {
            $container->loadFromExtension($ext->getAlias());
        }

        $container->compile($resolveEnvironment);

        return $container;
    }

    /**
     * @param array $paths
     * @param array $options
     *
     * @return ContainerInterface|null
     */
    public static function getDI(array $paths, array $options): ?ContainerInterface
    {
        $class = $options['class'] ?? '';
        if (!$class) {
            return null;
        }

        $cacheDisabled = getenv(static::ENV_CACHE_DISABLED);
        if ($cacheDisabled) {
            return self::buildContainer($paths, $class, $options);
        }

        return self::getCachedContainer($class);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $options
     *
     * @return string|array|null
     */
    public static function cacheDI(ContainerBuilder $container, array $options)
    {
        $class = $options['class'] ?? '';
        if (!$class) {
            return null;
        }

        $exploded = explode('\\', $class);
        $config = array_merge($options, [
            'class' => array_pop($exploded),
            'namespace' => implode('\\', $exploded),
        ]);

        $dumper = self::buildDumper($container);
        return $dumper->dump($config);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return PhpDumper
     */
    private static function buildDumper(ContainerBuilder $container)
    {
        $dumper = new PhpDumper($container);
        if (class_exists(PhpDumper::class)) {
            $dumper->setProxyDumper(new ProxyDumper);
        }

        return $dumper;
    }

    /**
     * @param string $class
     *
     * @return ContainerInterface
     */
    private static function getCachedContainer($class)
    {
        if (!class_exists($class)) {
            throw new RuntimeException("DI Cached Container class not found: \"${class}\"");
        }

        return new $class;
    }

    /**
     * @param array $paths
     * @param string $class
     * @param array $options
     *
     * @return ContainerInterface
     */
    private static function buildContainer(array $paths, $class, array $options)
    {
        $container = static::buildDI($paths, !self::shouldBuildImmediately());

        if (self::shouldBuildImmediately()) {
            $cached = static::cacheDI($container, $options);

            $content = str_replace('<?php', '', $cached);
            eval($content);
            $container = new $class;
        }

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $paths
     *
     * @return LoaderInterface
     */
    private static function buildConfigLoader(ContainerBuilder $container, array $paths): LoaderInterface
    {
        $locator = new FileLocator($paths);

        $loaders = [
            new YamlFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
        ];

        $resolver = new LoaderResolver($loaders);

        return new DelegatingLoader($resolver);
    }

    /**
     * @return bool
     */
    private static function shouldBuildImmediately(): bool
    {
        return static::BUILD_AND_CACHE;
    }
}
