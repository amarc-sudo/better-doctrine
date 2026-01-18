<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Runner;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use Doctrine\DBAL\Connection;

final class ProcedureRunner
{
    private AdapterRegistry $registry;

    public function __construct(AdapterRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Execute the stored procedure with provided parameters.
     *
     * @param array<string, mixed> $parameters
     */
    public function run(Connection $connection, StoredProcedure $procedure, array $parameters = []): int
    {
        $adapter = $this->registry->getAdapter($connection->getDatabasePlatform());
        $sql = $adapter->callStatement($procedure);

        return (int) $connection->executeStatement($sql, $parameters);
    }
}
