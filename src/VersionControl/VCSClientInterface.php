<?php
/**
 * @copyright (c) 2018 Steve Kluck
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\VersionControl;

interface VCSClientInterface
{
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
    public function resolveRef(string $user, string $repo, string $reference): ?array;

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
    public function resolveRefType(string $reference): ?array;

    /**
     * @param string $user
     * @param string $repo
     *
     * @return string
     */
    public function urlForRepository(string $user, string $repo): string;

    /**
     * @param string $user
     * @param string $repo
     * @param string $ref
     *
     * @return string
     */
    public function urlForReference(string $user, string $repo, string $ref): string;

    /**
     * Get the reference data for branches for a repository.
     *
     * @param string $user
     * @param string $repo
     *
     * @return array
     */
    public function branches(string $user, string $repo): array;

    /**
     * Get all pull requests for a repository.
     *
     * @param string $user
     * @param string $repo
     * @param array $filter
     *
     * @return array
     */
    public function pullRequests(string $user, string $repo, array $filter = []): array;

    /**
     * Get metadata for a specific pull request.
     *
     * @param string $user
     * @param string $repo
     * @param string $number
     *
     * @return array|null
     */
    public function pullRequest(string $user, string $repo, string $number): ?array;

    /**
     * Get the extended metadata for a repository.
     *
     * @param string $user
     * @param string $repo
     *
     * @return array|null
     */
    public function repository(string $user, string $repo): ?array;

    /**
     * Get the reference data for tags for a repository.
     *
     * @param string $user
     * @param string $repo
     *
     * @return array
     */
    public function tags(string $user, string $repo): array;

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
    public function diff(string $user, string $repo, string $base, string $head);
}
