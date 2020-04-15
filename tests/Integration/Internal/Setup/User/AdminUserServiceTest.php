<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Setup\User;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\EshopCommunity\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Setup\User\AdminUserInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use PHPUnit\Framework\TestCase;

class AdminUserServiceTest extends TestCase
{
    use ContainerTrait;

    public function testAddMallAdminUser()
    {
        $testUser = new User();
        $testUser->oxuser__oxusername = new Field('testuser@oxideshop.dev', Field::T_RAW);
        $testUser->setPassword('test');

        $adminUserService = $this->get(AdminUserInterface::class);

        $return = $adminUserService->addAdminUser($testUser);
        $this->assertTrue($return);

        $testUser->load($testUser->getIdByUserName('testuser@oxideshop.dev'));

        $this->assertTrue($testUser->isMallAdmin());

        $testUser->delete($testUser->getId());
    }

    public function testAddAdminUser()
    {
        $testUser = new User();
        $testUser->oxuser__oxusername = new Field('testuser@oxideshop.dev', Field::T_RAW);
        $testUser->setPassword('test');

        $adminUserService = $this->get(AdminUserInterface::class);

        $return = $adminUserService->addAdminUser($testUser, 1);
        $this->assertTrue($return);

        $testUser->load($testUser->getIdByUserName('testuser@oxideshop.dev'));

        $this->assertFalse($testUser->isMallAdmin());

        $this->assertEquals(1, $testUser->oxuser__oxrights->value);

        $testUser->delete($testUser->getId());
    }
}
