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
use Hal\Core\Entity\TargetTemplate;
use Hal\Core\Utility\PagedResultsTrait;
use Hal\Core\Utility\SortingTrait;

class TargetTemplateRepository extends EntityRepository
{
    use PagedResultsTrait;
    use SortingTrait;

    const DQL_GET_PAGED = <<<SQL_QUERY
   SELECT t
     FROM %s t
 ORDER BY t.type, t.name DESC
SQL_QUERY;

    /**
     * Get all templates, paged.
     *
     * @param int $limit
     * @param int $page
     *
     * @return Paginator
     */
    public function getPagedResults($limit = 50, $page = 0): Paginator
    {
        $dql = sprintf(self::DQL_GET_PAGED, TargetTemplate::class);
        return $this->getPaginator($dql, $limit, $page);
    }

    /**
     * Get all templates sorted into environments.
     *
     * @param Application|null $application
     *
     * @return array
     */
    public function getGroupedTemplates(?Application $application = null): array
    {
        $environments = $this->getEntityManager()
            ->getRepository(Environment::class)
            ->findAll();

        if ($application) {
            $findBy = ['application' => $application];
        } else {
            $findBy = [];
        }

        $templates = $this->findBy($findBy);

        usort($environments, $this->environmentSorter());
        usort($templates, $this->templateSorter());

        $sorted = [];
        foreach ($environments as $environment) {
            $sorted[$environment->id()] = [
                'environment' => $environment,
                'templates' => [],
            ];
        }

        foreach ($templates as $template) {
            $id = $template->environment()->id();
            $sorted[$id]['templates'][] = $template;
        }

        return array_filter($sorted, function ($e) {
            return count($e['templates']) !== 0;
        });
    }
}
