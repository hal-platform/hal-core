<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Crypto;

/**
 * Convenience factory to build encryption from a hex-encoded secret stored on filesystem.
 */
class CryptoFilesystemFactory
{
    const ERR_KEY_PATH = 'Path to secret key is invalid. No key file exists at "%s".';
    const ERR_INVALID_KEY = 'Secret key at path "%s" is invalid.';

    /**
     * @var callable
     */
    private $keyLoader;

    /**
     * @var string
     */
    private $keyPath;

    /**
     * @param string $keyPath
     * @param callable|null $keyLoader
     */
    public function __construct(string $keyPath, ?callable $keyLoader = null)
    {
        $this->keyPath = $keyPath;
        $this->keyLoader = $keyLoader ?: $this->getDefaultSecretLoader();
    }

    /**
     * @throws CryptoException
     *
     * @return Encryption
     */
    public function getCrypto()
    {
        if (!file_exists($this->keyPath)) {
            throw new CryptoException(sprintf(self::ERR_KEY_PATH, $this->keyPath));
        }

        $key = call_user_func($this->keyLoader, $this->keyPath);
        $key = trim($key);

        if (strlen($key) % 2 !== 0) {
            throw new CryptoException(sprintf(self::ERR_INVALID_KEY, $this->keyPath));
        }

        $key = hex2bin($key);

        return new Encryption($key);
    }

    /**
     * @return callable
     */
    protected function getDefaultSecretLoader()
    {
        return 'file_get_contents';
    }
}
