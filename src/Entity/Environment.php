<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Utility\EntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class Environment implements JsonSerializable
{
    use EntityTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isProduction;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);

        $this->name = '';
        $this->isProduction = false;
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
    public function isProduction(): bool
    {
        return $this->isProduction;
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
     * @param bool $isProduction
     *
     * @return self
     */
    public function withIsProduction(bool $isProduction): self
    {
        $this->isProduction = $isProduction;
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
            'is_production' => $this->isProduction(),
        ];

        return $json;
    }
}
