<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Definition;

use Doctrine\DBAL\Platforms\AbstractPlatform;

final class ProcedureDefinition
{
    private StoredProcedure $default;

    /**
     * @var array<class-string<AbstractPlatform>, StoredProcedure>
     */
    private array $overrides;

    /**
     * @param array<class-string<AbstractPlatform>, StoredProcedure> $overrides
     */
    public function __construct(StoredProcedure $default, array $overrides = [])
    {
        $this->default = $default;
        $this->overrides = $overrides;
    }

    public function resolve(AbstractPlatform $platform): StoredProcedure
    {
        foreach ($this->overrides as $platformClass => $procedure) {
            if (is_a($platform, $platformClass)) {
                return $procedure;
            }
        }

        return $this->default;
    }

    public function getName(): string
    {
        return $this->default->getName();
    }
}
