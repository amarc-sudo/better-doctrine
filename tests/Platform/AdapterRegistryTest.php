<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Platform;

use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Platform\MySqlProcedureAdapter;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use PHPUnit\Framework\TestCase;

final class AdapterRegistryTest extends TestCase
{
    public function testReturnsMatchingAdapter(): void
    {
        $registry = new AdapterRegistry([new MySqlProcedureAdapter()]);

        $adapter = $registry->getAdapter(new MySQLPlatform());

        self::assertInstanceOf(MySqlProcedureAdapter::class, $adapter);
    }

    public function testThrowsWhenMissingAdapter(): void
    {
        $registry = new AdapterRegistry([]);

        $this->expectException(\RuntimeException::class);
        $registry->getAdapter(new MySQLPlatform());
    }
}
