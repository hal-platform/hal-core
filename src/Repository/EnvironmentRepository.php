<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Repository;

use Doctrine\ORM\EntityRepository;
use Hal\Core\Entity\Application;
use Hal\Core\Entity\Environment;
use Hal\Core\Entity\Target;
use Hal\Core\Utility\SortingTrait;

class EnvironmentRepository extends EntityRepository
{
    use SortingTrait;

    const ENV_QUERY_REGION = 'environment_region_app_%s';

    const DQL_BY_APPLICATION = <<<SQL_QUERY
   SELECT env
     FROM %s target

     JOIN %s env WITH env = target.environment

    WHERE target.application = :application
SQL_QUERY;

    /**
     * Get all buildable environments for an application.
     *
     * @param Application $application
     *
     * @return Environment[]
     */
    public function getBuildableEnvironmentsByApplication(Application $application)
    {
        $region = sprintf(self::ENV_QUERY_REGION, $application->id());

        $dql = sprintf(self::DQL_BY_APPLICATION, Target::class, Environment::class);

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setCacheable(true)
            ->setCacheRegion($region)
            ->setParameter('application', $application);

        $environments = $query->getResult();

        usort($environments, $this->environmentSorter());

        return $environments;
    }

    /**
     * Clear cache for buildable environments for an application.
     *
     * @param Application $application
     *
     * @return void
     */
    public function clearBuildableEnvironmentsByApplication(Application $application)
    {
        $region = sprintf(self::ENV_QUERY_REGION, $application->id());

        $cache = $this
            ->getEntityManager()
            ->getCache();

        $cache
            ->getQueryCache($region)
            ->clear();
    }

    /**
     * @param callable|null $sorter
     *
     * @return Environment[]
     */
    public function getAllEnvironmentsSorted(?callable $sorter = null)
    {
        $environments = $this->findAll();

        if ($sorter) {
            usort($environments, $sorter);
        } else {
            usort($environments, $this->environmentSorter());
        }

        return $environments;
    }
}
