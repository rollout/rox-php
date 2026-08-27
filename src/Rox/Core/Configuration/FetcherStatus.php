<?php

namespace Rox\Core\Configuration;

final class FetcherStatus
{
    const AppliedFromEmbedded = 1;
    const AppliedFromLocalStorage = 2;
    const AppliedFromNetwork = 3;
    const ErrorFetchedFailed = 4;
    // Served from a cached config past its normal TTL because a live fetch failed
    // (transport error or 5xx from the CDN).
    const AppliedFromStaleCache = 5;
}
