<?php

namespace Rox\Core\CustomProperties;

class DeviceProperty extends CustomProperty
{
    public function getName()
    {
        $name = parent::getName();
        return "rox.${name}";
    }
}
