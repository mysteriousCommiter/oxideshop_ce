<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Dao;

use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Admin;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\PasswordValueObject;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\RightsValueObject;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\UserNameValueObject;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\Exception\UserNotFoundException;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

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
            ->values([
                'OXID'        => ':OXID',
                'OXUSERNAME'  => ':OXUSERNAME',
                'OXPASSWORD'  => ':OXPASSWORD',
                'OXRIGHTS'    => ':OXRIGHTS',
                'OXSHOPID'    => ':OXSHOPID',
            ])
            ->setParameters([
                'OXID' => $admin->getId(),
                'OXUSERNAME' => $admin->getUserName(),
                'OXPASSWORD' => $admin->getPassword(),
                'OXRIGHTS' => $admin->getRights(),
                'OXSHOPID' => $admin->getShopId()
            ]);
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
            ->set('OXUSERNAME', ':OXUSERNAME')
            ->set('OXPASSWORD', ':OXPASSWORD')
            ->set('OXRIGHTS', ':OXRIGHTS')
            ->set('OXSHOPID', ':OXSHOPID')
            ->where('OXID = :OXID')
            ->setParameters([
                'OXID' => $admin->getId(),
                'OXUSERNAME' => $admin->getUserName(),
                'OXPASSWORD' => $admin->getPassword(),
                'OXRIGHTS' => $admin->getRights(),
                'OXSHOPID' => $admin->getShopId()
            ]);

        $queryBuilder->execute();
    }

    public function findByEmail(UserNameValueObject $email): Admin
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID', 'OXUSERNAME', 'OXPASSWORD', 'OXRIGHTS', 'OXSHOPID')
            ->from('oxuser')
            ->where('OXUSERNAME LIKE :OXUSERNAME')
            ->setParameters([
                'OXUSERNAME' => $email
            ]);

        $result = $queryBuilder->execute()->fetch();
        if (false === $result) {
            throw UserNotFoundException::byEmail($email);
        }

        return Admin::fromDb(
            $result['OXID'],
            UserNameValueObject::fromDb($result['OXUSERNAME']),
            PasswordValueObject::fromDb($result['OXPASSWORD']),
            RightsValueObject::fromDb($result['OXRIGHTS']),
            (int) $result['OXSHOPID']
        );
    }
}
