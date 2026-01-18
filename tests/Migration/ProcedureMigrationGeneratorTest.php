<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Migration;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Migration\ProcedureMigrationGenerator;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Tests\Fixtures\StubAdapter;
use BetterDoctrine\StoredProcedure\Tests\Fixtures\StubInspector;
use BetterDoctrine\StoredProcedure\Inspector\ProcedureSnapshot;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

final class ProcedureMigrationGeneratorTest extends TestCase
{
    public function testGenerateBuildsPlanWithDrops(): void
    {
        $adapter = new StubAdapter();
        $registry = new AdapterRegistry([$adapter]);
        $inspector = new StubInspector(
            [
                'existing_proc' => new ProcedureSnapshot('existing_proc', 'old-body'),
                'legacy_proc' => new ProcedureSnapshot('legacy_proc', 'legacy-body'),
            ],
            ['existing_proc', 'legacy_proc']
        );

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        $generator = new ProcedureMigrationGenerator($inspector, $registry);

        $plan = $generator->generate(
            $connection,
            [new StoredProcedure('existing_proc', 'new-body')],
            true
        );

        self::assertSame(
            ['replace existing_proc', 'drop legacy_proc'],
            $plan->getUpStatements()
        );
        self::assertSame(['drop existing_proc'], $plan->getDownStatements());
    }

    public function testGenerateWithoutDropUnusedKeepsExtra(): void
    {
        $adapter = new StubAdapter();
        $registry = new AdapterRegistry([$adapter]);
        $inspector = new StubInspector(
            [
                'existing_proc' => new ProcedureSnapshot('existing_proc', 'old-body'),
                'legacy_proc' => new ProcedureSnapshot('legacy_proc', 'legacy-body'),
            ],
            ['existing_proc', 'legacy_proc']
        );

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        $generator = new ProcedureMigrationGenerator($inspector, $registry);

        $plan = $generator->generate(
            $connection,
            [new StoredProcedure('existing_proc', 'new-body')],
            false
        );

        self::assertSame(
            ['replace existing_proc'],
            $plan->getUpStatements()
        );
        self::assertSame(['drop existing_proc'], $plan->getDownStatements());
    }

    public function testGenerateResolvesPlatformAwareDefinition(): void
    {
        $adapter = new StubAdapter();
        $registry = new AdapterRegistry([$adapter]);
        $inspector = new StubInspector([], []);

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getName')->willReturn('stub');

        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new \Doctrine\DBAL\Platforms\PostgreSQLPlatform());

        $generator = new ProcedureMigrationGenerator($inspector, $registry);

        $definition = new class implements \BetterDoctrine\StoredProcedure\Contract\PlatformAwareProcedureEntityInterface {
            public function getDefaultProcedure(): \BetterDoctrine\StoredProcedure\Definition\StoredProcedure
            {
                return new \BetterDoctrine\StoredProcedure\Definition\StoredProcedure('demo', 'default');
            }

            public function getPlatformOverrides(): array
            {
                return [
                    \Doctrine\DBAL\Platforms\PostgreSQLPlatform::class => new \BetterDoctrine\StoredProcedure\Definition\StoredProcedure('demo', 'override_pg'),
                ];
            }
        };

        $plan = $generator->generate($connection, [$definition], false);

        self::assertSame(['replace demo'], $plan->getUpStatements());
    }
}
