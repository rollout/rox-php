<?php

namespace Rox\Core\Repositories;

use Rox\Core\Configuration\Models\TargetGroupModel;

class TargetGroupRepository implements TargetGroupRepositoryInterface
{
    /**
     * @var TargetGroupModel[] $_targetGroups
     */
    private $_targetGroups;

    /**
     * @param TargetGroupModel[] $targetGroups
     * @return void
     */
    function setTargetGroups($targetGroups)
    {
        $this->_targetGroups = $targetGroups;
    }

    /**
     * @param string $id
     * @return TargetGroupModel|null
     */
    function getTargetGroup($id)
    {
        return current(array_filter($this->_targetGroups, function (TargetGroupModel $element) use ($id) {
            return $element->getId() == $id;
        }));
    }
}
