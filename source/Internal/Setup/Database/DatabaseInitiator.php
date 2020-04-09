<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup\Database;

use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Migration\MigrationExecutorInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Exception\InitiateDatabaseException;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;

/**
 * Class DatabaseInitiator
 *
 * @package OxidEsales\EshopCommunity\Internal\Setup\Database
 */
class DatabaseInitiator implements DatabaseInitiatorInterface
{

    /** @var BasicContextInterface */
    private $context;

    /** @var MigrationExecutorInterface */
    private $migrationExecutor;

    /** @var ConnectionProviderInterface */
    private $connectionProvider;

    /** @var Connection */
    private $dbConnection;

    /**
     * DatabaseInitiator constructor.
     *
     * @param BasicContextInterface            $context
     * @param MigrationExecutorInterface  $migrationExecutor
     * @param ConnectionProviderInterface $connectionProvider
     */
    public function __construct(
        BasicContextInterface $context,
        MigrationExecutorInterface $migrationExecutor,
        ConnectionProviderInterface $connectionProvider
    ) {
        $this->context = $context;
        $this->migrationExecutor = $migrationExecutor;
        $this->connectionProvider = $connectionProvider;
    }

    public function initiateDatabase(): void
    {
        $this->dbConnection = $this->connectionProvider->get();

        $this->isPossibleToCreateAndUseView();

        $this->enterInitialData();
    }

    /**
     * @throws InitiateDatabaseException
     */
    private function isPossibleToCreateAndUseView(): void
    {
        try {
            $this->executeSqlQuery('CREATE OR REPLACE VIEW oxviewtest As SELECT 1');
            $this->executeSqlQuery('SELECT * from oxviewtest');
            $this->executeSqlQuery('DROP VIEW oxviewtest');
        } catch (\Throwable $exception) {
            throw new InitiateDatabaseException(
                InitiateDatabaseException::VIEW_CREATE_PROBLEM,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws InitiateDatabaseException
     */
    private function enterInitialData(): void
    {
        $sqlFilePath = $this->context->getCommunityEditionSourcePath() . '/Internal/Setup/Database/Sql';
        $this->executeSqlQueryFromFile("$sqlFilePath/database_schema.sql");
        $this->executeSqlQueryFromFile("$sqlFilePath/initial_data.sql");

        try {
            $this->migrationExecutor->execute();
        } catch (\Throwable $exception) {
            throw new InitiateDatabaseException(
                InitiateDatabaseException::DATABASE_INITIATE_PROBLEM,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param string $query
     *
     * @return mixed
     * @throws InitiateDatabaseException
     */
    private function executeSqlQuery(string $query)
    {
        try {
            [$statement] = explode(' ', ltrim($query));

            if (in_array(strtoupper($statement), ['SELECT', 'SHOW'])) {
                return $this->dbConnection->query($query);
            }

            return $this->dbConnection->exec($query);
        } catch (\Throwable $exception) {
            throw new InitiateDatabaseException(
                InitiateDatabaseException::RUN_SQL_QUERY_PROBLEM,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param string $sqlFilePath
     *
     * @throws InitiateDatabaseException
     */
    private function executeSqlQueryFromFile(string $sqlFilePath): void
    {
        $queries = file_get_contents($sqlFilePath);
        if (!$queries) {
            throw new InitiateDatabaseException(InitiateDatabaseException::READ_SQL_FILE_PROBLEM);
        }

        $this->executeSqlQuery($queries);
    }
}
