<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Fixtures;

use BetterDoctrine\StoredProcedure\Inspector\ProcedureInspectorInterface;
use BetterDoctrine\StoredProcedure\Inspector\ProcedureSnapshot;
use Doctrine\DBAL\Connection;

final class StubInspector implements ProcedureInspectorInterface
{
    /**
     * @var array<string, ProcedureSnapshot>
     */
    private array $definitions;

    /**
     * @var string[]
     */
    private array $names;

    /**
     * @param array<string, ProcedureSnapshot> $definitions
     * @param string[] $names
     */
    public function __construct(array $definitions, array $names)
    {
        $this->definitions = $definitions;
        $this->names = $names;
    }

    public function fetchDefinition(Connection $connection, string $name): ?ProcedureSnapshot
    {
        return $this->definitions[$name] ?? null;
    }

    public function listProcedures(Connection $connection): array
    {
        return $this->names;
    }
}
