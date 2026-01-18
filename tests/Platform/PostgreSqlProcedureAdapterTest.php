<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Platform;

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\PostgreSqlProcedureAdapter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\TestCase;

final class PostgreSqlProcedureAdapterTest extends TestCase
{
    private PostgreSqlProcedureAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new PostgreSqlProcedureAdapter();
    }

    public function testSupportsPostgres(): void
    {
        self::assertTrue($this->adapter->supports(new PostgreSQLPlatform()));
        self::assertFalse($this->adapter->supports($this->createMock(AbstractPlatform::class)));
    }

    public function testCreateAndCallStatements(): void
    {
        $procedure = new StoredProcedure(
            'publish_user',
            'UPDATE users SET published = 1 WHERE id = in_id;',
            [
                new Parameter('in_id', 'INT', ParameterMode::IN),
            ],
            'plpgsql'
        );

        $create = $this->adapter->createStatement($procedure);
        self::assertStringContainsString('CREATE OR REPLACE PROCEDURE publish_user', $create);
        self::assertStringContainsString('LANGUAGE plpgsql', $create);

        $call = $this->adapter->callStatement($procedure);
        self::assertSame('CALL publish_user(:in_id);', $call);
    }
}
