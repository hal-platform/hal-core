<?php
/**
 * @copyright (c) 2018 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hal\Core\Utility\EntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class User implements JsonSerializable
{
    use EntityTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isDisabled;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var Collection
     */
    protected $tokens;

    /**
     * @var Collection
     */
    protected $identities;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);

        $this->name = '';
        $this->isDisabled = false;
        $this->settings = [];

        $this->tokens = new ArrayCollection;
        $this->identities = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
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
     * @return array
     */
    public function settings(): array
    {
        return $this->settings;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function setting(string $name)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        return null;
    }

    /**
     * @return Collection
     */
    public function tokens(): Collection
    {
        return $this->tokens;
    }

    /**
     * @return Collection
     */
    public function identities(): Collection
    {
        return $this->identities;
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
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function withSetting(string $name, $value): self
    {
        $this->settings[$name] = $value;
        return $this;
    }

    /**
     * @param array $settings
     *
     * @return self
     */
    public function withSettings(array $settings): self
    {
        $this->settings = [];
        foreach ($settings as $name => $value) {
            $this->withSetting($name, $value);
        }

        return $this;
    }

    // @todo add token add/remove - Collection items should always be removed from the parent
    // @todo add identity add/remove - Collection items should always be removed from the parent

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

            'settings' => $this->settings(),

            'tokens' => $this->tokens()->toArray(),
            'identities' => $this->identities()->toArray(),
        ];

        return $json;
    }
}
