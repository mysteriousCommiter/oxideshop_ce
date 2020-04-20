<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject;

class Admin
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var UserNameValueObject
     */
    private $userName;

    /**
     * @var PasswordValueObject
     */
    private $password;

    /**
     * @var RightsValueObject
     */
    private $rights;

    /**
     * @var int
     */
    private $shopId;

    private function __construct(
        $id,
        UserNameValueObject $userName,
        PasswordValueObject $password,
        RightsValueObject $rights,
        int $shopId
    ) {
        $this->id   = $id;
        $this->userName = $userName;
        $this->password = $password;
        $this->rights   = $rights;
        $this->shopId   = $shopId;
    }

    public static function fromUser(
        string $userId,
        UserNameValueObject $userName,
        PasswordValueObject $password,
        RightsValueObject $rights,
        int $shopId
    ): self {

        if (strlen($userId) !== 32) {
            throw new \InvalidArgumentException();
        }

        if ($shopId > 0) {
            throw new \InvalidArgumentException();
        }

        return new self(
            $userId,
            $userName,
            $password,
            $rights,
            $shopId
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return UserNameValueObject
     */
    public function getUserName(): UserNameValueObject
    {
        return $this->userName;
    }

    /**
     * @return PasswordValueObject
     */
    public function getPassword(): PasswordValueObject
    {
        return $this->password;
    }

    /**
     * @return RightsValueObject
     */
    public function getRights(): RightsValueObject
    {
        return $this->rights;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }
}
