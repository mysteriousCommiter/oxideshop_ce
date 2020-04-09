<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Setup\Database;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Migration\MigrationExecutorInterface;
use OxidEsales\EshopCommunity\Internal\Setup\Database\DatabaseInitiator;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\Facts\Facts;
use PHPUnit\Framework\TestCase;

class DatabaseInitiatorTest extends TestCase
{
    use ContainerTrait;

    public function testInitiateDatabase(): void
    {
        $databaseInitiator = $this->getDatabaseInitiator();
        $databaseInitiator->initiateDatabase();
    }

    /**
     * @return DatabaseInitiator
     */
    private function getDatabaseInitiator(): DatabaseInitiator
    {
        $context = $this->getMockBuilder(BasicContext::class)
            ->setMethods(['getCommunityEditionSourcePath'])
            ->getMock();

        $context->method('getCommunityEditionSourcePath')->willReturn((new Facts())->getCommunityEditionSourcePath());

        $connectionProvider = $this->getMockBuilder(ConnectionProviderInterface::class)
            ->getMock();

        $migrationExecutor = $this->getMockBuilder(MigrationExecutorInterface::class)
            ->getMock();

        return new DatabaseInitiator($context, $migrationExecutor, $connectionProvider);
    }
}
