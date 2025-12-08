<?php declare(strict_types=1);

namespace Shopware\Tests\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Exception\ColumnDoesNotExist;
use Doctrine\DBAL\Schema\Exception\ForeignKeyDoesNotExist;
use Doctrine\DBAL\Schema\Exception\IndexDoesNotExist;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\MySQLSchemaManager;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;

/**
 * @internal
 */
trait MigrationTestTrait
{
    private ?MySQLSchemaManager $schemaManager = null;

    #[Before]
    public function startTransaction(): void
    {
        KernelLifecycleManager::getConnection()->beginTransaction();
    }

    #[After]
    public function rollbackTransaction(): void
    {
        if (!KernelLifecycleManager::getConnection()->isTransactionActive()) {
            KernelLifecycleManager::getConnection()->rollBack();
        }
    }

    protected function fetchLanguageId(Connection $connection, string $code): ?string
    {
        return $connection->fetchOne(
            'SELECT `language`.`id`
             FROM `language`
                 INNER JOIN `locale` ON `language`.`locale_id` = `locale`.`id`
             WHERE `locale`.`code` = :code
             ORDER BY `language`.`created_at` ASC
             LIMIT 1',
            ['code' => $code]
        ) ?: null;
    }

    protected function columnExists(Connection $connection, string $table, string $columnName): bool
    {
        return $this->getSchemaManager($connection)->introspectSchema()->getTable($table)->hasColumn($columnName);
    }

    protected function getColumnOfTable(Connection $connection, string $table, string $columnName): ?Column
    {
        try {
            return $this->getSchemaManager($connection)->introspectSchema()->getTable($table)->getColumn($columnName);
        } catch (ColumnDoesNotExist) {
            return null;
        }
    }

    protected function indexExists(Connection $connection, string $table, string $indexName): bool
    {
        return $this->getSchemaManager($connection)->introspectSchema()->getTable($table)->hasIndex($indexName);
    }

    protected function getIndexOfTable(Connection $connection, string $table, string $indexName): ?Index
    {
        try {
            return $this->getSchemaManager($connection)->introspectSchema()->getTable($table)->getIndex($indexName);
        } catch (IndexDoesNotExist) {
            return null;
        }
    }

    protected function getForeignKeyOfTable(Connection $connection, string $table, string $foreignKeyName): ?ForeignKeyConstraint
    {
        try {
            return $this->getSchemaManager($connection)->introspectSchema()->getTable($table)->getForeignKey($foreignKeyName);
        } catch (ForeignKeyDoesNotExist) {
            return null;
        }
    }

    protected function getSchemaManager(Connection $connection): MySQLSchemaManager
    {
        if ($this->schemaManager === null) {
            $schemaManager = $connection->createSchemaManager();
            static::assertInstanceOf(MySQLSchemaManager::class, $schemaManager);
            $this->schemaManager = $schemaManager;
        }

        return $this->schemaManager;
    }
}
