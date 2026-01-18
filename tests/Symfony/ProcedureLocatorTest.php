<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Symfony;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Symfony\ProcedureLocator;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\TestCase;

final class ProcedureLocatorTest extends TestCase
{
    public function testLoadsProceduresFromDirectory(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'publish_user.php';
        file_put_contents(
            $file,
            "<?php\nreturn new \\BetterDoctrine\\StoredProcedure\\Definition\\StoredProcedure('publish_user','body');"
        );

        $locator = new ProcedureLocator($dir);
        $definitions = $locator->load();
        $resolved = $definitions[0]->resolve(new MySQLPlatform());

        self::assertCount(1, $definitions);
        self::assertInstanceOf(StoredProcedure::class, $resolved);
        self::assertSame('publish_user', $resolved->getName());
    }

    public function testReturnsEmptyWhenDirectoryMissing(): void
    {
        $locator = new ProcedureLocator(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'not_existing_dir_' . uniqid());
        self::assertSame([], $locator->load());
    }

    public function testAcceptsProcedureEntityInterface(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'publish_user.php';
        $entity = <<<'PHP'
<?php

return new class implements \BetterDoctrine\StoredProcedure\Contract\ProcedureEntityInterface {
    public function getProcedure(): \BetterDoctrine\StoredProcedure\Definition\StoredProcedure
    {
        return new \BetterDoctrine\StoredProcedure\Definition\StoredProcedure('publish_user', 'body');
    }
};
PHP;

        file_put_contents($file, $entity);

        $locator = new ProcedureLocator($dir);
        $definitions = $locator->load();

        self::assertCount(1, $definitions);
        self::assertInstanceOf(StoredProcedure::class, $definitions[0]->resolve(new PostgreSQLPlatform()));
    }

    public function testPlatformAwareOverrideIsResolved(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'proc_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'publish_user.php';
        $platformAware = <<<'PHP'
<?php

return new class implements \BetterDoctrine\StoredProcedure\Contract\PlatformAwareProcedureEntityInterface {
    public function getDefaultProcedure(): \BetterDoctrine\StoredProcedure\Definition\StoredProcedure
    {
        return new \BetterDoctrine\StoredProcedure\Definition\StoredProcedure('publish_user', 'default');
    }

    public function getPlatformOverrides(): array
    {
        return [
            \Doctrine\DBAL\Platforms\PostgreSQLPlatform::class => new \BetterDoctrine\StoredProcedure\Definition\StoredProcedure('publish_user', 'pg-body'),
        ];
    }
};
PHP;

        file_put_contents($file, $platformAware);

        $locator = new ProcedureLocator($dir);
        $definitions = $locator->load();

        $resolvedPg = $definitions[0]->resolve(new PostgreSQLPlatform());
        $resolvedMy = $definitions[0]->resolve(new MySQLPlatform());

        self::assertSame('pg-body', $resolvedPg->getBody());
        self::assertSame('default', $resolvedMy->getBody());
    }
}
