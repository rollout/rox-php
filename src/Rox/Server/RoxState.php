<?php

namespace Rox\Server;

final class RoxState
{
    const Idle = 0;
    const SettingUp = 1;
    const Set = 2;
    const ShuttingDown = 3;
    const Corrupted = 4;
}
