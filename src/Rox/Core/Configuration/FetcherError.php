<?php

namespace Rox\Core\Configuration;

final class FetcherError
{
    const NoError = 0;
    const CorruptedJson = 1;
    const EmptyJson = 2;
    const SignatureVerificationError = 3;
    const NetworkError = 4;
    const MismatchAppKey = 5;
    const Unknown = 6;
}
