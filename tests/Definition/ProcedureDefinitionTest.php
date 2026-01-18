<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Definition;

use BetterDoctrine\StoredProcedure\Definition\ProcedureDefinition;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\TestCase;

final class ProcedureDefinitionTest extends TestCase
{
    public function testResolvesOverride(): void
    {
        $default = new StoredProcedure('demo', 'body_default');
        $pg = new StoredProcedure('demo', 'body_pg');

        $definition = new ProcedureDefinition($default, [
            PostgreSQLPlatform::class => $pg,
        ]);

        self::assertSame('body_pg', $definition->resolve(new PostgreSQLPlatform())->getBody());
        self::assertSame('body_default', $definition->resolve(new MySQLPlatform())->getBody());
    }
}
