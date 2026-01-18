<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Contract;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;

interface PlatformAwareProcedureEntityInterface
{
    public function getDefaultProcedure(): StoredProcedure;

    /**
     * @return array<class-string<\Doctrine\DBAL\Platforms\AbstractPlatform>, StoredProcedure>
     */
    public function getPlatformOverrides(): array;
}
