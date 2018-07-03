<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl\GitHub;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\LazyOpenStream;
use Hal\Core\Entity\Application;
use Hal\Core\VersionControl\VCSDownloaderInterface;
use Hal\Core\VersionControl\VCSException;

class GitHubDownloader implements VCSDownloaderInterface
{
    const ERR_APP_MISCONFIGURED = 'Application version control is misconfigured.';

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @param ClientInterface $guzzle
     */
    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Get content of archives in a repository
     *
     * @link http://developer.github.com/v3/repos/contents/
     *
     * @param Application $application
     * @param string $commit
     * @param string $targetFile
     *
     * @throws VCSException
     *
     * @return bool
     */
    public function download(Application $application, string $commit, string $targetFile): bool
    {
        $username = $application->parameter('gh.owner');
        $repository = $application->parameter('gh.repo');

        if (!$username || !$repository) {
            throw new VCSException(self::ERR_APP_MISCONFIGURED);
        }

        $endpoint = implode('/', [
            'repos',
            rawurlencode($username),
            rawurlencode($repository),
            'tarball',
            rawurlencode($commit),
        ]);

        $options = [
            'sink' => new LazyOpenStream($targetFile, 'w+'),
        ];

        try {
            $response = $this->guzzle->request('GET', $endpoint, $options);
        } catch (Exception $e) {
            throw new VCSException($e->getMessage(), $e->getCode(), $e);
        }

        return ($response->getStatusCode() === 200);
    }
}
