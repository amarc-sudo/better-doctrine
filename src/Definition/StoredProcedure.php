<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Definition;

final class StoredProcedure
{
    private string $name;

    private string $body;

    /**
     * @var Parameter[]
     */
    private array $parameters;

    private string $language;

    private bool $deterministic;

    /**
     * @param Parameter[] $parameters
     */
    public function __construct(
        string $name,
        string $body,
        array $parameters = [],
        string $language = 'SQL',
        bool $deterministic = false
    ) {
        if ($name === '') {
            throw new \InvalidArgumentException('Procedure name cannot be empty.');
        }

        $this->name = $name;
        $this->body = $body;
        $this->parameters = array_values($parameters);
        $this->language = $language;
        $this->deterministic = $deterministic;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function isDeterministic(): bool
    {
        return $this->deterministic;
    }
}
