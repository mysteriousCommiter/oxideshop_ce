<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup\Database;

use OxidEsales\EshopCommunity\Internal\Setup\Database\Exception\CreateDatabaseException;

interface DatabaseCreatorInterface
{
    /**
     * @param string $dbHost
     * @param int    $dbPort
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbName
     *
     * @throws CreateDatabaseException
     */
    public function createDatabase(string $dbHost, int $dbPort, string $dbUser, string $dbPass, string $dbName): void;
}
