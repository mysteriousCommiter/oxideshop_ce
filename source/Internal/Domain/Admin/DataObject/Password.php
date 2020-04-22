<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Domain\Authentication\Bridge\PasswordServiceBridgeInterface;

class Password
{
    /**
     * @var string
     */
    private $password;

    private function __construct(string $password)
    {
        $this->password = $password;
    }

    public static function fromUserInput(string $password): self
    {
        if (strlen($password) == 0) {
            throw new \InvalidArgumentException();
        }
        $container = ContainerFactory::getInstance()->getContainer();
        $passwordHashService = $container->get(PasswordServiceBridgeInterface::class);

        return new self($passwordHashService->hash($password));
    }

    public static function fromDb(string $password): self
    {
        return new self($password);
    }

    public function __toString()
    {
        return $this->password;
    }
}
