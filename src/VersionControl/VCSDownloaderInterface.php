<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl;

use Hal\Core\Entity\Application;

interface VCSDownloaderInterface
{
    /**
     * Download source code from version control provider.
     *
     * @todo update this to easily allow git clones (targetFile may be directory?)
     *
     * @param Application $application
     * @param string $commit
     * @param string $targetFile
     *
     * @throws VCSException
     *
     * @return bool
     */
    public function download(Application $application, string $commit, string $targetFile): bool;
}
