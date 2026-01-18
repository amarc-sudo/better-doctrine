<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Inspector;

use Doctrine\DBAL\Connection;

final class DbalProcedureInspector implements ProcedureInspectorInterface
{
    public function fetchDefinition(Connection $connection, string $name): ?ProcedureSnapshot
    {
        $platform = $connection->getDatabasePlatform();

        $sql = match (true) {
            $platform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform => 'SELECT ROUTINE_DEFINITION AS definition FROM information_schema.ROUTINES WHERE ROUTINE_TYPE = \'PROCEDURE\' AND ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = :name',
            $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform => 'SELECT routine_definition AS definition FROM information_schema.routines WHERE routine_type = \'PROCEDURE\' AND specific_schema NOT IN (\'pg_catalog\', \'information_schema\') AND routine_name = :name LIMIT 1',
            $platform instanceof \Doctrine\DBAL\Platforms\SQLServerPlatform => 'SELECT sm.definition AS definition FROM sys.sql_modules sm INNER JOIN sys.objects o ON sm.object_id = o.object_id WHERE o.[type] = \'P\' AND o.[name] = :name',
            default => null,
        };

        if ($sql === null) {
            return null;
        }

        /** @var array<string, mixed>|false $row */
        $row = $connection->fetchAssociative($sql, ['name' => $name]);
        if ($row === false || !isset($row['definition'])) {
            return null;
        }

        return new ProcedureSnapshot($name, (string) $row['definition']);
    }

    public function listProcedures(Connection $connection): array
    {
        $platform = $connection->getDatabasePlatform();

        $sql = match (true) {
            $platform instanceof \Doctrine\DBAL\Platforms\MySQLPlatform => 'SELECT ROUTINE_NAME AS name FROM information_schema.ROUTINES WHERE ROUTINE_TYPE = \'PROCEDURE\' AND ROUTINE_SCHEMA = DATABASE()',
            $platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform => 'SELECT routine_name AS name FROM information_schema.routines WHERE routine_type = \'PROCEDURE\' AND specific_schema NOT IN (\'pg_catalog\', \'information_schema\')',
            $platform instanceof \Doctrine\DBAL\Platforms\SQLServerPlatform => 'SELECT name FROM sys.objects WHERE type = \'P\'',
            default => null,
        };

        if ($sql === null) {
            return [];
        }

        /** @var list<array{name: string}> $rows */
        $rows = $connection->fetchAllAssociative($sql);

        return array_map(static fn(array $row): string => $row['name'], $rows);
    }
}
