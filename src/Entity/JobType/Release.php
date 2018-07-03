<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\JobType;

use Hal\Core\Entity\Application;
use Hal\Core\Entity\Environment;
use Hal\Core\Entity\Job;
use Hal\Core\Entity\Target;
use Hal\Core\Type\JobEnum;
use QL\MCP\Common\Time\TimePoint;

class Release extends Job
{
    const JOB_TYPE = JobEnum::TYPE_RELEASE;

    /**
     * @var Build|null
     */
    protected $build;

    /**
     * @var Application|null
     */
    protected $application;

    /**
     * @var Environment|null
     */
    protected $environment;

    /**
     * @var Target|null
     */
    protected $target;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        parent::__construct($id, $created);

        $this->build = null;
        $this->application = null;
        $this->environment = null;
        $this->target = null;
    }

    /**
     * @return Build
     */
    public function build(): ?Build
    {
        return $this->build;
    }

    /**
     * @return Application|null
     */
    public function application(): ?Application
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
     * @return Target|null
     */
    public function target(): ?Target
    {
        return $this->target;
    }

    /**
     * @param Build $build
     *
     * @return self
     */
    public function withBuild(Build $build): self
    {
        $this->build = $build;
        return $this;
    }

    /**
     * @param Application|null $application
     *
     * @return self
     */
    public function withApplication(?Application $application): self
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @param Environment|null $environment
     *
     * @return self
     */
    public function withEnvironment(?Environment $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param Target|null $target
     *
     * @return self
     */
    public function withTarget(?Target $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize() + [
            'build_id' => $this->build() ? $this->build()->id() : null,

            'application_id' => $this->application() ? $this->application()->id() : null,
            'environment_id' => $this->environment() ? $this->environment()->id() : null,
            'target_id' => $this->target() ? $this->target()->id() : null,
        ];

        return $json;
    }
}
