<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl;

use Hal\Core\Entity\System\VersionControlProvider;

interface VCSAdapterInterface
{
    /**
     * @param VersionControlProvider $vcs
     *
     * @throws VersionControlException
     *
     * @return VCSClientInterface|null
     */
    public function buildClient(VersionControlProvider $vcs): ?VCSClientInterface;

    /**
     * @param VersionControlProvider $vcs
     *
     * @throws VersionControlException
     *
     * @return VCSDownloaderInterface|null
     */
    public function buildDownloader(VersionControlProvider $vcs): ?VCSDownloaderInterface;
}
