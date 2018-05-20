<?php

namespace Hal\Core\Seeds;

use Doctrine\ORM\EntityManagerInterface;
use Hal\Core\Entity\Application;
use Hal\Core\Entity\Environment;
use Hal\Core\Entity\System\UserIdentityProvider;
use Hal\Core\Entity\System\VersionControlProvider;
use Hal\Core\Entity\User;
use Hal\Core\Entity\User\UserIdentity;
use Hal\Core\Entity\User\UserPermission;

/**
 * Run postgres within docker (ephemeral)
 * > docker run -d -p 80:80 -e "PGADMIN_DEFAULT_EMAIL=hal" -e "PGADMIN_DEFAULT_PASSWORD=hal" dpage/pgadmin4
 * > docker run -d -p 5432:5432 -e "POSTGRES_DB=hal" -e "POSTGRES_USER=hal" postgres:9.6
 */

return function(EntityManagerInterface $em) {
    $password = getenv('HAL_ADMIN_PASSWORD');
    $ghToken = getenv('HAL_GITHUB_TOKEN');
    $ghRepo = getenv('HAL_EXAMPLE_PROJECT');

    [$ghOwner, $ghRepo] = explode('/', getenv('HAL_EXAMPLE_PROJECT'));

    if (!$password || !$ghToken || !$ghOwner || !$ghRepo) {
        echo "Please provide the following environment variables:\n";
        echo "- \$HAL_ADMIN_PASSWORD\n";
        echo "- \$HAL_GITHUB_TOKEN\n\n";
        echo "- \$HAL_EXAMPLE_PROJECT\n\n";
        exit(1);
    }

    if ($em->getRepository(UserIdentityProvider::class)->findAll()) {
        echo "Cannot bootstrap data. IDP already present and configured.\n\n";
        exit(1);
    }

    // Initial Bootstrap

    $idp =(new UserIdentityProvider)
        ->withName('Administrator Access')
        ->withType('internal');

    $vcs = (new VersionControlProvider)
        ->withName('GitHub.com')
        ->withType('gh')
        ->withParameter('gh.token', $ghToken);

    // Bootstrap Environments

    $environment1 = (new Environment)
        ->withName('staging')
        ->withIsProduction(false);
    $environment2 = (new Environment)
        ->withName('prod')
        ->withIsProduction(true);

    // User

    $user = (new User)
        ->withName('System Administrator');

    $identity = (new UserIdentity)
        ->withProviderUniqueID('admin')
        ->withParameter('internal.setup_token', null)
        ->withParameter('internal.setup_token_expiry', null)
        ->withParameter('internal.password', password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]))
        ->withProvider($idp)
        ->withUser($user);

    $permissions = (new UserPermission)
        ->withType('super')
        ->withUser($user);

    // Application

    $application = (new Application)
        ->withName('Example Project')
        ->withParameter('gh.owner', $ghOwner)
        ->withParameter('gh.repo', $ghRepo)
        ->withProvider($vcs);

    // Save

    $em->persist($idp);
    $em->persist($vcs);
    $em->persist($environment1);
    $em->persist($environment2);
    $em->persist($user);
    $em->persist($identity);
    $em->persist($permissions);
    $em->persist($application);

    $em->flush();
};
