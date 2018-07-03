<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hal\Core\Type\JobEnum;
use Hal\Core\Type\JobStatusEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class Job implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    const JOB_TYPE = JobEnum::TYPE_JOB;

    /**
     * @var TimePoint|null
     */
    protected $start;
    protected $end;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var User|null
     */
    protected $user;

    /**
     * @var Collection
     */
    protected $artifacts;
    protected $events;
    protected $meta;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->status = JobStatusEnum::defaultOption();

        $this->start = null;
        $this->end = null;

        $this->user = null;
        $this->artifacts = new ArrayCollection;
        $this->events = new ArrayCollection;
        $this->meta = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return static::JOB_TYPE;
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return TimePoint|null
     */
    public function start(): ?TimePoint
    {
        return $this->start;
    }

    /**
     * @return TimePoint|null
     */
    public function end(): ?TimePoint
    {
        return $this->end;
    }

    /**
     * @return User|null
     */
    public function user(): ?User
    {
        return $this->user;
    }

    /**
     * @return Collection
     */
    public function artifacts(): Collection
    {
        return $this->artifacts;
    }

    /**
     * @return Collection
     */
    public function events(): Collection
    {
        return $this->events;
    }

    /**
     * @return Collection
     */
    public function meta(): Collection
    {
        return $this->meta;
    }

    /**
     * @param string $status
     *
     * @return static
     */
    public function withStatus(string $status): self
    {
        $this->status = JobStatusEnum::ensureValid($status);
        return $this;
    }

    /**
     * @param TimePoint|null $start
     *
     * @return static
     */
    public function withStart(?TimePoint $start): self
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @param TimePoint|null $end
     *
     * @return static
     */
    public function withEnd(?TimePoint $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @param User|null $user
     *
     * @return static
     */
    public function withUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return bool
     */
    public function inProgress(): bool
    {
        return in_array($this->status(), [JobStatusEnum::TYPE_PENDING, JobStatusEnum::TYPE_RUNNING], true);
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return in_array($this->status(), [JobStatusEnum::TYPE_SUCCESS, JobStatusEnum::TYPE_FAILURE, JobStatusEnum::TYPE_REMOVED], true);
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status() === JobStatusEnum::TYPE_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isFailure(): bool
    {
        return $this->status() === JobStatusEnum::TYPE_FAILURE;
    }

    // @todo add artifact add/remove - Collection items should always be removed from the parent
    // @todo add event add/remove - Collection items should always be removed from the parent
    // @todo add meta add/remove - Collection items should always be removed from the parent

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [
            'id' => $this->id(),
            'created' => $this->created(),

            'type' => $this->type(),
            'status' => $this->status(),
            'parameters' => $this->parameters(),

            'start' => $this->start(),
            'end' => $this->end(),

            'user_id' => $this->user() ? $this->user()->id() : null,

            'artifacts' => $this->artifacts()->toArray(),
            'events' => $this->events()->toArray(),
            'meta' => $this->meta()->toArray(),
        ];

        return $json;
    }
}
