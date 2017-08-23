<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity\Credential;

use PHPUnit\Framework\TestCase;

class AWSCredentialTest extends TestCase
{
    public function testDefaultValues()
    {
        $cred = new AWSCredential;

        $this->assertSame('', $cred->key());
        $this->assertSame('', $cred->secret());
    }

    public function testProperties()
    {
        $cred = new AWSCredential('key1', 'secret2');

        $this->assertSame('key1', $cred->key());
        $this->assertSame('secret2', $cred->secret());
    }
}