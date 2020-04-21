<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Domain\Admin\Service;

use OxidEsales\EshopCommunity\Application\Model\User;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\Service\AdminUserServiceInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use PHPUnit\Framework\TestCase;

class AdminUserServiceTest extends TestCase
{
    use ContainerTrait;

    public function testAdminUserService()
    {
        $email = 'testuser@oxideshop.dev';
        $adminUserService = $this->get(AdminUserServiceInterface::class);

        $adminUserService->createAdmin(
            $email,
            'test123',
            '1',
            1
        );

        $testUser = new User();
        $testUser->load($testUser->getIdByUserName($email));
        $this->assertFalse($testUser->isMallAdmin());
        $this->assertEquals(1, $testUser->oxuser__oxrights->value);

        $adminUserService->updateToAdmin($email);

        $testUser->load($testUser->getIdByUserName($email));
        $this->assertTrue($testUser->isMallAdmin());
        $testUser->delete($testUser->getId());
    }
}
