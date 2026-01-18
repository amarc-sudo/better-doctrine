<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Tests\Diff;

use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use BetterDoctrine\StoredProcedure\Diff\ProcedureDiffer;
use BetterDoctrine\StoredProcedure\Inspector\ProcedureSnapshot;
use BetterDoctrine\StoredProcedure\Tests\Fixtures\StubAdapter;
use PHPUnit\Framework\TestCase;

final class ProcedureDifferTest extends TestCase
{
    public function testGeneratesPlanForNewChangedAndExtra(): void
    {
        $adapter = new StubAdapter();
        $differ = new ProcedureDiffer();

        $expected = [
            'create_me' => new StoredProcedure('create_me', 'new-body'),
            'update_me' => new StoredProcedure('update_me', 'new-body'),
        ];

        $snapshots = [
            'create_me' => null,
            'update_me' => new ProcedureSnapshot('update_me', 'old-body'),
        ];

        $extras = [
            'legacy' => new ProcedureSnapshot('legacy', 'legacy-body'),
        ];

        $plan = $differ->diff($expected, $snapshots, $adapter, $extras, true);

        self::assertSame(
            ['replace create_me', 'replace update_me', 'drop legacy'],
            $plan->getUpStatements()
        );
        self::assertSame(
            ['drop create_me', 'drop update_me'],
            $plan->getDownStatements()
        );
    }

    public function testNoChangesProducesEmptyPlan(): void
    {
        $adapter = new StubAdapter();
        $differ = new ProcedureDiffer();

        $expected = [
            'unchanged' => new StoredProcedure('unchanged', 'same-body'),
        ];

        $snapshots = [
            'unchanged' => new ProcedureSnapshot('unchanged', 'same-body'),
        ];

        $plan = $differ->diff($expected, $snapshots, $adapter, [], false);

        self::assertSame([], $plan->getUpStatements());
        self::assertSame([], $plan->getDownStatements());
    }
}
