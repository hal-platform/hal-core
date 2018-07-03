<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Type;

class ScheduledActionStatusEnum
{
    use EnumTrait;

    const ERR_INVALID = '"%s" is not a valid scheduled action status option.';

    const TYPE_PENDING = 'pending';
    const TYPE_ABORTED = 'aborted';
    const TYPE_LAUNCHED = 'launched';

    /**
     * @return string
     */
    public static function defaultOption()
    {
        return self::TYPE_PENDING;
    }

    /**
     * @return string[]
     */
    public static function options()
    {
        return [
            self::TYPE_PENDING,

            self::TYPE_ABORTED,
            self::TYPE_LAUNCHED,
        ];
    }
}
