<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Entity;

use Hal\Core\Entity\System\UserIdentityProvider;
use Hal\Core\Entity\User\UserToken;
use PHPUnit\Framework\TestCase;
use QL\MCP\Common\Time\TimePoint;

class UserTest extends TestCase
{
    public function testDefaultValues()
    {
        $user = new User;

        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $user->id());
        $this->assertInstanceOf(TimePoint::class, $user->created());

        $this->assertSame('', $user->name());

        $this->assertSame(false, $user->isDisabled());

        $this->assertCount(0, $user->tokens());
    }

    public function testProperties()
    {
        $provider = new UserIdentityProvider;

        $user = (new User('1234'))
            ->withName('Bob Smith')
            ->withIsDisabled(true)
            ->withSetting('this', ['that'])
            ->withSetting('this2', false);

        $user->tokens()->add(new UserToken);
        $user->tokens()->add(new UserToken);

        $this->assertSame('1234', $user->id());
        $this->assertSame('Bob Smith', $user->name());
        $this->assertSame(true, $user->isDisabled());
        $this->assertSame(['that'], $user->setting('this'));
        $this->assertSame(false, $user->setting('this2'));

        $this->assertCount(2, $user->tokens());
    }

    public function testSerialization()
    {
        $provider = new UserIdentityProvider('5678');

        $user = (new User('1234', new TimePoint(2017, 12, 31, 12, 0, 0, 'UTC')))
            ->withName('Smith, Bob')
            ->withIsDisabled(true);

        $expected = <<<JSON_TEXT
{
    "id": "1234",
    "created": "2017-12-31T12:00:00Z",
    "name": "Smith, Bob",
    "is_disabled": true,
    "settings": [],
    "tokens": [],
    "identities": []
}
JSON_TEXT;

        $this->assertSame($expected, json_encode($user, JSON_PRETTY_PRINT));
    }

    public function testDefaultSerialization()
    {
        $user = new User('1', new TimePoint(2017, 12, 31, 12, 0, 0, 'UTC'));

        $expected = <<<JSON_TEXT
{
    "id": "1",
    "created": "2017-12-31T12:00:00Z",
    "name": "",
    "is_disabled": false,
    "settings": [],
    "tokens": [],
    "identities": []
}
JSON_TEXT;

        $this->assertSame($expected, json_encode($user, JSON_PRETTY_PRINT));
    }
}
