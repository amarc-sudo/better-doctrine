<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Runner;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Runner\StaticProcedureRunner;
use BetterDoctrine\StoredProcedure\Symfony\ProcedureLocator;
use BetterDoctrine\StoredProcedure\Tests\Fixtures\StubAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

final class StaticProcedureRunnerTest extends TestCase
{
    public function testCallRunsProcedureByName(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'p.php';
        file_put_contents(
            $file,
            "<?php\nreturn new \\BetterDoctrine\\StoredProcedure\\Definition\\StoredProcedure('p','body');"
        );

        $adapter = new StubAdapter();
        $registry = new AdapterRegistry([$adapter]);

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $connection
            ->expects(self::once())
            ->method('executeStatement')
            ->with('call p', ['k' => 'v'])
            ->willReturn(1);

        $locator = new ProcedureLocator($dir);

        $result = StaticProcedureRunner::call($connection, 'p', ['k' => 'v'], $registry, $locator);

        self::assertSame(1, $result);
    }

    public function testCallThrowsWhenProcedureMissing(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        $registry = new AdapterRegistry([new StubAdapter()]);
        $locator = new ProcedureLocator(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'empty_' . uniqid());

        $this->expectException(\RuntimeException::class);
        StaticProcedureRunner::call($connection, 'missing_proc', [], $registry, $locator);
    }

    public function testCallResolvesPlatformOverride(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'p.php';
        file_put_contents(
            $file,
            "<?php\nreturn new class implements \\BetterDoctrine\\StoredProcedure\\Contract\\PlatformAwareProcedureEntityInterface { public function getDefaultProcedure(): \\BetterDoctrine\\StoredProcedure\\Definition\\StoredProcedure { return new \\BetterDoctrine\\StoredProcedure\\Definition\\StoredProcedure('p','default'); } public function getPlatformOverrides(): array { return [\\Doctrine\\DBAL\\Platforms\\PostgreSQLPlatform::class => new \\BetterDoctrine\\StoredProcedure\\Definition\\StoredProcedure('p','override_pg')]; } };"
        );

        $adapter = new StubAdapter();
        $registry = new AdapterRegistry([$adapter]);

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new \Doctrine\DBAL\Platforms\PostgreSQLPlatform());
        $connection
            ->expects(self::once())
            ->method('executeStatement')
            ->with('call p', ['k' => 'v'])
            ->willReturn(1);

        $locator = new ProcedureLocator($dir);

        $result = StaticProcedureRunner::call($connection, 'p', ['k' => 'v'], $registry, $locator);

        self::assertSame(1, $result);
    }
}
