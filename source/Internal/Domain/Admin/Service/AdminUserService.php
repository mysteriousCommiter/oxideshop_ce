<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Service;

use OxidEsales\EshopCommunity\Internal\Domain\Admin\Dao\AdminDaoInterface;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Admin;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\PasswordValueObject;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\RightsValueObject;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\UserNameValueObject;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;

class AdminUserService
{
    /**
     * @var AdminDaoInterface
     */
    private $adminDao;

    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;

    /**
     * AdminUserService constructor.
     *
     * @param AdminDaoInterface $adminDao
     */
    public function __construct(
        AdminDaoInterface $adminDao,
        ShopAdapterInterface $shopAdapter
    ) {
        $this->adminDao = $adminDao;
        $this->shopAdapter = $shopAdapter;
    }

    /**
     * @param string $userName
     * @param string $password
     * @param string $rights
     * @param string $shopId
     */
    public function createAdmin(
        string $userName,
        string $password,
        string $rights = RightsValueObject::MALL_ADMIN,
        ?string $shopId = null
    ) {
        $this->adminDao->create(Admin::fromUser(
            $this->shopAdapter->generateUniqueId(),
            UserNameValueObject::fromUserInput($userName),
            PasswordValueObject::fromUserInput($password),
            RightsValueObject::fromUserInput($rights),
            (int)$shopId ?? 1
        ));
    }
}
