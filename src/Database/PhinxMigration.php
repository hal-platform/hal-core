<?php
/**
 * @copyright (c) 2017 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace Hal\Core\Database;

use Phinx\Db\Adapter\AdapterWrapper;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Table;
use Phinx\Migration\AbstractMigration;

/**
 * Rules for Hal database schema:
 *
 * - Minimize logic in the database layer.
 * - Do not use adapter-specific settings (e.g. no mysql enums)
 * - No stored procedures or code in the database layer.
 * - Do not handle uniqueness in the database layer.
 *
 * - Primary keys should always be "UUID" (except for build/release jobs)
 *
 * - Foreign keys should always be nullable
 *     - Always ensure the application logic to handle relation and cascading changes
 *
 * - Dates should always be "DATETIME"
 *     - Do not use "TIMESTAMP"
 *     - Dates must always be normalized to UTC before sending to database
 *
 * - Enums should always be 20 character "VARCHAR"
 *    - Enforcing enum types should always be in the application layer
 *
 */
class PhinxMigration extends AbstractMigration
{
    /**
     * @return Table
     */
    protected function createUUIDTable($name, $primary = 'id')
    {
        $table = $this->table($name, [
            'id' => false,
            'primary_key' => $primary
        ]);

        $table
            ->addColumn($primary, 'uuid', [])
            ->create();

        return $table;
    }

    /**
     * @return array
     */
    protected function enumOptions($default)
    {
        return [
            'limit' => 20,
            'default' => $default
        ];
    }

    /**
     * Only supported options:
     * - 64kb (default)
     * - 16mb
     *
     * @param string $size
     *
     * @return array
     */
    protected function blobOptions($size = '64kb')
    {
        $limit = (strtolower($size) === '16mb') ? MysqlAdapter::BLOB_MEDIUM : MysqlAdapter::BLOB_REGULAR;

        $adapter = $this->getAdapter();

        if ($adapter instanceof AdapterWrapper) {
            $adapter = $adapter->getAdapter();
        }

        return $adapter instanceof MysqlAdapter ? ['limit' => $limit] : [];
    }

    /**
     * Only supported options:
     * - 64kb (default)
     * - 16mb
     *
     * @param string $size
     *
     * @return array
     */
    protected function textOptions($size = '64kb')
    {
        $limit = (strtolower($size) === '16mb') ? MysqlAdapter::TEXT_MEDIUM : MysqlAdapter::TEXT_REGULAR;

        $adapter = $this->getAdapter();

        if ($adapter instanceof AdapterWrapper) {
            $adapter = $adapter->getAdapter();
        }

        return $adapter instanceof MysqlAdapter ? ['limit' => $limit] : [];
    }
}
