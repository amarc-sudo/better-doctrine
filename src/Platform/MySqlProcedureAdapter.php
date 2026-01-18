<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Platform;

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;

final class MySqlProcedureAdapter implements ProcedurePlatformAdapterInterface
{
    public function supports(AbstractPlatform $platform): bool
    {
        return $platform instanceof MySQLPlatform;
    }

    public function createStatement(StoredProcedure $procedure): string
    {
        $parameters = $this->formatParameters($procedure);

        $body = sprintf('BEGIN %s END', rtrim($procedure->getBody(), ";\n\r\t "));

        return sprintf(
            'CREATE PROCEDURE %s(%s) %s;',
            $procedure->getName(),
            $parameters,
            $body
        );
    }

    public function replaceStatement(StoredProcedure $procedure): string
    {
        return $this->dropStatement($procedure->getName()) . PHP_EOL . $this->createStatement($procedure);
    }

    public function dropStatement(string $name): string
    {
        return sprintf('DROP PROCEDURE IF EXISTS %s;', $name);
    }

    public function callStatement(StoredProcedure $procedure): string
    {
        $placeholders = $this->formatCallPlaceholders($procedure);

        return sprintf('CALL %s(%s);', $procedure->getName(), $placeholders);
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
        $parts = array_map(
            static fn(Parameter $parameter): string => $parameter->signaturePart(),
            $procedure->getParameters()
        );

        return implode(', ', $parts);
    }

    private function formatCallPlaceholders(StoredProcedure $procedure): string
    {
        $parts = array_map(
            static fn(Parameter $parameter): string => ':' . $parameter->getName(),
            $procedure->getParameters()
        );

        return implode(', ', $parts);
    }
}
