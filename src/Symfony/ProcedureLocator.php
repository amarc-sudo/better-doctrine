<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Symfony;

use BetterDoctrine\StoredProcedure\Contract\PlatformAwareProcedureEntityInterface;
use BetterDoctrine\StoredProcedure\Contract\ProcedureEntityInterface;
use BetterDoctrine\StoredProcedure\Definition\ProcedureDefinition;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;

final class ProcedureLocator
{
    private string $directory;

    public function __construct(string $directory = null)
    {
        $this->directory = $directory ?? getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Procedure';
    }

    /**
     * Load procedure definitions from PHP files located in the configured directory.
     *
     * Each file must return either a StoredProcedure instance, a ProcedureEntityInterface,
     * a PlatformAwareProcedureEntityInterface, or an array of those. This keeps the integration simple and works with Symfony config
     * pointing to the desired directory.
     *
     * @return ProcedureDefinition[]
     */
    public function load(): array
    {
        if (!is_dir($this->directory)) {
            return [];
        }

        $files = glob($this->directory . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            return [];
        }

        $procedures = [];
        foreach ($files as $file) {
            /** @psalm-suppress UnresolvableInclude */
            $loaded = require $file;
            $procedures = array_merge($procedures, $this->normalizeLoaded($loaded, $file));
        }

        return $procedures;
    }

    /**
     * @param mixed $loaded
     * @return ProcedureDefinition[]
     */
    private function normalizeLoaded($loaded, string $file): array
    {
        $items = is_array($loaded) ? $loaded : [$loaded];

        $result = [];
        foreach ($items as $item) {
            if ($item instanceof StoredProcedure) {
                $result[] = new ProcedureDefinition($item);
                continue;
            }

            if ($item instanceof ProcedureEntityInterface) {
                $result[] = new ProcedureDefinition($item->getProcedure());
                continue;
            }

            if ($item instanceof PlatformAwareProcedureEntityInterface) {
                $result[] = new ProcedureDefinition(
                    $item->getDefaultProcedure(),
                    $item->getPlatformOverrides()
                );
                continue;
            }

            throw new \RuntimeException(sprintf('File "%s" must return StoredProcedure or ProcedureEntityInterface instances.', $file));
        }

        return $result;
    }
}
