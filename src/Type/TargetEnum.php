<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Type;

class TargetEnum
{
    use EnumTrait;

    const ERR_INVALID = '"%s" is not a valid target option.';

    const TYPE_RSYNC = 'rsync';
    const TYPE_EB = 'eb';
    const TYPE_ELB = 'elb';
    const TYPE_S3 = 's3';
    const TYPE_CD = 'cd';
    const TYPE_SCRIPT = 'script';

    /**
     * @return string
     */
    public static function defaultOption()
    {
        return self::TYPE_RSYNC;
    }

    /**
     * @return string[]
     */
    public static function options()
    {
        return [
            self::TYPE_RSYNC,
            self::TYPE_EB,
            self::TYPE_ELB,
            self::TYPE_S3,
            self::TYPE_CD,
            self::TYPE_SCRIPT,
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
            case self::TYPE_CD:
                return 'CodeDeploy';

            case self::TYPE_EB:
                return 'Elastic Beanstalk';

            case self::TYPE_ELB:
                return 'Elastic Load Balancer';

            case self::TYPE_S3:
                return 'S3';

            case self::TYPE_SCRIPT:
                return 'Script';

            case self::TYPE_RSYNC:
                return 'RSync';

            default:
                return 'Unknown';
        }
    }
}
