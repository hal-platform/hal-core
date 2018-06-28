<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\Setup;
use Hal\Core\Cache\NamespacedCache;
use Hal\Core\Database\DoctrineUtility\DoctrineConfigurator;
use Hal\Core\RandomGenerator;
use Hal\Core\Type\CompressedJSONArrayType;
use Hal\Core\Type\TimePointType;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\SimpleCacheAdapter;
use Symfony\Component\Cache\DoctrineProvider;
use Symfony\Component\Cache\Simple\ArrayCache;

return function (ContainerConfigurator $container) {

    $p = $container->parameters();
    $s = $container->services();

    $p
        ->set('env(HAL_ORM_CACHE)',      'memory')
        ->set('env(HAL_ORM_DEVMODE_ON)',   false)
        ->set('env(HAL_ORM_PROXY_DIR)',  __DIR__ . '/../.doctrine')
        ->set('env(HAL_ORM_CONFIG_DIR)', __DIR__ . '/doctrine')

        ->set('doctrine.connection', [
            'user'     => '%env(HAL_DB_USER)%',
            'password' => '%env(HAL_DB_PASSWORD)%',
            'host'     => '%env(HAL_DB_HOST)%',
            'port'     => '%env(int:HAL_DB_PORT)%',
            'dbname'   => '%env(HAL_DB_NAME)%',
            'driver'   => '%env(HAL_DB_DRIVER)%'
        ])

        ->set('doctrine.cache.lvl2_enabled',        true)
        ->set('doctrine.cache.lvl2_ttl',            600)
        ->set('doctrine.cache.lvl2_lock',           60)
        ->set('doctrine.cache.ttl',                 60)
        ->set('doctrine.cache.namespace',           'doctrine')
        ->set('doctrine.cache.namespace_delimiter', '.')

        ->set('doctrine.config.namespace_config_map', [
            '%env(HAL_ORM_CONFIG_DIR)%' => 'Hal\Core\Entity'
        ])

        ->set('doctrine.config.custom_types', [
            CompressedJSONArrayType::class,
            TimePointType::class
        ])

        ->set('doctrine.proxy.connection', [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ])
    ;

    $s
        ->defaults()
            ->private();

    // Doctrine Proxy
    $s
        ->set('doctrine.em.proxy', EntityManager::class)
            ->factory([EntityManager::class, 'create'])
            ->configurator([ref(DoctrineConfigurator::class), 'configure'])
            ->arg('$connection', '%doctrine.proxy.connection%')
            ->arg('$config', ref('doctrine.em.proxy.config'))
            ->arg('$eventManager', ref(EventManager::class))
            ->public()

        ->set('doctrine.em.proxy.config', Configuration::class)
            ->factory([Setup::class, 'createConfiguration'])
            ->arg('$isDevMode', '%env(bool:HAL_ORM_DEVMODE_ON)%')
            ->arg('$proxyDir', '%env(HAL_ORM_PROXY_DIR)%')
            ->call('setMetadataDriverImpl', [ref(YamlDriver::class)])
    ;

    // Doctrine Entity Manager
    $s
        ->set(EntityManagerInterface::class)
            ->factory([EntityManager::class, 'create'])
            ->configurator([ref(DoctrineConfigurator::class), 'configure'])
            ->arg('$connection', '%doctrine.connection%')
            ->arg('$config', ref('doctrine.config'))
            ->arg('$eventManager', ref(EventManager::class))
            ->public()
            ->lazy()

        ->set(DoctrineConfigurator::class)
            ->arg('$typeClasses', '%doctrine.config.custom_types%')
        ->set(EventManager::class)
    ;

    // Doctrine Configuration
    $s
        ->set('doctrine.config', Configuration::class)
            ->factory([Setup::class, 'createConfiguration'])
            ->arg('$isDevMode', '%env(bool:HAL_ORM_DEVMODE_ON)%')
            ->arg('$proxyDir', '%env(HAL_ORM_PROXY_DIR)%')
            ->arg('$cache', ref(DoctrineProvider::class))
            ->call('setMetadataDriverImpl', [ref(YamlDriver::class)])
            ->call('setSecondLevelCacheEnabled', ['%doctrine.cache.lvl2_enabled%'])
            ->call('setSecondLevelCacheConfiguration', [ref(CacheConfiguration::class)])

        ->set(YamlDriver::class)
            ->arg('$locator', ref(SymfonyFileLocator::class))
            ->arg('$fileExtension', '.orm.yaml')
            ->call('setGlobalBasename', ['global'])

        ->set(SymfonyFileLocator::class)
            ->arg('$prefixes', '%doctrine.config.namespace_config_map%')
            ->arg('$fileExtension', '.orm.yaml')
            ->arg('$nsSeparator', '/')
    ;

    // Doctrine Cache
    $s
        ->set('doctrine.cache', CacheInterface::class)
            ->factory([ref('service_container'), 'get'])
            ->arg('$id', 'doctrine.cache.%env(HAL_ORM_CACHE)%')
            ->public()

        ->set('doctrine.cache.memory', ArrayCache::class)
            ->call('setLogger', [ref('doctrine.cache.blackhole_logger')])
            ->public()

        ->set(DoctrineProvider::class)
            ->arg('$pool', ref('doctrine.cache_psr6_to_psr16'))

        ->set('doctrine.cache_psr6_to_psr16', SimpleCacheAdapter::class)
            ->arg('$pool', ref('doctrine.cache_namespaced'))
            ->call('setLogger', [ref('doctrine.cache.blackhole_logger')])

        ->set('doctrine.cache_namespaced', NamespacedCache::class)
            ->arg('$cache', ref('doctrine.cache'))
            ->arg('$namespace', '%doctrine.cache.namespace%')
            ->arg('$delimiter', '%doctrine.cache.namespace_delimiter%')

        ->set('doctrine.cache.blackhole_logger', NullLogger::class)

        ->set(CacheConfiguration::class)
            ->call('setCacheFactory', [ref(DefaultCacheFactory::class)])

        ->set(DefaultCacheFactory::class)
            ->arg('$cacheConfig', ref(RegionsConfiguration::class))
            ->arg('$cache', ref(DoctrineProvider::class))

        ->set(RegionsConfiguration::class)
            ->arg('$defaultLifetime', '%doctrine.cache.lvl2_ttl%')
            ->arg('$defaultLockLifetime', '%doctrine.cache.lvl2_lock%')
    ;

    // Doctrine Helpers
    $s
        ->set(RandomGenerator::class)
    ;
};
