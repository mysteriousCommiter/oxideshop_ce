<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Service;

use OxidEsales\EshopCommunity\Internal\Domain\Admin\Dao\AdminDaoInterface;

interface AdminUserServiceInterface
{
    public function createAdmin(AdminDaoInterface $admin);

    public function updateAdmin(AdminDaoInterface $admin);
}
