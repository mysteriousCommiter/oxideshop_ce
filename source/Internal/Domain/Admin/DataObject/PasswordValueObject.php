<?php

/**
* Copyright © OXID eSales AG. All rights reserved.
* See LICENSE file for license details.
*/

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Service\PasswordHashServiceInterface;

class PasswordValueObject
{
    private string $password;

    private function __construct(string $password)
    {
        $this->password = $password;
    }

    public static function fromUserInput(string $password): self
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $passwordHashService = $container->get(PasswordHashServiceInterface::class);

        return new self($passwordHashService->hash($password));
    }

    public function __toString()
    {
        return $this->password;
    }
}
