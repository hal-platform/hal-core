<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\System;

use Hal\Core\Type\IdentityProviderEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ParameterTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class UserIdentityProvider implements JsonSerializable
{
    use EntityTrait;
    use ParameterTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeParameters();

        $this->name = '';
        $this->type = IdentityProviderEnum::defaultOption();
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
    public function type(): string
    {
        return $this->type;
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
     * @param string $type
     *
     * @return self
     */
    public function withType(string $type): self
    {
        $this->type = IdentityProviderEnum::ensureValid($type);
        return $this;
    }

    /**
     * Format a pretty name for the IDP.
     *
     * @return string
     */
    public function formatType(): string
    {
        return IdentityProviderEnum::format($this->type());
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
            'type' => $this->type(),

            'parameters' => $this->parameters(),
        ];

        return $json;
    }
}
