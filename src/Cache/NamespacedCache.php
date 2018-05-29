<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Cache;

use Psr\SimpleCache\CacheInterface;

class NamespacedCache implements CacheInterface
{
    private const DEFAULT_DELIMITER = '.';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @param CacheInterface $cache
     * @param string $namespace
     * @param string $delimiter
     */
    public function __construct(CacheInterface $cache, string $namespace, string $delimiter = self::DEFAULT_DELIMITER)
    {
        $this->cache = $cache;
        $this->namespace = $namespace;
        $this->delimiter = $delimiter;
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     */
    public function get($key, $default = null)
    {
        return $this->cache->get($this->buildCacheKey($key), $default);
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->cache->set($this->buildCacheKey($key), $value, $ttl);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function delete($key)
    {
        return $this->cache->delete($this->buildCacheKey($key));
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        return $this->cache->clear();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     */
    public function getMultiple($keys, $default = null)
    {
        array_walk($keys, function (&$v) {
            $v = $this->buildCacheKey($v);
        });

        return $this->cache->getMultiple($keys, $default);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     */
    public function setMultiple($values, $ttl = null)
    {
        $new = [];

        array_walk($values, function ($v, $key) use (&$new) {
            $new[$this->buildCacheKey($key)] = $v;
        });

        return $this->cache->setMultiple($new, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     */
    public function deleteMultiple($keys)
    {
        array_walk($keys, function (&$v) {
            $v = $this->buildCacheKey($v);
        });

        return $this->cache->deleteMultiple($keys);
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->cache->has($this->buildCacheKey($key));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function buildCacheKey($key): string
    {
        return $this->namespace . $this->delimiter . $key;
    }
}
