<?php

namespace Hautelook\GearmanBundle\Model;

use Hautelook\GearmanBundle\Model\GearmanJobInterface;

/**
 * Expose details about the Gearman job handle returned after the task is scheduled to run
 * @author Brandon Woodmansee <brandon.woodmansee@hautelook.com>
 */
class GearmanJobStatus
{
    protected $handle;
    protected $returnCode;
    protected $workload;
    protected $functionName;

    /**
     * Create a GearmanJobStatus object.  Store the job handle and return code for the task.
     * @param GearmanJobInterface $job        The job
     * @param string              $handle     Gearman job handle
     * @param int                 $returnCode Result of GearmanClient::returnCode()
     */
    public function __construct(GearmanJobInterface $job, $handle, $returnCode)
    {
        $this->handle = $handle;
        $this->returnCode = $returnCode;
        // Extract some job data for reporting
        $this->setFunctionName($job->getFunctionName());
        $this->setWorkload($job->getWorkload());
    }

    /**
     * Return the job handle
     * @return string Job handle
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Return the GearmanClient return code.
     * @return int Return Code
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * Store the job's workload.
     * @param array $workload
     */
    protected function setWorkload($workload)
    {
        $this->workload = $workload;
    }

    /**
     * Return the job's workload.
     * @return array $workload
     */
    public function getWorkload()
    {
        return $this->workload;
    }

    /**
     * Return the job's function name.
     * @return string Name
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }

    /**
     * Set the job's function name.
     * @param string $name Job's function name
     */
    protected function setFunctionName($name)
    {
        $this->functionName = $name;
    }

    /**
     * Returns true if GearmanClient returned a GEARMAN_SUCCESS
     * returnCode for the task.
     * @return bool Success flag
     */
    public function isSuccessful()
    {
        return ($this->getReturnCode() == GEARMAN_SUCCESS);
    }
}
