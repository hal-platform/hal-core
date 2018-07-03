<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Type;

class AuditActionEnum
{
    use EnumTrait;

    const ERR_INVALID = '"%s" is not a valid audit option.';

    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';

    /**
     * @return string
     */
    public static function defaultOption()
    {
        return self::TYPE_CREATE;
    }

    /**
     * @return string[]
     */
    public static function options()
    {
        return [
            self::TYPE_CREATE,
            self::TYPE_UPDATE,
            self::TYPE_DELETE,
        ];
    }
}
