<?php

namespace Rox\Core\Repositories;

use Rox\Core\Configuration\Models\ExperimentModel;

interface ExperimentRepositoryInterface
{
    /**
     * @param ExperimentModel[] $experiments
     * @return void
     */
    function setExperiments($experiments);

    /**
     * @param string $flagName
     * @return ExperimentModel|null
     */
    function getExperimentByFlag($flagName);

    /**
     * @return ExperimentModel[]
     */
    function getAllExperiments();
}
