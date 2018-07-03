<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Type;

class JobEventStageEnum
{
    use EnumTrait;

    const ERR_INVALID = '"%s" is not a valid event stage option.';

    const TYPE_UNKNOWN = 'unknown';

    const TYPE_CREATED = 'created';
    const TYPE_STARTING = 'starting';
    const TYPE_RUNNING = 'running';
    const TYPE_ENDING = 'ending';

    const TYPE_FINISHED = 'finished';
    const TYPE_SUCCESS = 'success';
    const TYPE_FAILURE = 'failure';

    /**
     * @return string
     */
    public static function defaultOption()
    {
        return self::TYPE_UNKNOWN;
    }

    /**
     * @return string[]
     */
    public static function options()
    {
        return [
            self::TYPE_UNKNOWN,

            self::TYPE_CREATED,
            self::TYPE_STARTING,
            self::TYPE_RUNNING,
            self::TYPE_ENDING,

            self::TYPE_FINISHED,
            self::TYPE_SUCCESS,
            self::TYPE_FAILURE,
        ];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function format($type)
    {
        switch ($type) {
            case self::TYPE_CREATED:
                return 'Created';

            case self::TYPE_STARTING:
                return 'Starting';

            case self::TYPE_RUNNING:
                return 'Running';

            case self::TYPE_ENDING:
                return 'Ending';

            case self::TYPE_FINISHED:
                return 'Finished';

            case self::TYPE_SUCCESS:
                return 'Success';

            case self::TYPE_FAILURE:
                return 'Failure';

            default:
                return 'Unknown';
        }
    }
}
