<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Type\TargetEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class Target implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    const PARAM_GROUP = 'group';            // cd
    const PARAM_CONFIG = 'configuration';   // cd

    const PARAM_APP = 'application';        // eb, cd
    const PARAM_ENV = 'environment';        // eb

    const PARAM_REGION = 'region';          // s3, cd, eb
    const PARAM_S3_METHOD = 's3_method';    // s3
    const PARAM_BUCKET = 'bucket';          // s3, cd, eb
    const PARAM_REMOTE_PATH = 'path';       // s3, cd, eb, rsync
    const PARAM_LOCAL_PATH = 'source';      // s3, cd, eb
    const PARAM_CONTEXT = 'context';        // script

    const S3_METHODS = ['sync', 'artifact'];

    /**
     * @var string
     */
    protected $type;
    protected $name;
    protected $url;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Environment|null
     */
    protected $environment;

    /**
     * @var TargetTemplate|null
     */
    protected $template;

    /**
     * @var Credential|null
     */
    protected $credential;

    /**
     * Last job run on this this target.
     *
     * @var Job|null
     */
    protected $lastJob;

    /**
     * @param string $type
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($type = '', $id = '', TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->type = $type ? TargetEnum::ensureValid($type) : TargetEnum::defaultOption();
        $this->name = '';
        $this->url = '';

        $this->application = null;
        $this->environment = null;

        $this->template = null;
        $this->credential = null;

        $this->lastJob = null;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return Application
     */
    public function application(): Application
    {
        return $this->application;
    }

    /**
     * @return Environment|null
     */
    public function environment(): ?Environment
    {
        return $this->environment;
    }

    /**
     * @return TargetTemplate|null
     */
    public function template(): ?TargetTemplate
    {
        return $this->template;
    }

    /**
     * @return Credential|null
     */
    public function credential(): ?Credential
    {
        return $this->credential;
    }

    /**
     * @return Job|null
     */
    public function lastJob(): ?Job
    {
        return $this->lastJob;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function withType($type): self
    {
        $this->type = TargetEnum::ensureValid($type);
        return $this;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $url
     *
     * @return self
     */
    public function withURL(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param Application $application
     *
     * @return self
     */
    public function withApplication(Application $application): self
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @param Environment $environment
     *
     * @return self
     */
    public function withEnvironment(?Environment $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param TargetTemplate|null $template
     *
     * @return self
     */
    public function withTemplate(?TargetTemplate $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param Credential|null $credential
     *
     * @return self
     */
    public function withCredential(?Credential $credential): self
    {
        $this->credential = $credential;
        return $this;
    }

    /**
     * @param Job|null $job
     *
     * @return self
     */
    public function withLastJob(?Job $job): self
    {
        $this->lastJob = $job;
        return $this;
    }

    /**
     * Format a pretty name for the target.
     *
     * @return string
     */
    public function formatType(): string
    {
        return TargetEnum::format($this->type());
    }

    /**
     * Is this group for AWS?
     *
     * @return bool
     */
    public function isAWS()
    {
        return in_array($this->type(), [TargetEnum::TYPE_CD, TargetEnum::TYPE_EB, TargetEnum::TYPE_S3]);
    }

    /**
     * Format parameters into something readable.
     *
     * @return string
     */
    public function formatParameters()
    {
        switch ($this->type()) {
            case TargetEnum::TYPE_CD:
                return $this->parameter('group') ?: '???';

            case TargetEnum::TYPE_EB:
                return $this->parameter('environment') ?: '???';

            case TargetEnum::TYPE_S3:
                $bucket = $this->parameter('bucket') ?: '???';
                if ($path = $this->parameter('path')) {
                    $bucket = sprintf('%s/%s', $bucket, $path);

                    if ($source = $this->parameter('source')) {
                        $bucket = sprintf('%s:%s', $source, $bucket);
                    }
                }

                return $bucket;

            case TargetEnum::TYPE_SCRIPT:
                return $this->parameter('context') ?: '???';

            case TargetEnum::TYPE_RSYNC:
                return $this->parameter('path') ?: '???';

            default:
                return 'Unknown';
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [
            'id' => $this->id(),
            'created' => $this->created(),

            'type' => $this->type(),
            'name' => $this->name(),
            'url' => $this->url(),

            'parameters' => $this->parameters(),

            'application_id' => $this->application() ? $this->application()->id() : null,
            'environment_id' => $this->environment() ? $this->environment()->id() : null,

            'credential_id' => $this->credential() ? $this->credential()->id() : null,
            'template_id' => $this->template() ? $this->template()->id() : null,

            'job_id' => $this->lastJob() ? $this->lastJob()->id() : null,
        ];

        return $json;
    }
}
