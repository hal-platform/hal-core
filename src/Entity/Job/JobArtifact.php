<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\Job;

use Hal\Core\Entity\Job;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class JobArtifact implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isRemovable;

    /**
     * @var Job|null
     */
    protected $job;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->name = '';
        $this->isRemovable = false;

        $this->job = null;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRemovable(): bool
    {
        return $this->isRemovable;
    }

    /**
     * @return Job|null
     */
    public function job(): ?Job
    {
        return $this->job;
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
     * @param bool $isRemovable
     *
     * @return self
     */
    public function withIsRemovable(bool $isRemovable): self
    {
        $this->isRemovable = $isRemovable;
        return $this;
    }

    /**
     * @param Job $job
     *
     * @return self
     */
    public function withJob(Job $job): self
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [
            'id' => $this->id(),
            'created' => $this->created(),

            'name' => $this->name(),
            'is_removable' => $this->isRemovable(),

            'parameters' => $this->parameters(),

            'job_id' => $this->job() ? $this->job()->id() : null,
        ];

        return $json;
    }
}
