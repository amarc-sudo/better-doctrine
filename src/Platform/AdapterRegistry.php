<?php

declare(strict_types=1);

namespace BetterDoctrine\StoredProcedure\Platform;

use Doctrine\DBAL\Platforms\AbstractPlatform;

final class AdapterRegistry
{
    /**
     * @var ProcedurePlatformAdapterInterface[]
     */
    private array $adapters;

    /**
     * @param ProcedurePlatformAdapterInterface[] $adapters
     */
    public function __construct(array $adapters)
    {
        $this->adapters = array_values($adapters);
    }

    public function getAdapter(AbstractPlatform $platform): ProcedurePlatformAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($platform)) {
                return $adapter;
            }
        }

        throw new \RuntimeException(sprintf('No adapter registered for platform "%s".', $platform::class));
    }
}
