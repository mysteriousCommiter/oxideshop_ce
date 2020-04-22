<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Domain\Admin\DataObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Rights;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;

class RightsTest extends TestCase
{
    use ContainerTrait;

    public function rightsProvider()
    {
        return [
            ['malladmin'],
            ['1'],
            ['254'],
        ];
    }
    /**
     * @dataProvider rightsProvider
     */
    public function testFromUserInput($testRights)
    {
        $testRights = 'malladmin';
        $rights = Rights::fromUserInput($testRights);

        $this->assertEquals($testRights, $rights);
    }

    public function testFailsFromUserInput()
    {
        $this->expectException(InvalidArgumentException::class);

        Rights::fromUserInput('0');

        $this->expectException(InvalidArgumentException::class);

        Rights::fromUserInput('asdasd');
    }
}
