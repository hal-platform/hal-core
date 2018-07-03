<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Entity\Credential\AWSRoleCredential;
use Hal\Core\Entity\Credential\AWSStaticCredential;
use Hal\Core\Entity\Credential\PrivateKeyCredential;
use Hal\Core\Type\CredentialEnum;
use Hal\Core\Utility\EntityTrait;
use Hal\Core\Utility\ScopedEntityTrait;
use JsonSerializable;
use QL\MCP\Common\Time\TimePoint;

class Credential implements JsonSerializable
{
    use EntityTrait;
    use ScopedEntityTrait;

    /**
     * @var string
     */
    protected $name;
    protected $type;

    /**
     * Signifies a credential is internal and can be edited only by administrators.
     *
     * @var bool
     */
    protected $isInternal;

    /**
     * @var AWSRoleCredential|null
     */
    protected $awsRole;

    /**
     * @var AWSStaticCredential|null
     */
    protected $awsStatic;

    /**
     * @var PrivateKeyCredential|null
     */
    protected $privateKey;

    /**
     * @param string $id
     * @param TimePoint|null $created
     */
    public function __construct($id = '', ?TimePoint $created = null)
    {
        $this->initializeEntity($id, $created);
        $this->initializeScopes();

        $this->name = '';
        $this->type = CredentialEnum::defaultOption();
        $this->isInternal = false;

        $this->awsRole = new AWSRoleCredential;
        $this->awsStatic = new AWSStaticCredential;
        $this->privateKey = new PrivateKeyCredential;
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
    public function isInternal(): bool
    {
        return $this->isInternal;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return AWSRoleCredential|AWSStaticCredential|PrivateKeyCredential|null
     */
    public function details()
    {
        if ($this->type() === CredentialEnum::TYPE_AWS_ROLE) {
            return $this->awsRole;

        } elseif ($this->type() === CredentialEnum::TYPE_AWS_STATIC) {
            return $this->awsStatic;

        } elseif ($this->type() === CredentialEnum::TYPE_PRIVATEKEY) {
            return $this->privateKey;
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function withType(string $type): self
    {
        $this->type = CredentialEnum::ensureValid($type);
        return $this;
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
     * @param bool $isInternal
     *
     * @return self
     */
    public function withIsInternal(bool $isInternal): self
    {
        $this->isInternal = $isInternal;
        return $this;
    }

    /**
     * @param AWSRoleCredential|AWSStaticCredential|PrivateKeyCredential $info
     *
     * @return self
     */
    public function withDetails($info): self
    {
        if ($info instanceof AWSRoleCredential) {
            $this->withType(CredentialEnum::TYPE_AWS_ROLE);
            $this->awsRole = $info;
            $this->awsStatic = new AWSStaticCredential;
            $this->privateKey = new PrivateKeyCredential;

        } elseif ($info instanceof AWSStaticCredential) {
            $this->withType(CredentialEnum::TYPE_AWS_STATIC);
            $this->awsRole = new AWSRoleCredential;
            $this->awsStatic = $info;
            $this->privateKey = new PrivateKeyCredential;

        } elseif ($info instanceof PrivateKeyCredential) {
            $this->withType(CredentialEnum::TYPE_PRIVATEKEY);
            $this->awsRole = new AWSRoleCredential;
            $this->awsStatic = new AWSStaticCredential;
            $this->privateKey = $info;

        } else {
            $this->withType(CredentialEnum::TYPE_AWS_STATIC);
            $this->awsRole = new AWSRoleCredential;
            $this->awsStatic = new AWSStaticCredential;
            $this->privateKey = new PrivateKeyCredential;
        }

        return $this;
    }

    /**
     * Format a pretty name for the credential.
     *
     * @return string
     */
    public function formatType(): string
    {
        return CredentialEnum::format($this->type());
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
            'isInternal' => $this->isInternal(),

            'details' => $this->details(),

            'application_id' => $this->application() ? $this->application()->id() : null,
            'organization_id' => $this->organization() ? $this->organization()->id() : null,
            'environment_id' => $this->environment() ? $this->environment()->id() : null,
        ];

        return $json;
    }
}
