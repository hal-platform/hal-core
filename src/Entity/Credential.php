<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Hal\Core\Entity;

use JsonSerializable;
use QL\Hal\Core\Entity\Credential\AWSCredential;
use QL\Hal\Core\Entity\Credential\PrivateKeyCredential;

class Credential implements JsonSerializable
{
    /**
     * @type string
     */
    protected $id;

    /**
     * @type string
     */
    protected $type;

    /**
     * @type string
     */
    protected $name;

    /**
     * @type AWSCredential|null
     */
    protected $aws;

    /**
     * @type PrivateKeyCredential|null
     */
    protected $privateKey;

    /**
     * @param string $id
     */
    public function __construct($id = '')
    {
        $this->id = $id;

        $this->type = '';
        $this->name = '';

        $this->aws = new AWSCredential;
        $this->privateKey = new PrivateKeyCredential;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return AWSCredential|null
     */
    public function aws()
    {
        return $this->aws;
    }

    /**
     * @return PrivateKeyCredential|null
     */
    public function privateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $id
     *
     * @return self
     */
    public function withId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function withType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function withName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param AWSCredential $aws
     *
     * @return self
     */
    public function withAWS(AWSCredential $aws)
    {
        $this->aws = $aws;
        return $this;
    }

    /**
     * @param PrivateKeyCredential $key
     *
     * @return self
     */
    public function withPrivateKey(PrivateKeyCredential $key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = [
            'id' => $this->id(),

            'type' => $this->type(),
            'name' => $this->name(),

            // 'aws' => $this->aws(),
            // 'privateKey' => $this->privateKey(),
        ];

        return $json;
    }
}
