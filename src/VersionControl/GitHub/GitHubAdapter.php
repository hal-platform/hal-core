<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl\GitHub;

use Github\Client;
use Github\HttpClient\Builder;
use Github\ResultPager;
use GuzzleHttp\Client as GuzzleClient;
use Hal\Core\Entity\System\VersionControlProvider;
use Hal\Core\Parameters;
use Hal\Core\Type\VCSProviderEnum;
use Hal\Core\Utility\CachingTrait;
use Hal\Core\Validation\ValidatorErrorTrait;
use Hal\Core\VersionControl\VCSAdapterInterface;
use Hal\Core\VersionControl\VCSClientInterface;
use Hal\Core\VersionControl\VCSDownloaderInterface;

class GitHubAdapter implements VCSAdapterInterface
{
    use CachingTrait;
    use ValidatorErrorTrait;

    const CACHE_KEY_TEMPLATE = 'vcs_clients.%s_%s';
    const DEFAULT_GITHUB_API_URL = 'https://api.github.com';
    const ERR_VCS_MISCONFIGURED = 'GitHub.com Version Control Provider is misconfigured.';

    /**
     * @var Builder
     */
    private $httpClientBuilder;

    /**
     * @var string
     */
    private $baseURL;

    /**
     * @var array
     */
    private $defaultGuzzleOptions;

    /**
     * @param Builder $httpClientBuilder
     * @param string $githubBaseURL
     */
    public function __construct(Builder $httpClientBuilder, string $githubBaseURL = self::DEFAULT_GITHUB_API_URL)
    {
        $this->httpClientBuilder = $httpClientBuilder;
        $this->baseURL = rtrim($githubBaseURL, '/');
        $this->defaultGuzzleOptions = [];
    }

    /**
     * @var array
     */
    public function setDefaultDownloaderOptions(array $options)
    {
        $this->defaultGuzzleOptions = $options;
    }

    /**
     * @param VersionControlProvider $vcs
     *
     * @return VCSClientInterface|null
     */
    public function buildClient(VersionControlProvider $vcs): ?VCSClientInterface
    {
        if ($vcs->type() !== VCSProviderEnum::TYPE_GITHUB) {
            $this->addError(self::ERR_VCS_MISCONFIGURED);
            return null;
        }

        $key = sprintf(self::CACHE_KEY_TEMPLATE, $vcs->type(), $vcs->id());

        $client = $this->getFromCache($key);
        if ($client instanceof VCSClientInterface) {
            return $client;
        }

        $token = $vcs->parameter(Parameters::VCS_GH_TOKEN);
        if (!$token) {
            $this->addError(self::ERR_VCS_MISCONFIGURED);
            return null;
        }

        $sdk = new Client($this->httpClientBuilder, null);
        $sdk->authenticate($token, null, Client::AUTH_HTTP_TOKEN);
        $client = new GitHubClient($sdk, new ResultPager($sdk));

        // Pass along the this cache. Ideally this is configurable.
        $client->setCache($this->cache());

        // Should only be in memory
        $this->setToCache($key, $client, 60);

        return $client;
    }

    /**
     * @param VersionControlProvider $vcs
     *
     * @return VCSDownloaderInterface|null
     */
    public function buildDownloader(VersionControlProvider $vcs): ?VCSDownloaderInterface
    {
        if ($vcs->type() !== VCSProviderEnum::TYPE_GITHUB) {
            $this->addError(self::ERR_VCS_MISCONFIGURED);
            return null;
        }

        $baseURL = $this->baseURL;
        $token = $vcs->parameter(Parameters::VCS_GH_TOKEN);
        if (!$baseURL || !$token) {
            $this->addError(self::ERR_VCS_MISCONFIGURED);
            return null;
        }

        $options = $this->defaultGuzzleOptions + [
            'base_uri' => $baseURL,
            'headers' => [
                'Authorization' => sprintf('token %s', $token),
            ],

            'allow_redirects' => true,
            'connect_timeout' => 5,
            'timeout' => 300, # 5 minutes seems like a reasonable amount of time?
            'http_errors' => false,
        ];

        $guzzle = new GuzzleClient($options);

        return new GitHubDownloader($guzzle);
    }
}
