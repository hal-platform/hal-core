<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl\GitHub;

use Github\Client;
use Github\Exception\RuntimeException;
use Github\ResultPager;
use Hal\Core\Utility\CachingTrait;
use Hal\Core\VersionControl\RefSortingTrait;
use Hal\Core\VersionControl\VCSClientInterface;
use Http\Client\Exception\RequestException;

class GitHubClient implements VCSClientInterface
{
    use CachingTrait;
    use RefSortingTrait;

    const CACHE_KEY_TEMPLATE = 'vcs_clients.gh.%s_%s';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResultPager
     */
    private $pager;

    /**
     * @var RefResolver
     */
    private $resolver;

    /**
     * @param Client $client
     * @param ResultPager $pager
     */
    public function __construct(Client $client, ResultPager $pager)
    {
        $this->client = $client;
        $this->pager = $pager;

        $this->resolver = new RefResolver($this->client);
    }

    /**
     * Resolve a git reference in the following format.
     *
     * Tag: tag/(tag name)
     * Pull: pull/(pull request number)
     * Commit: (commit hash){40}
     * Branch: (branch name)
     *
     * Will return an array of [reference, commit] or null on failure
     *
     * @param string $user
     * @param string $repo
     * @param string $reference
     *
     * @return array|null
     */
    public function resolveRef(string $user, string $repo, string $reference): ?array
    {
        $key = sprintf(self::CACHE_KEY_TEMPLATE, md5($user . $repo), 'ref_' . md5($reference));

        if (($latest = $this->getFromCache($key)) !== null) {
            return $latest;
        }

        $resolved = $this->resolver->resolve($user, $repo, $reference);

        $this->setToCache($key, $resolved, 30);
        return $resolved;
    }

    /**
     * Resolve a git reference type in the following format.
     *
     * Tag: tag/(tag name)
     * Pull: pull/(pull request number)
     * Commit: (commit hash){40}
     * Branch: (branch name)
     *
     * Will return an array of ['type', $ref] or fallback to ['branch', $ref]
     *
     * @param string $reference
     *
     * @return array
     */
    public function resolveRefType(string $reference): ?array
    {
        return $this->resolver->resolveRefType($reference);
    }

    /**
     * @param string $user
     * @param string $repo
     *
     * @return string
     */
    public function urlForRepository(string $user, string $repo): string
    {
        $key = sprintf(self::CACHE_KEY_TEMPLATE, md5($user . $repo), 'repo_url');

        if (($latest = $this->getFromCache($key)) !== null) {
            return $latest;
        }

        $api = $this->client->api('repo');
        $params = [$user, $repo];

        $repo = $this->callGitHub([$api, 'show'], $params);

        $url = $repo['html_url'] ?? '';

        $this->setToCache($key, $url, 30);
        return $url;
    }

    /**
     * @param string $user
     * @param string $repo
     * @param string $ref
     *
     * @return string
     */
    public function urlForReference(string $user, string $repo, string $ref): string
    {
        $key = sprintf(self::CACHE_KEY_TEMPLATE, md5($user . $repo), 'ref_url_' . md5($ref));

        if (($latest = $this->getFromCache($key)) !== null) {
            return $latest;
        }

        [$type, $resolved] = $this->resolveRefType($ref);
        $repoURL = $this->urlForRepository($user, $repo);

        switch ($type) {
            case 'commit':
                $url = "${repoURL}/commit/${resolved}";
                break;

            case 'tag':
                $url = "${repoURL}/releases/tag/${resolved}";
                break;

            case 'pull':
                $url = "${repoURL}/pull/${resolved}";
                break;

            case 'branch':
            default:
                $url = "${repoURL}/tree/${resolved}";
        }

        $this->setToCache($key, $url, 30);
        return $url;
    }

    /**
     * Get the reference data for branches for a repository.
     *
     * @param string $user
     * @param string $repo
     *
     * @return array
     */
    public function branches(string $user, string $repo): array
    {
        $default = [];
        $api = $this->client->api('git_data')->references();
        $params = [$api, 'branches', [$user, $repo]];

        $refs = $this->callGitHub([$this->pager, 'fetchAll'], $params, $default);

        array_walk($refs, function (&$data) {
            $data['name'] = str_replace('refs/heads/', '', $data['ref']);
        });

        usort($refs, $this->branchSorter());

        return $refs;
    }

    /**
     * Get all pull requests for a repository.
     *
     * @param string $user
     * @param string $repo
     * @param array $filter
     *
     * @return array
     */
    public function pullRequests(string $user, string $repo, array $filter = []): array
    {
        $default = [];
        $api = $this->client->api('pull_request');
        $params = [$user, $repo, $filter];

        $refs = $this->callGitHub([$api, 'all'], $params, $default);

        usort($refs, $this->prSorter($user));

        return $refs;
    }

    /**
     * Get metadata for a specific pull request.
     *
     * @param string $user
     * @param string $repo
     * @param string $number
     *
     * @return array|null
     */
    public function pullRequest(string $user, string $repo, string $number): ?array
    {
        $api = $this->client->api('pull_request');
        $params = [$user, $repo, $number];

        $refs = $this->callGitHub([$api, 'show'], $params);

        return is_array($refs) ? $refs : null;
    }

    /**
     * Get the extended metadata for a repository.
     *
     * @param string $user
     * @param string $repo
     *
     * @return array|null
     */
    public function repository(string $user, string $repo): ?array
    {
        $api = $this->client->api('repo');
        $params = [$user, $repo];

        $repo = $this->callGitHub([$api, 'show'], $params);

        return is_array($repo) ? $repo : null;
    }

    /**
     * Get the reference data for tags for a repository.
     *
     * @param string $user
     * @param string $repo
     *
     * @return array
     */
    public function tags(string $user, string $repo): array
    {
        $default = [];
        $api = $this->client->api('git_data')->references();
        $params = [$api, 'tags', [$user, $repo]];

        $refs = $this->callGitHub([$this->pager, 'fetchAll'], $params, $default);

        array_walk($refs, function (&$data) {
            $data['name'] = str_replace('refs/tags/', '', $data['ref']);
        });

        usort($refs, $this->tagSorter());

        return $refs;
    }

    /**
     * Compare two git commits
     *
     * @param string $user
     * @param string $repo
     * @param string $base
     * @param string $head
     *
     * @return array|string|null
     */
    public function diff(string $user, string $repo, string $base, string $head)
    {
        $api = $this->client->api('repo')->commits();
        $params = [$user, $repo, $base, $head];

        return $this->callGitHub([$api, 'compare'], $params);
    }

    /**
     * @param callable $api
     * @param array $params
     * @param mixed $default
     *
     * @return array|string|null
     */
    private function callGitHub(callable $api, array $params = [], $default = null)
    {
        try {
            $response = $api(...$params);

        } catch (RequestException $e) {
            $response = $default;

        } catch (RuntimeException $e) {
            $response = $default;
        }

        return $response;
    }
}
