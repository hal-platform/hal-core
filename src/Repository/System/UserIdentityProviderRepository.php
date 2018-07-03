<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Repository\System;

use Doctrine\ORM\EntityRepository;
use Hal\Core\Entity\System\UserIdentityProvider;
use Hal\Core\Entity\User\UserIdentity;

class UserIdentityProviderRepository extends EntityRepository
{
    const DQL_USER_COUNT_FOR_IDP = <<<SQL_QUERY
   SELECT p.id, COUNT(p.id) as idents
     FROM %s i
     JOIN %s p WITH p = i.provider
 GROUP BY p.id
SQL_QUERY;

    /**
     * @return array
     */
    public function getUserCounts(): array
    {
        $dql = sprintf(self::DQL_USER_COUNT_FOR_IDP, UserIdentity::class, UserIdentityProvider::class);

        $result = $this->getEntityManager()
            ->createQuery($dql)
            ->getResult();

        $counts = [];
        foreach ($result as ['id' => $id, 'idents' => $users]) {
            $counts[$id] = $users;
        }

        return $counts;
    }
}
