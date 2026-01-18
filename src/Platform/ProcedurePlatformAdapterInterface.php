<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Platform;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use Doctrine\DBAL\Platforms\AbstractPlatform;

interface ProcedurePlatformAdapterInterface
{
    public function supports(AbstractPlatform $platform): bool;

    public function createStatement(StoredProcedure $procedure): string;

    public function replaceStatement(StoredProcedure $procedure): string;

    public function dropStatement(string $name): string;

    public function callStatement(StoredProcedure $procedure): string;

    public function fingerprint(StoredProcedure $procedure): string;

    public function normalizeDefinition(string $definition): string;
}
