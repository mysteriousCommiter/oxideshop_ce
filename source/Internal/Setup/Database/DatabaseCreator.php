<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup\Database;

use OxidEsales\EshopCommunity\Internal\Setup\Database\Exception\CreateDatabaseException;
use PDO;

/**
 * Class DatabaseCreator
 *
 * @package OxidEsales\EshopCommunity\Internal\Setup\Database
 */
class DatabaseCreator implements DatabaseCreatorInterface
{

    /** @var PDO */
    private $dbConnection;

    /**
     * @param string $dbHost
     * @param int    $dbPort
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbName
     *
     * @throws CreateDatabaseException
     */
    public function createDatabase(string $dbHost, int $dbPort, string $dbUser, string $dbPass, string $dbName): void
    {
        $this->getDatabaseConnection($dbHost, $dbPort, $dbUser, $dbPass);

        if ($this->isDatabaseExist($dbName)) {
            throw new CreateDatabaseException(CreateDatabaseException::DATABASE_IS_EXIST);
        }

        try {
            $this->dbConnection->exec('CREATE DATABASE ' . $dbName . ' CHARACTER SET utf8 COLLATE utf8_general_ci;');
        } catch (\Throwable $exception) {
            throw new CreateDatabaseException(
                CreateDatabaseException::CREATION_DATABASE_PROBLEM,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param string $dbHost
     * @param int    $dbPort
     * @param string $dbUser
     * @param string $dbPass
     *
     * @throws CreateDatabaseException
     */
    private function getDatabaseConnection(string $dbHost, int $dbPort, string $dbUser, string $dbPass): void
    {
        try {
            $this->dbConnection = new PDO(
                sprintf('mysql:host=%s;port=%s', $dbHost, $dbPort),
                $dbUser,
                $dbPass,
                [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
            );
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (\Throwable $exception) {
            throw new CreateDatabaseException(
                CreateDatabaseException::CONNECTION_PROBLEM,
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param string $dbName
     *
     * @return bool
     */
    private function isDatabaseExist(string $dbName): bool
    {
        try {
            $this->dbConnection->exec('USE ' . $dbName);
        } catch (\Throwable $exception) {
            return false;
        }

        return true;
    }
}
