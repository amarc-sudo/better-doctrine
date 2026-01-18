<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Diff;

final class ProcedureSyncPlan
{
    /**
     * @var string[]
     */
    private array $upStatements;

    /**
     * @var string[]
     */
    private array $downStatements;

    /**
     * @param string[] $upStatements
     * @param string[] $downStatements
     */
    public function __construct(array $upStatements, array $downStatements)
    {
        $this->upStatements = array_values($upStatements);
        $this->downStatements = array_values($downStatements);
    }

    /**
     * @return string[]
     */
    public function getUpStatements(): array
    {
        return $this->upStatements;
    }

    /**
     * @return string[]
     */
    public function getDownStatements(): array
    {
        return $this->downStatements;
    }
}
