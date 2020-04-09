<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Setup\Database\Exception;

use Exception;
use Throwable;

/**
 * Class InitiateDatabaseException
 *
 * @package OxidEsales\EshopCommunity\Internal\Setup\Database
 */
class InitiateDatabaseException extends Exception
{

    public const DATABASE_INITIATE_PROBLEM = 'Failed: Could not initiate database';
    public const VIEW_CREATE_PROBLEM = 'Failed: Could not create or use view';
    public const RUN_SQL_QUERY_PROBLEM = 'Failed: SQL query could not be run';
    public const READ_SQL_FILE_PROBLEM = 'Failed: SQL file can not be read';

    /** @var array */
    protected $metaData = [];

    public function __construct($message = '', $code = 0, Throwable $previous = null, $metaData = [])
    {
        $this->metaData = $metaData;
        parent::__construct($message, $code, $previous);
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }
}
