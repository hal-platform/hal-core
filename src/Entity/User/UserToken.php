<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\User;

use Hal\Core\Entity\Organization;
use Hal\Core\Entity\User;
use Hal\Core\Utility\EntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class UserToken implements JsonSerializable
{
    use EntityTrait;

    /**
     * @var string
     */
    protected $name;
    protected $value;

    /**
     * Token is created on behalf of a user.
     *
     * @var User|null
     */
    protected $user;

    /**
     * Token is created on behalf of an organization. (Not currently used)
     *
     * @var Organization|null
     */
    protected $organization;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);

        $this->name = '';
        $this->value = '';

        $this->user = null;
        $this->organization = null;
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
     * @return User|null
     */
    public function user(): ?User
    {
        return $this->user;
    }

    /**
     * @return Organization|null
     */
    public function organization(): ?Organization
    {
        return $this->organization;
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
     * @param Organization|null $organization
     *
     * @return self
     */
    public function withOrganization(?Organization $organization): self
    {
        $this->organization = $organization;
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

            'user_id' => $this->user() ? $this->user()->id() : null,
            'organization_id' => $this->organization() ? $this->organization()->id() : null,
        ];

        return $json;
    }
}
