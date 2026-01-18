<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Inspector;

final class ProcedureSnapshot
{
    private string $name;

    private string $definition;

    public function __construct(string $name, string $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }
}
