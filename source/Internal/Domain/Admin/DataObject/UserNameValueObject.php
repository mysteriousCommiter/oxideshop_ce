<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject;

use OxidEsales\EshopCommunity\Internal\Domain\Email\EmailValidatorService;

class UserNameValueObject
{
    /**
     * @var string
     */
    private $userName;

    private function __construct(string $userName)
    {
        $this->userName = $userName;
    }

    public static function fromUserInput(string $userName): self
    {
        if (!EmailValidatorService::isEmailValid($userName)) {
            throw new \InvalidArgumentException();
        }

        return new self($userName);
    }

    public static function fromDb(string $userName): self
    {
        return new self($userName);
    }


    public function __toString()
    {
        return $this->userName;
    }
}
