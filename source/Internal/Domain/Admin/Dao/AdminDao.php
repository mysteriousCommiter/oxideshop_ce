<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Dao;

use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Admin;

class AdminDao implements AdminDaoInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @param QueryBuilderFactoryInterface $queryBuilderFactory
     */
    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @param Admin $admin
     */
    public function create(Admin $admin)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('oxuser')
            ->set('OXID', $admin->getId())
            ->set('OXUSERNAME', $admin->getUserName())
            ->set('OXPASSWORD', $admin->getPassword())
            ->set('OXRIGHTS', $admin->getRights())
            ->set('OXSHOPID', $admin->getShopId());

        $queryBuilder->execute();
    }

    /**
     * @param Admin $admin
     */
    public function update(Admin $admin)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->update('oxuser')
            ->set('OXUSERNAME', $admin->getUserName())
            ->set('OXPASSWORD', $admin->getPassword())
            ->set('OXRIGHTS', $admin->getRights())
            ->set('OXSHOPID', $admin->getShopId())
            ->where('OXID = :OXID')
            ->setParameters([
                OXID => $admin->getId()
            ]);

        $queryBuilder->execute();
    }


}
