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
     * @var UserName
     */
    private $userName;

    /**
     * @var Password
     */
    private $password;

    /**
     * @var Rights
     */
    private $rights;

    /**
     * @var int
     */
    private $shopId;

    private function __construct(
        $id,
        UserName $userName,
        Password $password,
        Rights $rights,
        int $shopId
    ) {
        $this->id   = $id;
        $this->userName = $userName;
        $this->password = $password;
        $this->rights   = $rights;
        $this->shopId   = $shopId;
    }

    public static function fromUserInput(
        string $userId,
        UserName $userName,
        Password $password,
        Rights $rights,
        int $shopId
    ): self {

        if (strlen($userId) !== 32) {
            throw new \InvalidArgumentException();
        }

        if ($shopId <= 0) {
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

    public static function fromDb(
        string $userId,
        UserName $userName,
        Password $password,
        Rights $rights,
        int $shopId
    ): self {
        return new self(
            $userId,
            $userName,
            $password,
            $rights,
            $shopId
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserName(): UserName
    {
        return $this->userName;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getRights(): Rights
    {
        return $this->rights;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function withNewRights(Rights $rights): self
    {
        $admin = clone $this;
        $admin->rights = $rights;
        return $admin;
    }
}
