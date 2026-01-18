<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Runner;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Runner\ProcedureRunner;
use BetterDoctrine\StoredProcedure\Tests\Fixtures\StubAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

final class ProcedureRunnerTest extends TestCase
{
    public function testRunExecutesCallStatement(): void
    {
        $adapter = new StubAdapter();
        $registry = new AdapterRegistry([$adapter]);
        $runner = new ProcedureRunner($registry);

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $connection
            ->expects(self::once())
            ->method('executeStatement')
            ->with('call sample_proc', ['foo' => 'bar'])
            ->willReturn(1);

        $procedure = new StoredProcedure('sample_proc', 'body');

        $result = $runner->run($connection, $procedure, ['foo' => 'bar']);

        self::assertSame(1, $result);
    }
}
