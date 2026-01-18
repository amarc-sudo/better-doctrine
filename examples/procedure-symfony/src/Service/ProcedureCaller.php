<?php

declare(strict_types=1);

namespace App\Service;

use BetterDoctrine\StoredProcedure\Runner\StaticProcedureRunner;
use Doctrine\DBAL\Connection;

final class ProcedureCaller
{
    public function __construct(private Connection $connection) {}

    public function publishUser(int $userId): void
    {
        StaticProcedureRunner::call($this->connection, 'publish_user', ['in_id' => $userId]);
    }
}
