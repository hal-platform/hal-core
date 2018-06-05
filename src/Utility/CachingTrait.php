<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Utility;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;

/**
 * Provide convenience methods and wrappers for caching in repositories.
 *
 * This trait gracefully handles cases where no cache is set.
 *
 * NEVER access properties set in traits directly from within the consumer of the trait!
 *
 * For example:
 * Use $this->cache()
 * Not $this->cache
 */
trait CachingTrait
{
    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var int|null
     */
    private $cacheTTL;

    /**
     * @return CacheInterface
     */
    private function cache(): CacheInterface
    {
        if (!$this->cache) {
            $this->cache = new ArrayCache(0, false);
        }

        return $this->cache;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    private function getFromCache(string $key)
    {
        return $this->cache()->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param mixed $ttl
     *
     * @return void
     */
    private function setToCache(string $key, $value, $ttl = null): void
    {
        $params = func_get_args();

        // Use default TTL if none is provided
        if ($this->cacheTTL && $ttl === null) {
            array_push($params, $this->cacheTTL);
        }

        call_user_func_array([$this->cache(), 'set'], $params);
    }

    /**
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param int $ttl
     *
     * @return void
     */
    public function setCacheTTL(int $ttl): void
    {
        $this->cacheTTL = $ttl;
    }
}
