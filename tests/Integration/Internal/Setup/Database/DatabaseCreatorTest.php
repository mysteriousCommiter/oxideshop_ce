<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Setup\Database;

use OxidEsales\EshopCommunity\Internal\Setup\Database\DatabaseCreator;
use OxidEsales\EshopCommunity\Internal\Setup\Database\Exception\CreateDatabaseException;
use OxidEsales\Facts\Config\ConfigFile;
use PHPUnit\Framework\TestCase;
use PDO;

class DatabaseCreatorTest extends TestCase
{

    private $params = [
        'dbName' => 'setup_command_db_test'
    ];

    public function tearDown(): void
    {
        // Drop database 'setup_command_db_test' after each run
        $dbConnection = new PDO(
            sprintf('mysql:host=%s;port=%s', $this->params['dbHost'], $this->params['dbPort']),
            $this->params['dbUser'],
            $this->params['dbPwd'],
            [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
        );
        $dbConnection->exec('DROP DATABASE ' . $this->params['dbName']);

        parent::tearDown();
    }

    public function testCreateDatabase(): void
    {
        $this->getDatabaseConnectionInfo();

        $databaseCreator = new DatabaseCreator();
        $databaseCreator->createDatabase(
            $this->params['dbHost'],
            $this->params['dbPort'],
            $this->params['dbUser'],
            $this->params['dbPwd'],
            $this->params['dbName']
        );

        // Database is already exist, exception must be trowed
        $this->expectException(CreateDatabaseException::class);
        $databaseCreator->createDatabase(
            $this->params['dbHost'],
            $this->params['dbPort'],
            $this->params['dbUser'],
            $this->params['dbPwd'],
            $this->params['dbName']
        );
    }

    private function getDatabaseConnectionInfo(): void
    {
        $configFile = new ConfigFile();

        $this->params['dbHost'] = $configFile->getVar('dbHost');
        $this->params['dbPort'] = $configFile->getVar('dbPort');
        $this->params['dbUser'] = $configFile->getVar('dbUser');
        $this->params['dbPwd'] = $configFile->getVar('dbPwd');
    }
}
