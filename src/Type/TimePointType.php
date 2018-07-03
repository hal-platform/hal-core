<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;
use QL\MCP\Common\Clock;
use QL\MCP\Common\Time\TimePoint;

class TimePointType extends DateTimeType
{
    const NAME = 'timepoint';

    private static $clock;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Convert TimePoint to database value
     *
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof TimePoint) {
            return $value->format($platform->getDateTimeFormatString(), 'UTC');
        }

        return null;
    }

    /**
     * Convert database value to TimePoint
     *
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value || !is_string($value)) {
            return null;
        }

        $timepoint = self::getParsingClock()->fromString($value, 'Y-m-d H:i:s');

        if (!$timepoint) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $timepoint;
    }

    /**
     * @param Clock|null $clock
     *
     * @return Clock
     */
    public static function getParsingClock(?Clock $clock = null)
    {
        if (func_num_args()) {
            self::$clock = $clock;
        }

        if (!self::$clock) {
            self::$clock = new Clock('now', 'UTC');
        }

        return self::$clock;
    }
}
