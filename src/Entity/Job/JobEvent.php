<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\Job;

use Hal\Core\Entity\Job;
use Hal\Core\Type\JobEventStageEnum;
use Hal\Core\Type\JobEventStatusEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class JobEvent implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $order;

    /**
     * @var int
     */
    protected $duration;

    /**
     * @var string
     */
    protected $message;

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

        $this->stage = JobEventStageEnum::defaultOption();
        $this->status = JobEventStatusEnum::defaultOption();

        $this->order = 0;
        $this->duration = 0;
        $this->message = '';

        $this->job = null;
    }

    /**
     * @return string
     */
    public function stage(): string
    {
        return $this->stage;
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function order(): int
    {
        return $this->order;
    }

    /**
     * @return int
     */
    public function duration(): int
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return Job|null
     */
    public function job(): ?Job
    {
        return $this->job;
    }

    /**
     * @param string $stage
     *
     * @return self
     */
    public function withStage(string $stage): self
    {
        $this->stage = JobEventStageEnum::ensureValid($stage);
        return $this;
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function withStatus(string $status): self
    {
        $this->status = JobEventStatusEnum::ensureValid($status);
        return $this;
    }

    /**
     * @param int $order
     *
     * @return self
     */
    public function withOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param int $duration
     *
     * @return self
     */
    public function withDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @param string $message
     *
     * @return self
     */
    public function withMessage(string $message): self
    {
        $this->message = $message;
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

            'stage' => $this->stage(),
            'status' => $this->status(),

            'order' => $this->order(),
            'duration' => $this->duration(),
            'message' => $this->message(),

            'parameters' => '**DATA**',
            'job_id' => $this->job() ? $this->job()->id() : null,
        ];

        return $json;
    }
}
