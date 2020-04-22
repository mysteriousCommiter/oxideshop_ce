<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Dao;

use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Admin;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\UserName;

interface AdminDaoInterface
{
    /**
     * @param Admin $admin
     */
    public function create(Admin $admin);

    /**
     * @param Admin $admin
     */
    public function update(Admin $admin);

    /**
     * @param UserName $email
     */
    public function findByEmail(UserName $email): Admin;
}
