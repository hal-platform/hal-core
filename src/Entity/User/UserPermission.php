<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\User;

use Hal\Core\Entity\User;
use Hal\Core\Type\UserPermissionEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ScopedEntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

/**
 * Right now this is per user, but eventually we need to support permission roles.
 *
 * Instead of users having permission(s)
 *
 * Roles would have permission(s) and Users have role(s) AND individual permission(s).
 */
class UserPermission implements JsonSerializable
{
    use EntityTrait;
    use ScopedEntityTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var User|null
     */
    protected $user;

    /**
     * @param string $type
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($type = '', $id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeScopes();

        $this->type = $type ? UserPermissionEnum::ensureValid($type) : UserPermissionEnum::defaultOption();

        $this->user = null;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return User
     */
    public function user(): ?User
    {
        return $this->user;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function withType(string $type): self
    {
        $this->type = UserPermissionEnum::ensureValid($type);
        return $this;
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function withUser(User $user): self
    {
        $this->user = $user;
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

            'type' => $this->type(),

            'user_id' => $this->user()->id(),

            'application_id' => $this->application() ? $this->application()->id() : null,
            'organization_id' => $this->organization() ? $this->organization()->id() : null,
            'environment_id' => $this->environment() ? $this->environment()->id() : null,
        ];

        return $json;
    }
}
