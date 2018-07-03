<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\Job;

use Hal\Core\Entity\Job;
use Hal\Core\Utility\EntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class JobMeta implements JsonSerializable
{
    use EntityTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

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

        $this->name = '';
        $this->value = '';

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
     * @return string
     */
    public function value(): string
    {
        return $this->value;
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
     * @param string $value
     *
     * @return self
     */
    public function withValue(string $value): self
    {
        $this->value = $value;
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
            'value' => $this->value(),

            'job_id' => $this->job() ? $this->job()->id() : null,
        ];

        return $json;
    }
}
