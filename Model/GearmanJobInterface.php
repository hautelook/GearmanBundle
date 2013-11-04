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
     * This function can be called to set the workload. This is mostly for listeners that want to manipulate
     * workload parameters
     * @param array $workload
     */
    public function setWorkload(array $workload);

    /**
     * This function needs to return the Gearman function to call
     * @return string
     */
    public function getFunctionName();

    /**
     * This function returns a unique task-ID for this job. If null is returned, an ID will be generated.
     *
     * @see http://us3.php.net/manual/en/gearmanjob.unique.php?
     * @return null|string
     */
    public function getUnique();
}
