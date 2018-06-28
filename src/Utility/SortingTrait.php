<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Utility;

use Hal\Core\Entity\Application;
use Hal\Core\Entity\Environment;
use Hal\Core\Entity\Organization;
use Hal\Core\Entity\Target;
use Hal\Core\Entity\TargetTemplate;
use Hal\Core\Entity\User\UserPermission;

/**
 * Provides sorting methods for entities. Designed to be used with usort
 *
 * - targetSorter
 * - environmentSorter
 * - applicationSorter
 * - organizationSorter
 */
trait SortingTrait
{
    private $sortingHelperEnvironmentOrder = [
        'dev' => 0,
        'staging' => 1,
        'test' => 2,
        'beta' => 3,
        'prod' => 4,
        'production' => 5,
    ];

    /**
     * @return callable
     */
    public function targetSorter()
    {
        return function (Target $a, Target $b) {
            $typeA = $a->formatType();
            $typeB = $b->formatType();

            $nameA = $a->name();
            $nameB = $b->name();

            if ($typeA === $typeB) {
                $typeA = $nameA;
                $typeB = $nameB;
            }

            return strcasecmp($typeA, $typeB);
        };
    }

    /**
     * @return callable
     */
    public function environmentSorter()
    {
        $order = $this->sortingHelperEnvironmentOrder;

        return function (Environment $a, Environment $b) use ($order) {

            $aName = strtolower($a->name());
            $bName = strtolower($b->name());

            $aOrder = isset($order[$aName]) ? $order[$aName] : 999;
            $bOrder = isset($order[$bName]) ? $order[$bName] : 999;

            if ($aOrder === $bOrder) {
                return 0;
            }

            return ($aOrder > $bOrder) ? 1 : -1;
        };
    }

    /**
     * @return callable
     */
    public function applicationSorter()
    {
        return function (Application $a, Application $b) {
            return strcasecmp($a->name(), $b->name());
        };
    }

    /**
     * @return callable
     */
    public function organizationSorter()
    {
        return function (Organization $a, Organization $b) {
            return strcasecmp($a->name(), $b->name());
        };
    }

    /**
     * @return callable
     */
    private function permissionSorter()
    {
        return function (UserPermission $a, UserPermission $b) {
            $aName = $a->user()->name();
            $bName = $b->user()->name();

            if ($aName === $bName) {
                strcasecmp($a->type(), $b->type());
            }

            return strcasecmp($aName, $bName);
        };
    }


    /**
     * @return callable
     */
    public function templateSorter()
    {
        return function (TargetTemplate $a, TargetTemplate $b) {
            return strcasecmp($a->name(), $b->name());
        };
    }
}
