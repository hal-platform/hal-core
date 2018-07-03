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
use Hal\Core\Utility\ScopedEntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

/**
 * Terrible naming! Must be changed!
 *
 * Alternatives:
 *
 * Space, System, Set, Domain, Workspace, Group, Node
 *
 * "Targets are attached to X"
 *
 * - "This target belongs to server XYZ"
 * - "This target is for aws region us-east-1"
 *
 * Other terms and systems:
 *     - Code Deploy Group
 *     - Elastic Beanstalk Environment
 *     - Kubernetes Pod
 */
class TargetTemplate implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;
    use ScopedEntityTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $type
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($type = '', $id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();
        $this->initializeScopes();

        $this->type = $type ? TargetEnum::ensureValid($type) : TargetEnum::defaultOption();
        $this->name = '';
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
    public function withName($name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Format a pretty name for the template.
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
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [
            'id' => $this->id(),
            'created' => $this->created(),

            'type' => $this->type(),
            'name' => $this->name(),

            'application_id' => $this->application() ? $this->application()->id() : null,
            'organization_id' => $this->organization() ? $this->organization()->id() : null,
            'environment_id' => $this->environment() ? $this->environment()->id() : null,
        ];

        return $json;
    }
}
