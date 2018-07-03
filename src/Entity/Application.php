<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Entity\System\VersionControlProvider;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class Application implements JsonSerializable
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
    protected $isDisabled;

    /**
     * @var VersionControlProvider|null
     */
    protected $provider;

    /**
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
        $this->initializeParameters();

        $this->name = '';
        $this->isDisabled = false;

        $this->provider = null;
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
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    /**
     * @return VersionControlProvider
     */
    public function provider(): ?VersionControlProvider
    {
        return $this->provider;
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
     * @param bool $isDisabled
     *
     * @return self
     */
    public function withIsDisabled(bool $isDisabled): self
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    /**
     * @param VersionControlProvider|null $provider
     *
     * @return self
     */
    public function withProvider(?VersionControlProvider $provider): self
    {
        $this->provider = $provider;
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
            'is_disabled' => $this->isDisabled(),

            'parameters' => $this->parameters(),

            'provider_id' => $this->provider() ? $this->provider()->id() : null,
            'organization_id' => $this->organization() ? $this->organization()->id() : null,
        ];

        return $json;
    }
}
