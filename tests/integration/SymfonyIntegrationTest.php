<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Hal\Core\DI;
use PHPUnit\Framework\TestCase;

class SymfonyIntegrationTest extends TestCase
{
    public function testContainerCompiles()
    {
        $root = realpath(__DIR__ . '/../../');
        putenv('HAL_DI_DISABLE_CACHE_ON=1');

        putenv('HAL_DB_USER=NA');
        putenv('HAL_DB_PASSWORD=NA');
        putenv('HAL_DB_HOST=NA');
        putenv('HAL_DB_PORT=0');
        putenv('HAL_DB_NAME=NA');
        putenv('HAL_DB_DRIVER=pdo_sqlite');

        $container = DI::buildDI([$root . '/config'], true);

        $this->assertInstanceOf(EntityManagerInterface::class, $container->get('doctrine.em.proxy'));
        $this->assertInstanceOf(EntityManagerInterface::class, $container->get('doctrine.em'));

        $this->assertInstanceOf(Cache::class, $container->get('doctrine.cache'));
        $this->assertInstanceOf(Cache::class, $container->get('doctrine.cache.memory'));
    }
}
