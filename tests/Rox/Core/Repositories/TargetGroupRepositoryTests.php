<?php

namespace Rox\Core\Repositories;

use Rox\Core\Configuration\Models\TargetGroupModel;
use Rox\RoxTestCase;

class TargetGroupRepositoryTests extends RoxTestCase
{
    public function testWillReturnNullWhenNotFound()
    {
        $tgs = [
            new TargetGroupModel("1", "x")
        ];

        $tgRepo = new TargetGroupRepository();
        $tgRepo->setTargetGroups($tgs);

        $this->assertEquals($tgRepo->getTargetGroup("2"), null);
    }

    public function testWillReturnWhenFound()
    {
        $tgs = [
            new TargetGroupModel("1", "x")
        ];

        $tgRepo = new TargetGroupRepository();
        $tgRepo->setTargetGroups($tgs);

        $this->assertEquals($tgRepo->getTargetGroup("1")->getCondition(), "x");
    }
}
