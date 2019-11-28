<?php

namespace Rox\Core\Configuration;

final class FetchedError
{
    const CorruptedJson = 0;
    const EmptyJson = 1;
    const SignatureVerificationError = 2;
    const NetworkError = 3;
    const MismatchAppKey = 4;
    const Unknown = 5;
    const NoError = 6;
}
