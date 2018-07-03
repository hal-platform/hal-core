<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ScopedEntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class EncryptedProperty implements JsonSerializable
{
    use EntityTrait;
    use ScopedEntityTrait;

    /**
     * @var string
     */
    protected $name;
    protected $secret;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeScopes();

        $this->name = '';
        $this->secret = '';
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
    public function secret(): string
    {
        return $this->secret;
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
     * @param string $secret
     *
     * @return self
     */
    public function withSecret($secret): self
    {
        $this->secret = $secret;
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
            'secret' => '**ENCRYPTED**',

            'application_id' => $this->application() ? $this->application()->id() : null,
            'organization_id' => $this->organization() ? $this->organization()->id() : null,
            'environment_id' => $this->environment() ? $this->environment()->id() : null,
        ];

        return $json;
    }
}
