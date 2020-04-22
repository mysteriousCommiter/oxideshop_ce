<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Domain\Admin\DataObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject\Password;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;

class PasswordTest extends TestCase
{
    use ContainerTrait;

    public function testFromUserInput()
    {
        $testPassword = 'test1234';
        $password = Password::fromUserInput($testPassword);

        $this->assertNotEquals($testPassword, $password);

        $passwordServiceBridge = $this->get(PasswordServiceBridgeInterface::class);

        $this->assertTrue($passwordServiceBridge->verifyPassword($testPassword, (string) $password));
    }

    public function testFailsFromUserInput()
    {
        $this->expectException(InvalidArgumentException::class);

        Password::fromUserInput('');
    }
}
