<?php
/**
 * CompressedJSONArrayType Doctrine Type
 *
 * Consider adding the following to the "require" section of your composer.json
 *
 *     "ext-zlib": "*",
 *
 * @license MIT
 */

namespace Hal\Core\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonArrayType;

/**
 * A Doctrine Type that stores JSON Arrays with string compression into BLOBs
 *
 * @see https://gist.github.com/baileyp/8275c5774615b45fcbed
 *
 * @author Peter Bailey <b33tle@gmail.com>
 */
class CompressedJSONArrayType extends JsonArrayType
{
    const NAME = 'compressed_json_array';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getBinaryTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return parent::convertToPHPValue(null, $platform);
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $decompressed = $this->deserialize($value);
        if ($decompressed === false) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return parent::convertToPHPValue($decompressed, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $converted = parent::convertToDatabaseValue($value, $platform);

        $compressed = $this->serialize($converted);
        if ($compressed === false) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $compressed;
    }

    /**
     * @param string $value
     *
     * @return string|bool
     */
    private function serialize($value)
    {
        $compressed = gzcompress($value);
        if ($compressed === false) {
            return false;
        }

        $encoded = base64_encode($compressed);
        return $encoded;
    }

    /**
     * @param string $value
     *
     * @return string|bool
     */
    private function deserialize($value)
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $decompressed = gzuncompress($decoded);
        return $decompressed;
    }
}
