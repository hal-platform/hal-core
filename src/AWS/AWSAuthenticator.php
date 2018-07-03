<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\AWS;

use Aws\AutoScaling\AutoScalingClient;
use Aws\CloudFormation\CloudFormationClient;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CodeDeploy\CodeDeployClient;
use Aws\CodePipeline\CodePipelineClient;
use Aws\Ec2\Ec2Client;
use Aws\ElasticBeanstalk\ElasticBeanstalkClient;
use Aws\ElasticLoadBalancing\ElasticLoadBalancingClient;
use Aws\S3\S3Client;
use Aws\Sdk;
use Aws\Ssm\SsmClient;
use Hal\Core\Entity\Credential\AWSRoleCredential;
use Hal\Core\Entity\Credential\AWSStaticCredential;
use Psr\Log\LoggerInterface;

class AWSAuthenticator
{
    const ERR_INVALID_REGION = 'Invalid AWS region specified.';
    const ERR_INVALID_CREDENTIAL = 'Missing or invalid credentials. AWS deployments require authentication credentials.';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Sdk
     */
    private $aws;

    /**
     * @var CredentialProvider
     */
    private $credentialProvider;

    /**
     * Hardcoded, since Enums were removed in aws sdk 3.0
     *
     * @var string[]
     */
    public static $awsRegions = [
        // asia pacific
        'ap-northeast-1',
        'ap-northeast-2',
        'ap-southeast-1',
        'ap-southeast-2',
        'ap-south-1',

        // europe, middle east, africa
        'eu-central-1',
        'eu-west-1',
        'eu-west-2',

        // americas
        'us-east-1',
        'us-east-2',
        'us-west-1',
        'us-west-2',

        'ca-central-1',
        'sa-east-1',
    ];

    /**
     * @param LoggerInterface $logger
     * @param CredentialProvider $provider
     * @param Sdk $aws
     */
    public function __construct(LoggerInterface $logger, CredentialProvider $provider, Sdk $aws)
    {
        $this->logger = $logger;
        $this->credentialProvider = $provider;
        $this->aws = $aws;
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return AutoScalingClient|null
     */
    public function getAutoscaling($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createAutoScaling($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return CodeDeployClient|null
     */
    public function getCD($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createCodeDeploy($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return CloudFormationClient|null
     */
    public function getCloudFormation($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createCloudFormation($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return CodePipelineClient|null
     */
    public function getCodePipeline($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createCodePipeline($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return ElasticBeanstalkClient|null
     */
    public function getEB($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createElasticBeanstalk($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return Ec2Client|null
     */
    public function getEC2($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createEc2($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return ElasticLoadBalancingClient|null
     */
    public function getELB($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createElasticLoadBalancing($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return S3Client|null
     */
    public function getS3($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createS3($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return SsmClient|null
     */
    public function getSSM($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createSsm($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return CloudWatchLogsClient|null
     */
    public function getCloudWatchLogs($region, $credential)
    {
        if (!$config = $this->getConfigWithCredentials($region, $credential)) {
            return null;
        }

        return $this->aws->createCloudWatchLogs($config);
    }

    /**
     * @param string $region
     * @param mixed $credential
     *
     * @return array|null
     */
    private function getConfigWithCredentials($region, $credential)
    {
        if (!in_array($region, self::$awsRegions, true)) {
            $this->logger->critical(self::ERR_INVALID_REGION, [
                'specified_region' => $region,
            ]);

            return null;
        }

        $credentials = null;

        if ($credential instanceof AWSStaticCredential) {
            $credentials = $this->credentialProvider->getStaticCredentials($credential);

        } elseif ($credential instanceof AWSRoleCredential) {
            $credentials = $this->credentialProvider->getRoleCredentials($credential, $region);
        }

        if (!$credentials) {
            $this->logger->critical(self::ERR_INVALID_CREDENTIAL);
            return null;
        }

        return [
            'region' => $region,
            'credentials' => $credentials,
        ];
    }
}
