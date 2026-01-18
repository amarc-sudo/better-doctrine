<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Runner;

use BetterDoctrine\StoredProcedure\Definition\ProcedureDefinition;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Platform\MySqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\PostgreSqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\SqlServerProcedureAdapter;
use BetterDoctrine\StoredProcedure\Symfony\ProcedureLocator;
use Doctrine\DBAL\Connection;

final class StaticProcedureRunner
{
    /**
     * Convenience static call to run a stored procedure by name.
     *
     * @param array<string, mixed> $parameters
     */
    public static function call(
        Connection $connection,
        string $procedureName,
        array $parameters = [],
        ?AdapterRegistry $registry = null,
        ?ProcedureLocator $locator = null
    ): int {
        $registry ??= new AdapterRegistry([
            new MySqlProcedureAdapter(),
            new PostgreSqlProcedureAdapter(),
            new SqlServerProcedureAdapter(),
        ]);

        $locator ??= new ProcedureLocator();

        foreach ($locator->load() as $definition) {
            /** @var ProcedureDefinition $definition */
            $procedure = $definition->resolve($connection->getDatabasePlatform());
            if ($procedure->getName() === $procedureName) {
                $runner = new ProcedureRunner($registry);

                return $runner->run($connection, $procedure, $parameters);
            }
        }

        throw new \RuntimeException(sprintf('Stored procedure "%s" not found.', $procedureName));
    }
}
