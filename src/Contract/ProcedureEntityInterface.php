<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Contract;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;

interface ProcedureEntityInterface
{
    public function getProcedure(): StoredProcedure;
}
