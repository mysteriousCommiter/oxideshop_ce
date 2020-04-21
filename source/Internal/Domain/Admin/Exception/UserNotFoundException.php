<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Domain\Admin\Exception;

use function sprintf;

final class UserNotFoundException extends \Exception
{
    public static function byEmail(string $email): self
    {
        return new self(sprintf('User with email %s does not exist', $email));
    }
}
