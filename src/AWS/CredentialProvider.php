<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\AWS;

use Aws\CacheInterface;
use Aws\Credentials\CredentialProvider as AWSCredentialProvider;
use Aws\Sdk;
use Aws\Sts\StsClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hal\Core\Crypto\Encryption;
use Hal\Core\Entity\Credential;
use Psr\Log\LoggerInterface;
use Hal\Core\Entity\Credential\AWSRoleCredential;
use Hal\Core\Entity\Credential\AWSStaticCredential;

class CredentialProvider
{
    const ERR_INVALID_SECRET = 'Missing credentials. AWS deployments require access secret.';
    const ERR_INVALID_INTERNAL_CREDENTIALS = 'Missing internal credentials. Searched for "%s".';

    const DEFAULT_STS_LIFETIME = 1800;
    const DEFAULT_INTERNAL_CREDENTIAL_NAME = 'Hal Internal Credentials - AWS';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Encryption
     */
    private $encryption;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Sdk
     */
    private $aws;

    /**
     * @var int
     */
    private $stsLifetime;

    /**
     * @var string
     */
    private $credentialName;

    /**
     * @var CacheInterface|null
     */
    private $credentialCache;

    /**
     * @var bool
     */
    private $useHostCredentials;

    /**
     * @var callable
     */
    private $hostCredentials;

    /**
     * @param LoggerInterface $logger
     * @param Encryption $encryption
     * @param EntityManagerInterface $em
     * @param Sdk $aws
     * @param bool $useHostCredentials
     */
    public function __construct(LoggerInterface $logger, Encryption $encryption, EntityManagerInterface $em, Sdk $aws, bool $useHostCredentials = false)
    {
        $this->logger = $logger;
        $this->encryption = $encryption;
        $this->em = $em;
        $this->aws = $aws;

        $this->stsLifetime = self::DEFAULT_STS_LIFETIME;
        $this->credentialName = self::DEFAULT_INTERNAL_CREDENTIAL_NAME;

        $this->useHostCredentials = $useHostCredentials;
        $this->hostCredentials = $this->getDefaultHostCredentials();
    }

    /**
     * Provide your own callable function that resolves AWS credentials.
     *
     * @see https://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html#using-a-credential-provider
     *
     * @param callable $provider
     *
     * @return void
     */
    public function setHostCredentials(callable $provider)
    {
        $this->hostCredentials = $provider;
    }

    /**
     * Set lifetime of STS tokens.
     *
     * Valid values: 900 - 3600 seconds
     * Default: 30 minutes
     *
     * @param int $seconds
     *
     * @return void
     */
    public function setSTSLifetime($seconds)
    {
        $seconds = (int) $seconds;

        if ($seconds >= 900 && $seconds <= 3600) {
            $this->stsLifetime = $seconds;
        }
    }

    /**
     * @param string $credentialName
     *
     * @return void
     */
    public function setInternalCredentialName($credentialName)
    {
        $this->credentialName = (string) $credentialName;
    }

    /**
     * @param CacheInterface $cache
     *
     * @return void
     */
    public function setCredentialCache(CacheInterface $cache)
    {
        $this->credentialCache = $cache;
    }

    /**
     * @param AWSStaticCredential $credential
     *
     * @return callable|array|null
     */
    public function getStaticCredentials(AWSStaticCredential $credential)
    {
        if ($this->useHostCredentials) {
            return $this->hostCredentials;
        }

        if (!$secret = $this->getSecret($credential)) {
            $this->logger->critical(self::ERR_INVALID_SECRET);
            return null;
        }

        return [
            'key' => $credential->key(),
            'secret' => $secret
        ];
    }

    /**
     * @param AWSRoleCredential $credential
     * @param string $region
     *
     * @return AWSCredentialProvider|callable|null
     */
    public function getRoleCredentials(AWSRoleCredential $credential, $region)
    {
        if (!$sts = $this->getStsWithInternalCredentials($region)) {
            return null;
        }

        $account = $credential->account();
        $role = $credential->role();
        $cacheKey = sprintf('%s_%s', $account, $role);

        $assumeRoleProvider = AWSCredentialProvider::assumeRole([
            'client' => $sts,
            'assume_role_params' => [
                'DurationSeconds' => $this->stsLifetime, # 900 - 3600
                'RoleArn' => sprintf('arn:aws:iam::%s:%s', $account, $role),
                'RoleSessionName' => 'hal-' . bin2hex(random_bytes(4)), # unique identifier for auditing
            ]
        ]);

        $provider = AWSCredentialProvider::memoize($assumeRoleProvider);
        if ($this->credentialCache) {
            $provider = AWSCredentialProvider::cache($provider, $this->credentialCache, $cacheKey);
        }

        return $provider;
    }

    /**
     * @param string $region
     *
     * @return StsClient|null
     */
    private function getStsWithInternalCredentials($region)
    {
        if ($this->useHostCredentials) {
            return $this->aws->createSts([
                'region' => $region,
                'credentials' => $this->hostCredentials
            ]);
        }

        $credentials = $this->em
            ->getRepository(Credential::class)
            ->findOneBy(['isInternal' => true, 'name' => $this->credentialName]);

        if (!$credentials instanceof Credential) {
            $this->logger->critical(sprintf(self::ERR_INVALID_INTERNAL_CREDENTIALS, $this->credentialName));
            return null;
        }

        if (!$credentials->details() instanceof AWSStaticCredential) {
            $this->logger->critical(sprintf(self::ERR_INVALID_INTERNAL_CREDENTIALS, $this->credentialName));
            return null;
        }

        if (!$internalCredentials = $this->getStaticCredentials($credentials->details())) {
            return null;
        }

        return $this->aws->createSts([
            'region' => $region,
            'credentials' => $internalCredentials
        ]);
    }

    /**
     * @param AWSStaticCredential $credential
     *
     * @return string
     */
    private function getSecret(AWSStaticCredential $credential)
    {
        if (!$secret = $credential->secret()) {
            return '';
        }

        return $secret = $this->encryption->decrypt($secret);
    }

    /**
     * @return callable
     */
    private function getDefaultHostCredentials()
    {
        return AWSCredentialProvider::defaultProvider();
    }
}
