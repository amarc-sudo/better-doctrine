<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Migration;

use BetterDoctrine\StoredProcedure\Definition\ProcedureDefinition;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Contract\PlatformAwareProcedureEntityInterface;
use BetterDoctrine\StoredProcedure\Diff\ProcedureDiffer;
use BetterDoctrine\StoredProcedure\Diff\ProcedureSyncPlan;
use BetterDoctrine\StoredProcedure\Inspector\ProcedureInspectorInterface;
use BetterDoctrine\StoredProcedure\Inspector\ProcedureSnapshot;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use Doctrine\DBAL\Connection;

final class ProcedureMigrationGenerator
{
    private ProcedureInspectorInterface $inspector;

    private AdapterRegistry $registry;

    private ProcedureDiffer $differ;

    public function __construct(
        ProcedureInspectorInterface $inspector,
        AdapterRegistry $registry,
        ?ProcedureDiffer $differ = null
    ) {
        $this->inspector = $inspector;
        $this->registry = $registry;
        $this->differ = $differ ?? new ProcedureDiffer();
    }

    /**
     * @param array<StoredProcedure|ProcedureDefinition|PlatformAwareProcedureEntityInterface> $procedures
     */
    public function generate(Connection $connection, array $procedures, bool $dropUnused = false): ProcedureSyncPlan
    {
        $adapter = $this->registry->getAdapter($connection->getDatabasePlatform());

        $expected = [];
        $snapshots = [];
        foreach ($procedures as $procedure) {
            if ($procedure instanceof PlatformAwareProcedureEntityInterface) {
                $definition = new ProcedureDefinition(
                    $procedure->getDefaultProcedure(),
                    $procedure->getPlatformOverrides()
                );
            } elseif ($procedure instanceof ProcedureDefinition) {
                $definition = $procedure;
            } else {
                $definition = new ProcedureDefinition($procedure);
            }

            $resolved = $definition->resolve($connection->getDatabasePlatform());

            $expected[$resolved->getName()] = $resolved;
            $snapshots[$resolved->getName()] = $this->inspector->fetchDefinition($connection, $resolved->getName());
        }

        $extraSnapshots = [];
        if ($dropUnused) {
            $existing = $this->inspector->listProcedures($connection);
            foreach ($existing as $name) {
                if (!isset($expected[$name])) {
                    $extraSnapshots[$name] = $this->inspector->fetchDefinition($connection, $name) ?? new ProcedureSnapshot($name, '');
                }
            }
        }

        return $this->differ->diff($expected, $snapshots, $adapter, $extraSnapshots, $dropUnused);
    }
}
