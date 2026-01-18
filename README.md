# Better Doctrine

A bundle of upgrades to supercharge Doctrine with raw SQL: versioned stored procedures, multi-DB diffs/migrations, and one-liner execution. Think “Doctrine on steroids” by using native power from MySQL, PostgreSQL, and SQL Server.

Full docs: [Better Doctrine Wiki](https://github.com/amarc-sudo/better-doctrine/wiki)

## Install

```bash
composer require amarc-sudo/doctrine-stored-procedure
```

Docs:
- Wiki: https://github.com/amarc-sudo/better-doctrine/wiki
- Symfony example (procedures + service + auto migration) in `examples/procedure-symfony/`.

Dev helpers:

```bash
composer install
composer test
composer phpstan
composer psalm
composer lint
```

## Define procedures (simple file drop)

Put your procedure definitions in `src/Procedure` (or another folder you configure). Each PHP file returns a `StoredProcedure`, a `ProcedureEntityInterface`, or a `PlatformAwareProcedureEntityInterface`.

```php
<?php

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Contract\ProcedureEntityInterface;
use BetterDoctrine\StoredProcedure\Contract\PlatformAwareProcedureEntityInterface;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;

final class PublishUser implements PlatformAwareProcedureEntityInterface, ProcedureEntityInterface
{
    public function getProcedure(): StoredProcedure
    {
        return new StoredProcedure(
            'publish_user',
            'BEGIN UPDATE users SET published = 1 WHERE id = in_id; END',
            [new Parameter('in_id', 'INT', ParameterMode::IN)]
        );
    }

    public function getDefaultProcedure(): StoredProcedure
    {
        return $this->getProcedure();
    }

    public function getPlatformOverrides(): array
    {
        return [
            PostgreSQLPlatform::class => new StoredProcedure(
                'publish_user',
                'BEGIN UPDATE users SET published = true WHERE id = in_id; END',
                [new Parameter('in_id', 'INT', ParameterMode::IN)],
                'plpgsql'
            ),
            SQLServerPlatform::class => new StoredProcedure(
                'publish_user',
                'UPDATE users SET published = 1 WHERE id = @in_id;',
                [new Parameter('in_id', 'INT', ParameterMode::IN)]
            ),
        ];
    }
}
```

## Call it (one line)

```php
use BetterDoctrine\StoredProcedure\Runner\StaticProcedureRunner;
use Doctrine\DBAL\Connection;

/** @var Connection $connection */
StaticProcedureRunner::call($connection, 'publish_user', ['in_id' => 123]);
```

## If you need migrations

Option A (automatic): extend `AutoProcedureMigration` and it will load procedures from your configured folder (default `src/Procedure`), generate `up/down` SQL, and drop unused procedures by default.

```php
use BetterDoctrine\StoredProcedure\Migration\AutoProcedureMigration;

final class Version20260118 extends AutoProcedureMigration
{
    // nothing else to write
}
```

Option B (manual wiring):

```php
use BetterDoctrine\StoredProcedure\Migration\ProcedureMigrationGenerator;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Platform\MySqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\PostgreSqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\SqlServerProcedureAdapter;
use BetterDoctrine\StoredProcedure\Inspector\DbalProcedureInspector;

$registry = new AdapterRegistry([
    new MySqlProcedureAdapter(),
    new PostgreSqlProcedureAdapter(),
    new SqlServerProcedureAdapter(),
]);

$generator = new ProcedureMigrationGenerator(
    new DbalProcedureInspector(),
    $registry
);

$plan = $generator->generate($connection, $procedures, true);
foreach ($plan->getUpStatements() as $sql) {
    $this->addSql($sql);
}
```

## Configure the folder (Symfony)

`config/packages/better_doctrine_stored_procedure.yaml`

```yaml
better_doctrine_stored_procedure:
  directory: '%kernel.project_dir%/src/Procedure'
```

## Quality

- PHPUnit tests with targeted mocks.
- PHPStan and Psalm for static analysis.
- PHPCS (PSR-12).

## License

MIT. See `LICENSE`.
