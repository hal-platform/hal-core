<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl;

use Hal\Core\Entity\System\VersionControlProvider;
use Hal\Core\Type\VCSProviderEnum;
use Hal\Core\Validation\ValidatorErrorTrait;

class VCSFactory
{
    use ValidatorErrorTrait;

    const ERR_VCS_MISCONFIGURED = 'No valid Version Control Provider was found. Hal may be misconfigured.';

    /**
     * @var array
     */
    private $adapters;

    /**
     * @param array $adapters
     */
    public function __construct(array $adapters = [])
    {
        $this->adapters = [];

        foreach ($adapters as $type => $adapter) {
            $this->addAdapter($type, $adapter);
        }
    }

    /**
     * @param string $type
     * @param VCSAdapterInterface $adapter
     *
     * @return void
     */
    public function addAdapter(string $type, VCSAdapterInterface $adapter): void
    {
        $this->adapters[$type] = $adapter;
    }

    /**
     * @param VersionControlProvider $vcs
     *
     * @return VCSClientInterface|null
     */
    public function authenticate(VersionControlProvider $vcs): ?VCSClientInterface
    {
        $adapter = $this->adapters[$vcs->type()] ?? null;
        if (!$adapter) {
            $this->addError(self::ERR_VCS_MISCONFIGURED);
            return null;
        }

        $client = $adapter->buildClient($vcs);

        if ($client instanceof VCSClientInterface) {
            return $client;
        }

        $this->importErrors($adapter->errors());
        return null;
    }

    /**
     * @param VersionControlProvider $vcs
     *
     * @return VCSDownloaderInterface|null
     */
    public function downloader(VersionControlProvider $vcs):? VCSDownloaderInterface
    {
        $adapter = $this->adapters[$vcs->type()] ?? null;
        if (!$adapter) {
            $this->addError(self::ERR_VCS_MISCONFIGURED);
            return null;
        }

        $downloader = $adapter->buildDownloader($vcs);

        if ($downloader instanceof VCSDownloaderInterface) {
            return $downloader;
        }

        $this->importErrors($adapter->errors());
        return null;
    }
}
