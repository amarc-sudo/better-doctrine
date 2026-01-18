<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Inspector;

use Doctrine\DBAL\Connection;

interface ProcedureInspectorInterface
{
    public function fetchDefinition(Connection $connection, string $name): ?ProcedureSnapshot;

    /**
     * @return string[]
     */
    public function listProcedures(Connection $connection): array;
}
