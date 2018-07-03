<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Repository\JobType;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Hal\Core\Entity\Application;
use Hal\Core\Entity\Environment;
use Hal\Core\Entity\JobType\Build;
use Hal\Core\Utility\PagedResultsTrait;

class BuildRepository extends EntityRepository
{
    use PagedResultsTrait;

    const DQL_BY_APPLICATION = <<<SQL_QUERY
   SELECT build
     FROM %s build
    WHERE build.application = :application
 ORDER BY build.created DESC
SQL_QUERY;
    const DQL_BY_APPLICATION_WITH_REF_FILTER = <<<SQL_QUERY
   SELECT build
     FROM %s build
    WHERE build.application = :application
      AND (build.reference = :ref OR build.commit = :ref)
 ORDER BY build.created DESC
SQL_QUERY;

    const DQL_BY_APPLICATION_AND_ENV = <<<SQL_QUERY
   SELECT build
     FROM %s build
    WHERE build.application = :application
      AND build.environment = :environment
 ORDER BY build.created DESC
SQL_QUERY;
    const DQL_BY_APPLICATION_AND_ENV_WITH_REF_FILTER = <<<SQL_QUERY
   SELECT build
     FROM %s build
    WHERE build.application = :application
      AND build.environment = :environment
      AND (build.reference = :ref OR build.commit = :ref)
 ORDER BY build.created DESC
SQL_QUERY;
    const DQL_ALL = <<<SQL_QUERY
   SELECT build
     FROM %s build
 ORDER BY build.created DESC
SQL_QUERY;

    /**
     * Get all builds for an application, paged.
     *
     * @param Application $application
     * @param int $limit
     * @param int $page
     * @param string $filter
     *
     * @return Paginator
     */
    public function getByApplication(Application $application, $limit = 25, $page = 0, $filter = '')
    {
        $template = ($filter) ? self::DQL_BY_APPLICATION_WITH_REF_FILTER : self::DQL_BY_APPLICATION;
        $dql = sprintf($template, Build::class);

        $params = ['application' => $application];
        if ($filter) {
            $params['ref'] = $filter;
        }

        return $this->getPaginator($dql, $limit, $page, $params);
    }

    /**
     * Get all builds for an application and environment, paged.
     *
     * @param Application $application
     * @param Environment $environment
     * @param int $limit
     * @param int $page
     * @param string $filter
     *
     * @return Paginator
     */
    public function getByApplicationForEnvironment(Application $application, Environment $environment, $limit = 25, $page = 0, $filter = '')
    {
        $template = ($filter) ? self::DQL_BY_APPLICATION_AND_ENV_WITH_REF_FILTER : self::DQL_BY_APPLICATION_AND_ENV;
        $dql = sprintf($template, Build::class);

        $params = [
            'application' => $application,
            'environment' => $environment,
        ];

        if ($filter) {
            $params['ref'] = $filter;
        }

        return $this->getPaginator($dql, $limit, $page, $params);
    }

    /**
     * @param int $limit
     * @param int $page
     *
     * @return Paginator
     */
    public function getPagedResults($limit = 25, $page = 0)
    {
        $template = self::DQL_ALL;
        $dql = sprintf($template, Build::class);

        return $this->getPaginator($dql, $limit, $page, []);
    }
}
