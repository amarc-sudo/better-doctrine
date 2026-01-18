<?php

declare(strict_types=1);

namespace App\Procedure;

use BetterDoctrine\StoredProcedure\Contract\PlatformAwareProcedureEntityInterface;
use BetterDoctrine\StoredProcedure\Contract\ProcedureEntityInterface;
use BetterDoctrine\StoredProcedure\Definition\Parameter;
use BetterDoctrine\StoredProcedure\Definition\ParameterMode;
use BetterDoctrine\StoredProcedure\Definition\StoredProcedure;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;

final class PublishUser implements PlatformAwareProcedureEntityInterface, ProcedureEntityInterface
{
    public function getProcedure(): StoredProcedure
    {
        return new StoredProcedure(
            'publish_user',
            'BEGIN UPDATE users SET published = 1 WHERE id = in_id; END',
            [
                new Parameter('in_id', 'INT', ParameterMode::IN),
            ]
        );
    }

    public function getDefaultProcedure(): StoredProcedure
    {
        return $this->getProcedure();
    }

    /**
     * @return array<class-string, StoredProcedure>
     */
    public function getPlatformOverrides(): array
    {
        return [
            PostgreSQLPlatform::class => new StoredProcedure(
                'publish_user',
                'BEGIN UPDATE users SET published = true WHERE id = in_id; END',
                [
                    new Parameter('in_id', 'INT', ParameterMode::IN),
                ],
                'plpgsql'
            ),
            SQLServerPlatform::class => new StoredProcedure(
                'publish_user',
                'UPDATE users SET published = 1 WHERE id = @in_id;',
                [
                    new Parameter('in_id', 'INT', ParameterMode::IN),
                ]
            ),
            MySQLPlatform::class => new StoredProcedure(
                'publish_user',
                'UPDATE users SET published = 1 WHERE id = in_id;',
                [
                    new Parameter('in_id', 'INT', ParameterMode::IN),
                ]
            ),
        ];
    }
}
