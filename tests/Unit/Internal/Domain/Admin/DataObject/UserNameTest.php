<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Domain\Admin\DataObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\UserName;

class UserNameTest extends TestCase
{
    public function testFromUserInput()
    {
        $email = 'test@oxideshop.de';
        $userName = UserName::fromUserInput($email);

        $this->assertEquals($email, $userName);
    }

    public function testFailsFromUserInput()
    {
        $this->expectException(InvalidArgumentException::class);

        $email = 'te@st@oxideshop.de.de';
        UserName::fromUserInput($email);
    }
}
