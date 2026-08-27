<?php

namespace Rox\Core\Network;

final class CacheStatus
{
    const HIT = 'HIT';
    const MISS = 'MISS';
    const REVALIDATED = 'REVALIDATED';
    const STALE = 'STALE';

    static function isFromCache(string $status): bool
    {
        return $status === self::HIT || $status === self::STALE;
    }

    static function isContentUnchanged(string $status): bool
    {
        return $status === self::HIT || $status === self::REVALIDATED || $status === self::STALE;
    }
}
