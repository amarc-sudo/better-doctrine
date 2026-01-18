<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Migration;

use BetterDoctrine\StoredProcedure\Inspector\DbalProcedureInspector;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Platform\MySqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\PostgreSqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\SqlServerProcedureAdapter;
use BetterDoctrine\StoredProcedure\Symfony\ProcedureLocator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop-in migration that auto-loads procedures from the configured directory and
 * generates SQL for you. Extend this class and add nothing else.
 *
 * Defaults:
 * - directory: "src/Procedure" (can be overridden via constructor argument)
 * - dropUnused: true (drops procedures not declared in your code)
 */
abstract class AutoProcedureMigration extends AbstractMigration
{
    private ProcedureMigrationGenerator $generator;

    private ProcedureLocator $locator;

    private bool $dropUnused;

    public function __construct(
        \Doctrine\DBAL\Connection $connection,
        ?string $procedureDirectory = null,
        bool $dropUnused = true
    ) {
        parent::__construct($connection);

        $this->locator = new ProcedureLocator($procedureDirectory);
        $this->generator = new ProcedureMigrationGenerator(
            new DbalProcedureInspector(),
            new AdapterRegistry([
                new MySqlProcedureAdapter(),
                new PostgreSqlProcedureAdapter(),
                new SqlServerProcedureAdapter(),
            ])
        );
        $this->dropUnused = $dropUnused;
    }

    public function up(Schema $schema): void
    {
        $plan = $this->generator->generate(
            $this->connection,
            $this->locator->load(),
            $this->dropUnused
        );

        foreach ($plan->getUpStatements() as $sql) {
            $this->addSql($sql);
        }
    }

    public function down(Schema $schema): void
    {
        $plan = $this->generator->generate(
            $this->connection,
            $this->locator->load(),
            $this->dropUnused
        );

        foreach ($plan->getDownStatements() as $sql) {
            $this->addSql($sql);
        }
    }
}
