<?php

declare(strict_types=1);

use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Inspector\DbalProcedureInspector;
use BetterDoctrine\StoredProcedure\Migration\ProcedureMigrationGenerator;
use BetterDoctrine\StoredProcedure\Platform\AdapterRegistry;
use BetterDoctrine\StoredProcedure\Platform\MySqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\PostgreSqlProcedureAdapter;
use BetterDoctrine\StoredProcedure\Platform\SqlServerProcedureAdapter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Example migration showing how to sync stored procedures using the library.
 */
final class ExampleMigration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Demonstrates stored procedure management with BetterDoctrine.';
    }

    public function up(Schema $schema): void
    {
        $plan = $this->generator()->generate(
            $this->connection,
            $this->procedures(),
            true
        );

        foreach ($plan->getUpStatements() as $sql) {
            $this->addSql($sql);
        }
    }

    public function down(Schema $schema): void
    {
        $plan = $this->generator()->generate(
            $this->connection,
            $this->procedures(),
            true
        );

        foreach ($plan->getDownStatements() as $sql) {
            $this->addSql($sql);
        }
    }

    /**
     * @return StoredProcedure[]
     */
    private function procedures(): array
    {
        return [
            new StoredProcedure(
                'publish_user',
                'BEGIN UPDATE users SET published = 1 WHERE id = in_id; END',
                [
                    new Parameter('in_id', 'INT', ParameterMode::IN),
                ]
            ),
        ];
    }

    private function generator(): ProcedureMigrationGenerator
    {
        $registry = new AdapterRegistry([
            new MySqlProcedureAdapter(),
            new PostgreSqlProcedureAdapter(),
            new SqlServerProcedureAdapter(),
        ]);

        return new ProcedureMigrationGenerator(
            new DbalProcedureInspector(),
            $registry
        );
    }
}
