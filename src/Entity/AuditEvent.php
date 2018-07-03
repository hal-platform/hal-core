<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Type\AuditActionEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use QL\MCP\Common\Time\TimePoint;

/**
 * Audit Events must be complete denormalized from DB entities.
 */
class AuditEvent
{
    use EntityTrait;
    use ParameterTrait;

    /**
     * @var string
     */
    protected $action;
    protected $actor;
    protected $description;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->action = AuditActionEnum::defaultOption();
        $this->actor = '';
        $this->description = '';
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function actor(): string
    {
        return $this->actor;
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @param string $action
     *
     * @return self
     */
    public function withAction(string $action): self
    {
        $this->action = AuditActionEnum::ensureValid($action);
        return $this;
    }

    /**
     * @param string $actor
     *
     * @return self
     */
    public function withActor(string $actor): self
    {
        $this->actor = $actor;
        return $this;
    }

    /**
     * @param string $description
     *
     * @return self
     */
    public function withDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
