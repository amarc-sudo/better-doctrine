<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Definition;

final class Parameter
{
    private string $name;

    private string $type;

    private ParameterMode $mode;

    public function __construct(string $name, string $type, ParameterMode $mode = ParameterMode::IN)
    {
        if ($name === '') {
            throw new \InvalidArgumentException('Parameter name cannot be empty.');
        }

        $this->name = $name;
        $this->type = $type;
        $this->mode = $mode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMode(): ParameterMode
    {
        return $this->mode;
    }

    public function signaturePart(): string
    {
        return sprintf('%s %s %s', $this->mode->value, $this->name, $this->type);
    }
}
