<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\AWS;

use Aws\Credentials\Credentials;
use Aws\Sdk;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use function GuzzleHttp\Promise\promise_for;
use Hal\Core\Crypto\Encryption;
use Hal\Core\Entity\Credential;
use Hal\Core\Entity\Credential\AWSRoleCredential;
use Hal\Core\Entity\Credential\AWSStaticCredential;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CredentialProviderTest extends TestCase
{
    private $logger;
    private $decrypter;
    private $em;
    private $aws;

    public function setUp()
    {
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->decrypter = Mockery::mock(Encryption::class);
        $this->em = Mockery::mock(EntityManagerInterface::class);
        // can't mock :(
        $this->aws = new Sdk(['version' => 'latest']);
    }

    public function testCredentialsSecretIsEmpty()
    {
        $this->logger
            ->shouldReceive('critical')
            ->with(CredentialProvider::ERR_INVALID_SECRET)
            ->once();

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSStaticCredential('key1234', '');

        $actual = $provider->getStaticCredentials($credential);

        $this->assertSame(null, $actual);
    }

    public function testCredentialsCannotBeDecrypted()
    {
        $this->decrypter
            ->shouldReceive('decrypt')
            ->with('secret1234')
            ->andReturn(null);

        $this->logger
            ->shouldReceive('critical')
            ->with(CredentialProvider::ERR_INVALID_SECRET)
            ->once();

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSStaticCredential('key1234', 'secret1234');

        $actual = $provider->getStaticCredentials($credential);

        $this->assertSame(null, $actual);
    }

    public function testStaticCredentialsIsSuccess()
    {
        $this->decrypter
            ->shouldReceive('decrypt')
            ->with('secret1234')
            ->andReturn('lolok');

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSStaticCredential('key1234', 'secret1234');

        $actual = $provider->getStaticCredentials($credential);

        $this->assertSame(['key' => 'key1234', 'secret' => 'lolok'], $actual);
    }

    public function testInternalCredentialsMissing()
    {
        $repo = Mockery::mock(EntityRepository::class);
        $this->em
            ->shouldReceive('getRepository')
            ->andReturn($repo);

        $repo
            ->shouldReceive('findOneBy')
            ->with(['isInternal' => true, 'name' => 'derpherp'])
            ->andReturnNull();

        $this->logger
            ->shouldReceive('critical')
            ->with('Missing internal credentials. Searched for "derpherp".')
            ->once();

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSRoleCredential('123456789', 'role/my-custom-role');
        $provider->setInternalCredentialName('derpherp');

        $actual = $provider->getRoleCredentials($credential, 'us-east-1');

        $this->assertSame(null, $actual);
    }

    public function testInternalCredentialWrongType()
    {
        $credentials = (new Credential)
            ->withDetails(new AWSRoleCredential('123456789', 'role-name'));

        $repo = Mockery::mock(EntityRepository::class);
        $this->em
            ->shouldReceive('getRepository')
            ->andReturn($repo);

        $repo
            ->shouldReceive('findOneBy')
            ->with(['isInternal' => true, 'name' => 'Hal Internal Credentials - AWS'])
            ->andReturn($credentials);

        $this->logger
            ->shouldReceive('critical')
            ->with('Missing internal credentials. Searched for "Hal Internal Credentials - AWS".')
            ->once();

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSRoleCredential('123456789', 'role/my-custom-role');

        $actual = $provider->getRoleCredentials($credential, 'us-east-1');

        $this->assertSame(null, $actual);
    }

    public function testAssumeCredentialsIsSuccess()
    {
        $credentials = (new Credential)
            ->withDetails(new AWSStaticCredential('key1234', 'secretabcd'));

        $repo = Mockery::mock(EntityRepository::class);
        $this->em
            ->shouldReceive('getRepository')
            ->andReturn($repo);

        $repo
            ->shouldReceive('findOneBy')
            ->with(['isInternal' => true, 'name' => 'Hal Internal Credentials - AWS'])
            ->andReturn($credentials);

        $this->decrypter
            ->shouldReceive('decrypt')
            ->with('secretabcd')
            ->andReturn('secret1234');

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSRoleCredential('123456789', 'role/my-custom-role');

        $actual = $provider->getRoleCredentials($credential, 'us-east-1');

        $this->assertTrue(is_callable($actual));
    }

    public function testUseHostCredentialsWithStaticCredentials()
    {
        $this->decrypter
            ->shouldNotReceive('decrypt')
            ->with('secret1234')
            ->andReturn('lolok');

        $AWSCredProvider = function(){
            return promise_for(new Credentials('ok', 'lolokFake!'));
        };
        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws, $useHostCredentials = true);
        $provider->setHostCredentials($AWSCredProvider);

        $credential = new AWSStaticCredential('key1234Fake', 'secret1234');

        $actual = $provider->getStaticCredentials($credential);

        $this->assertSame($AWSCredProvider, $actual);
    }

    public function testAssumeCredentialsIsSuccessWithHostCredentials()
    {
        $credentials = (new Credential)
            ->withDetails(new AWSStaticCredential('key1234', 'secretabcd'));

        $repo = Mockery::mock(EntityRepository::class);
        $this->em
            ->shouldNotReceive('getRepository')
            ->andReturn($repo);

        $repo
            ->shouldNotReceive('findOneBy')
            ->with(['isInternal' => true, 'name' => 'Hal Internal Credentials - AWS'])
            ->andReturn($credentials);

        $this->decrypter
            ->shouldNotReceive('decrypt')
            ->with('secretabcd')
            ->andReturn('secret1234');

        $provider = new CredentialProvider($this->logger, $this->decrypter, $this->em, $this->aws);
        $credential = new AWSRoleCredential('123456789', 'role/my-custom-role');

        $actual = $provider->getRoleCredentials($credential, 'us-east-1');

        $this->assertTrue(is_callable($actual));
    }
}
