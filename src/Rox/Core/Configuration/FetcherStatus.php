<?php

namespace Rox\Core\Configuration;

final class FetcherStatus
{
    const AppliedFromEmbedded = 0;
    const AppliedFromLocalStorage = 1;
    const AppliedFromNetwork = 2;
    const ErrorFetchedFailed = 3;
}
