<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup\User;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\EshopCommunity\Application\Model\User;
use OxidEsales\EshopCommunity\Core\Exception\UserException;
use Psr\Log\LoggerInterface;

/**
 * Class AdminUserService
 *
 * @package OxidEsales\EshopCommunity\Internal\Setup\User
 */
class AdminUserService implements AdminUserInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addAdminUser(User $newAdminUser, int $shopId = null): bool
    {
        if ($shopId) {
            $newAdminUser->oxuser__oxrights = new Field($shopId, Field::T_RAW);
        } else {
            $newAdminUser->oxuser__oxrights = new Field('malladmin', Field::T_RAW);
        }

        try {
            $newAdminUser->createAdminUser();
            return true;
        } catch (UserException $exception) {
            $this->logger->error($exception);
            return false;
        }
    }
}
