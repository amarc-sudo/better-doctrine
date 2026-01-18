<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Diff;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Inspector\ProcedureSnapshot;
use BetterDoctrine\StoredProcedure\Platform\ProcedurePlatformAdapterInterface;

final class ProcedureDiffer
{
    /**
     * @param array<string, StoredProcedure> $expected
     * @param array<string, ProcedureSnapshot|null> $snapshots
     * @param array<string, ProcedureSnapshot> $extraSnapshots
     */
    public function diff(
        array $expected,
        array $snapshots,
        ProcedurePlatformAdapterInterface $adapter,
        array $extraSnapshots,
        bool $dropUnused
    ): ProcedureSyncPlan {
        $upStatements = [];
        $downStatements = [];

        foreach ($expected as $name => $procedure) {
            $snapshot = $snapshots[$name] ?? null;

            if ($snapshot === null) {
                $upStatements[] = $adapter->replaceStatement($procedure);
                $downStatements[] = $adapter->dropStatement($name);
                continue;
            }

            $expectedFingerprint = $adapter->fingerprint($procedure);
            $actualFingerprint = $adapter->normalizeDefinition($snapshot->getDefinition());

            if ($expectedFingerprint !== $actualFingerprint) {
                $upStatements[] = $adapter->replaceStatement($procedure);
                $downStatements[] = $adapter->dropStatement($name);
            }
        }

        if ($dropUnused) {
            foreach ($extraSnapshots as $snapshot) {
                $upStatements[] = $adapter->dropStatement($snapshot->getName());
            }
        }

        return new ProcedureSyncPlan($upStatements, $downStatements);
    }
}
