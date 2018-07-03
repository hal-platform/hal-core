<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Type\ScheduledActionStatusEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

/**
 * Not a fan of how Parent/Child is handled right not but don't feel like rearchitecting it.
 *
 * Must be revisited though.
 */
class ScheduledAction implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    /**
     * @var string
     */
    protected $status;
    protected $message;

    /**
     * @var Job|null
     */
    protected $triggerJob;

    /**
     * @var Job|null
     */
    protected $scheduledJob;

    /**
     * @var User|null
     */
    protected $user;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->status = ScheduledActionStatusEnum::defaultOption();
        $this->message = '';

        $this->triggerJob = null;
        $this->scheduledJob = null;

        $this->user = null;
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
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
    public function triggerJob(): ?Job
    {
        return $this->triggerJob;
    }

    /**
     * @return Job|null
     */
    public function scheduledJob(): ?Job
    {
        return $this->scheduledJob;
    }

    /**
     * @return User|null
     */
    public function user(): ?User
    {
        return $this->user;
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function withStatus(string $status): self
    {
        $this->status = ScheduledActionStatusEnum::ensureValid($status);
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
     * @param User|null $user
     *
     * @return self
     */
    public function withUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param Job|null $job
     *
     * @return self
     */
    public function withTriggerJob(?Job $job): self
    {
        $this->triggerJob = $job;
        return $this;
    }

    /**
     * @param Job|null $job
     *
     * @return self
     */
    public function withScheduledJob(?Job $job): self
    {
        $this->scheduledJob = $job;
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

            'status' => $this->status(),
            'message' => $this->message(),

            'parameters' => $this->parameters(),

            'trigger_job_id' => $this->triggerJob() ? $this->triggerJob()->id() : null,
            'scheduled_job_id' => $this->scheduledJob() ? $this->scheduledJob()->id() : null,

            'user_id' => $this->user() ? $this->user()->id() : null,
        ];

        return $json;
    }
}
