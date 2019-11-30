<?php

namespace Rox\Core\Configuration;

final class FetcherStatus
{
    const AppliedFromEmbedded = 1;
    const AppliedFromLocalStorage = 2;
    const AppliedFromNetwork = 3;
    const ErrorFetchedFailed = 4;
}
