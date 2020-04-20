<?php

/**
* Copyright © OXID eSales AG. All rights reserved.
* See LICENSE file for license details.
*/

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\DataObject;

class RightsValueObject
{
    const MALL_ADMIN = 'malladmin';

    private string $rights;

    private function __construct(string $rights)
    {
        $this->$rights = $rights;
    }

    public static function fromUserInput(string $rights): self
    {
        if ($rights == self::MALL_ADMIN || is_numeric($rights)) {
            return new self($rights);
        }
        throw new \InvalidArgumentException();
    }

    public function __toString()
    {
        return $this->rights;
    }
}

