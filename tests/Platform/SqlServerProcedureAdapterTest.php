<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Platform;

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\SqlServerProcedureAdapter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use PHPUnit\Framework\TestCase;

final class SqlServerProcedureAdapterTest extends TestCase
{
    private SqlServerProcedureAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new SqlServerProcedureAdapter();
    }

    public function testSupportsSqlServer(): void
    {
        self::assertTrue($this->adapter->supports(new SQLServerPlatform()));
        self::assertFalse($this->adapter->supports($this->createMock(AbstractPlatform::class)));
    }

    public function testCreateAndCallStatements(): void
    {
        $procedure = new StoredProcedure(
            'publish_user',
            'UPDATE users SET published = 1 WHERE id = @in_id;',
            [
                new Parameter('in_id', 'INT', ParameterMode::IN),
                new Parameter('out_status', 'INT', ParameterMode::OUT),
            ]
        );

        $create = $this->adapter->createStatement($procedure);
        self::assertStringContainsString('CREATE OR ALTER PROCEDURE publish_user', $create);
        self::assertStringContainsString('@out_status INT OUTPUT', $create);

        $call = $this->adapter->callStatement($procedure);
        self::assertSame('EXEC publish_user @in_id = :in_id, @out_status = :out_status;', $call);
    }
}
