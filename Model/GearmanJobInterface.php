<?php

namespace Hautelook\GearmanBundle\Model;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
interface GearmanJobInterface
{
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;

    /**
     * This functions needs to return a string with all parameters that need to be passed
     * on to the Gearman server, so the workload.
     * The value should not yet be serialized.
     * @return array
     */
    public function getWorkload();

    /**
     * This function needs to return the Gearman function to call
     * @return string
     */
    public function getFunctionName();
}
