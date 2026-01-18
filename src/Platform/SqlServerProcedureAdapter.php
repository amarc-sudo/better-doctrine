<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Platform;

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;

final class SqlServerProcedureAdapter implements ProcedurePlatformAdapterInterface
{
    public function supports(AbstractPlatform $platform): bool
    {
        return $platform instanceof SQLServerPlatform;
    }

    public function createStatement(StoredProcedure $procedure): string
    {
        $parameters = $this->formatParameters($procedure);

        $body = rtrim($procedure->getBody(), ";\n\r\t ");

        return sprintf(
            'CREATE OR ALTER PROCEDURE %s %s AS BEGIN %s END;',
            $procedure->getName(),
            $parameters,
            $body
        );
    }

    public function replaceStatement(StoredProcedure $procedure): string
    {
        return $this->createStatement($procedure);
    }

    public function dropStatement(string $name): string
    {
        return sprintf('DROP PROCEDURE IF EXISTS %s;', $name);
    }

    public function callStatement(StoredProcedure $procedure): string
    {
        $placeholders = $this->formatCallPlaceholders($procedure);

        return sprintf('EXEC %s %s;', $procedure->getName(), $placeholders);
    }

    public function fingerprint(StoredProcedure $procedure): string
    {
        return $this->normalizeDefinition($this->createStatement($procedure));
    }

    public function normalizeDefinition(string $definition): string
    {
        $normalized = preg_replace('/\s+/', ' ', strtolower(trim($definition)));

        return $normalized ?? '';
    }

    private function formatParameters(StoredProcedure $procedure): string
    {
        if (count($procedure->getParameters()) === 0) {
            return '';
        }

        $parts = array_map(
            static function (Parameter $parameter): string {
                $mode = $parameter->getMode() === ParameterMode::IN ? '' : ' OUTPUT';

                return sprintf('@%s %s%s', $parameter->getName(), $parameter->getType(), $mode);
            },
            $procedure->getParameters()
        );

        return implode(', ', $parts);
    }

    private function formatCallPlaceholders(StoredProcedure $procedure): string
    {
        if (count($procedure->getParameters()) === 0) {
            return '';
        }

        $parts = array_map(
            static fn(Parameter $parameter): string => sprintf('@%s = :%s', $parameter->getName(), $parameter->getName()),
            $procedure->getParameters()
        );

        return implode(', ', $parts);
    }
}
