<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Fixtures;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\ProcedurePlatformAdapterInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class StubAdapter implements ProcedurePlatformAdapterInterface
{
    public function supports(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function createStatement(StoredProcedure $procedure): string
    {
        return 'create ' . $procedure->getName();
    }

    public function replaceStatement(StoredProcedure $procedure): string
    {
        return 'replace ' . $procedure->getName();
    }

    public function dropStatement(string $name): string
    {
        return 'drop ' . $name;
    }

    public function callStatement(StoredProcedure $procedure): string
    {
        return 'call ' . $procedure->getName();
    }

    public function fingerprint(StoredProcedure $procedure): string
    {
        return $this->normalizeDefinition($procedure->getBody());
    }

    public function normalizeDefinition(string $definition): string
    {
        return 'fp:' . trim($definition);
    }
}
