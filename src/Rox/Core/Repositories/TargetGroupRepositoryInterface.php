<?php

namespace Rox\Core\Repositories;

use Rox\Core\Configuration\Models\TargetGroupModel;

interface TargetGroupRepositoryInterface
{
    /**
     * @param TargetGroupModel[] $targetGroups
     * @return void
     */
    function setTargetGroups($targetGroups);

    /**
     * @param string $id
     * @return TargetGroupModel|null
     */
    function getTargetGroup($id);
}
