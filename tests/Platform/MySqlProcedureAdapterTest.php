<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Platform;

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\MySqlProcedureAdapter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\TestCase;

final class MySqlProcedureAdapterTest extends TestCase
{
    private MySqlProcedureAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new MySqlProcedureAdapter();
    }

    public function testSupportsMySql(): void
    {
        self::assertTrue($this->adapter->supports(new MySQLPlatform()));
        self::assertFalse($this->adapter->supports($this->createMock(AbstractPlatform::class)));
    }

    public function testCreateAndCallStatements(): void
    {
        $procedure = new StoredProcedure(
            'publish_user',
            'UPDATE users SET published = 1 WHERE id = in_id;',
            [
                new Parameter('in_id', 'INT', ParameterMode::IN),
                new Parameter('out_status', 'INT', ParameterMode::OUT),
            ]
        );

        $create = $this->adapter->createStatement($procedure);
        self::assertStringContainsString('CREATE PROCEDURE publish_user', $create);
        self::assertStringContainsString('IN in_id INT', $create);
        self::assertStringContainsString('OUT out_status INT', $create);

        $call = $this->adapter->callStatement($procedure);
        self::assertSame('CALL publish_user(:in_id, :out_status);', $call);
    }
}
