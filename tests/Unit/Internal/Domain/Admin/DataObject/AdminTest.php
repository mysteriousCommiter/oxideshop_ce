<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Domain\Admin\DataObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Admin;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Password;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Rights;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\UserName;

class AdminTest extends TestCase
{
    public function testFromUserInput()
    {
        $admin = Admin::fromUserInput(
            '550e8400e29b11d4a716446655440000',
            UserName::fromUserInput('test@oxideshop.de'),
            Password::fromUserInput('somePassword'),
            Rights::fromUserInput('malladmin'),
            1
        );

        $this->assertEquals('550e8400e29b11d4a716446655440000', $admin->getId());
        $this->assertEquals('test@oxideshop.de', $admin->getUserName());
        $this->assertEquals('somePassword', $admin->getPassword());
        $this->assertEquals('malladmin', $admin->getRights());
        $this->assertEquals('1', $admin->getShopId());
    }

    public function testFailsFromUserInput()
    {
        $this->expectException(InvalidArgumentException::class);

        $admin = Admin::fromUserInput(
            '550e8400e29b11d4a716446655440000asdasdasd',
            UserName::fromUserInput('test@oxideshop.de'),
            Password::fromUserInput('somePassword'),
            Rights::fromUserInput('malladmin'),
            1
        );

        $this->expectException(InvalidArgumentException::class);

        $admin = Admin::fromUserInput(
            '550e8400e29b11d4a716446655440000',
            UserName::fromUserInput('test@oxideshop.de'),
            Password::fromUserInput('somePassword'),
            Rights::fromUserInput('malladmin'),
            0
        );
    }
}
