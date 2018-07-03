<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Hal\Core\Entity\Application;
use Hal\Core\Entity\Environment;
use Hal\Core\Entity\Target;
use Hal\Core\Utility\PagedResultsTrait;
use Hal\Core\Utility\SortingTrait;

class TargetRepository extends EntityRepository
{
    use PagedResultsTrait;
    use SortingTrait;

    const DQL_GET_PAGED = <<<SQL_QUERY
   SELECT t
     FROM %s t
 ORDER BY t.type, t.name DESC
SQL_QUERY;

    /**
     * Get all targets, paged.
     *
     * @param int $limit
     * @param int $page
     *
     * @return Paginator
     */
    public function getPagedResults($limit = 50, $page = 0): Paginator
    {
        $dql = sprintf(self::DQL_GET_PAGED, Target::class);
        return $this->getPaginator($dql, $limit, $page);
    }

    /**
     * Get all targets sorted into environments.
     *
     * @param Application|null $application
     *
     * @return array
     */
    public function getGroupedTargets(?Application $application = null): array
    {
        $environments = $this->getEntityManager()
            ->getRepository(Environment::class)
            ->findAll();

        if ($application) {
            $findBy = ['application' => $application];
        } else {
            $findBy = [];
        }

        $targets = $this->findBy($findBy);

        usort($environments, $this->environmentSorter());
        usort($targets, $this->targetSorter());

        $sorted = [];
        foreach ($environments as $environment) {
            $sorted[$environment->id()] = [
                'environment' => $environment,
                'targets' => [],
            ];
        }

        foreach ($targets as $target) {
            $id = $target->environment()->id();
            $sorted[$id]['targets'][] = $target;
        }

        return array_filter($sorted, function ($e) {
            return count($e['targets']) !== 0;
        });
    }
}
