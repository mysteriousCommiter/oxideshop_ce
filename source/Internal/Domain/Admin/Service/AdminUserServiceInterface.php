<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Service;

use OxidEsales\EshopCommunity\Internal\Domain\Admin\Dao\AdminDaoInterface;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Admin;

interface AdminUserServiceInterface
{
    public function createAdmin(
        string $userName,
        string $password,
        string $rights,
        ?int $shopId
    );

    public function getAdminByEmail(string $email): Admin;

    public function updateToAdmin(
        string $userName,
        string $rights
    );
}
