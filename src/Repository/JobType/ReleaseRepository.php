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
use Hal\Core\Entity\JobType\Release;
use Hal\Core\Entity\Target;
use Hal\Core\Type\JobStatusEnum;
use Hal\Core\Utility\PagedResultsTrait;

class ReleaseRepository extends EntityRepository
{
    use PagedResultsTrait;

    const DQL_ROLLBACKS = <<<SQL_QUERY
   SELECT release
     FROM %s release
     JOIN release.target target
     JOIN release.build build
    WHERE
        release.target = :target AND
        release.status = :release_status AND
        build.status = :build_status
 ORDER BY release.created DESC
SQL_QUERY;

    const DQL_BY_TARGET = <<<SQL_QUERY
   SELECT release
     FROM %s release
     JOIN release.target target
    WHERE
        release.target = :target
 ORDER BY release.created DESC
SQL_QUERY;

    const DQL_BY_APPLICATION = <<<SQL_QUERY
   SELECT release
     FROM %s release
     JOIN release.build build
    WHERE release.application = :application
 ORDER BY release.created DESC
SQL_QUERY;
    const DQL_BY_APPLICATION_WITH_REF_FILTER = <<<SQL_QUERY
   SELECT release
     FROM %s release
     JOIN release.build build
    WHERE release.application = :application
      AND (build.reference = :ref OR build.commit = :ref)
 ORDER BY release.created DESC
SQL_QUERY;

    const DQL_BY_APPLICATION_AND_ENV = <<<SQL_QUERY
   SELECT release
     FROM %s release
     JOIN release.build build
    WHERE release.application = :application
      AND build.environment = :environment
 ORDER BY release.created DESC
SQL_QUERY;
    const DQL_BY_APPLICATION_AND_ENV_WITH_REF_FILTER = <<<SQL_QUERY
   SELECT release
     FROM %s release
     JOIN release.build build
    WHERE release.application = :application
      AND build.environment = :environment
      AND (build.reference = :ref OR build.commit = :ref)
 ORDER BY release.created DESC
SQL_QUERY;
    const DQL_ALL = <<<SQL_QUERY
   SELECT release
     FROM %s release
 ORDER BY release.created DESC
SQL_QUERY;

    /**
     * Get all releases that can be used as a rollback, paged.
     *
     * @param Target $target
     *
     * @param int $limit
     * @param int $page
     *
     * @return Paginator
     */
    public function getAvailableRollbacksByTarget(Target $target, $limit = 50, $page = 0)
    {
        $dql = sprintf(self::DQL_ROLLBACKS, Release::class);
        $params = [
            'target' => $target,
            'release_status' => JobStatusEnum::TYPE_SUCCESS,
            'build_status' => JobStatusEnum::TYPE_SUCCESS,
        ];

        return $this->getPaginator($dql, $limit, $page, $params);
    }

    /**
     * Get all releases to a target, paged.
     *
     * @param Target $target
     *
     * @param int $limit
     * @param int $page
     *
     * @return Paginator
     */
    public function getByTarget(Target $target, $limit = 50, $page = 0)
    {
        $dql = sprintf(self::DQL_BY_TARGET, Release::class);
        $params = ['target' => $target];

        return $this->getPaginator($dql, $limit, $page, $params);
    }

    /**
     * Get all releases for an application, paged.
     *
     * @param Application $application
     *
     * @param int $limit
     * @param int $page
     *
     * @param string $filter
     *
     * @return Paginator
     */
    public function getByApplication(Application $application, $limit = 50, $page = 0, $filter = '')
    {
        $template = ($filter) ? self::DQL_BY_APPLICATION_WITH_REF_FILTER : self::DQL_BY_APPLICATION;
        $dql = sprintf($template, Release::class);

        $params = ['application' => $application];
        if ($filter) {
            $params['ref'] = $filter;
        }

        return $this->getPaginator($dql, $limit, $page, $params);
    }

    /**
     * Get all releases for an application and environment, paged.
     *
     * @param Application $application
     * @param Environment $environment
     *
     * @param int $limit
     * @param int $page
     *
     * @param string $filter
     *
     * @return Paginator
     */
    public function getByApplicationForEnvironment(Application $application, Environment $environment, $limit = 50, $page = 0, $filter = '')
    {
        $template = ($filter) ? self::DQL_BY_APPLICATION_AND_ENV_WITH_REF_FILTER : self::DQL_BY_APPLICATION_AND_ENV;
        $dql = sprintf($template, Release::class);

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
        $dql = sprintf($template, Release::class);

        return $this->getPaginator($dql, $limit, $page, []);
    }
}
