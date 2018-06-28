<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hal\Core\Entity\System\UserIdentityProvider;
use Hal\Core\Entity\User;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class UserIdentity implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    /**
     * @var User|null
     */
    protected $user;

    /**
     * @var string
     */
    protected $providerUniqueID;

    /**
     * @var UserIdentityProvider|null
     */
    protected $provider;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->user = null;

        $this->providerUniqueID = '';

        $this->provider = null;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function providerUniqueID(): string
    {
        return $this->providerUniqueID;
    }

    /**
     * @return UserIdentityProvider
     */
    public function provider(): UserIdentityProvider
    {
        return $this->provider;
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
     * @param string $id
     *
     * @return self
     */
    public function withProviderUniqueID(string $id): self
    {
        $this->providerUniqueID = $id;
        return $this;
    }

    /**
     * @param UserIdentityProvider|null $provider
     *
     * @return self
     */
    public function withProvider(?UserIdentityProvider $provider): self
    {
        $this->provider = $provider;
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

            'parameters' => $this->parameters(),

            'provider_unique_id' => $this->providerUniqueID(),
            'provider_id' => $this->provider()->id()
        ];

        return $json;
    }
}
