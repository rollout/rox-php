<?php

namespace Rox\Core\Repositories;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\RoxTestCase;

class ExperimentRepositoryTests extends RoxTestCase
{
    public function testWillReturnNullWhenNotFound()
    {
        $exp = [
            new ExperimentModel("1", "1", "1", false, ["a"], [], "stam")
        ];

        $exRepo = new ExperimentRepository();
        $exRepo->setExperiments($exp);

        $this->assertEquals($exRepo->getExperimentByFlag("b"), null);
    }

    public function testWillReturnWhenFound()
    {
        $exp = [
            new ExperimentModel("1", "1", "1", false, ["a"], [], "stam")
        ];

        $exRepo = new ExperimentRepository();
        $exRepo->setExperiments($exp);

        $this->assertEquals($exRepo->getExperimentByFlag("a")->getId(), "1");
    }
}
