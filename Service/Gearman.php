<?php

namespace Hautelook\GearmanBundle\Service;

use Hautelook\GearmanBundle\GearmanJobInterface;
use Hautelook\GearmanBundle\EnvironmentAwareGearmanJobInterface;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class Gearman
{
    protected $gearmanClient;
    protected $environment;

    public function __construct(\GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
    }

    /**
     *
     * @param GearmanJobInterface $job
     */
    /**
     * This adds a job to the Gearman queue.
     * @param GearmanJobInterface $job        The job to be done
     * @param boolean             $background Whether the job should be run in the background
     * @param int                 $priority   What priority the job should be run as
     * @param string
     */
    public function addJob(
        GearmanJobInterface $job,
        $background = true,
        $priority = GearmanJobInterface::PRIORITY_NORMAL
    ) {
        $functionToCall = $job->getFunctionName();
        $workload       = $job->getWorkload();

        if ($job instanceof EnvironmentAwareGearmanJobInterface) {
            $workload = $this->injectWorkloadEnvironment($workload);
        }

        if ($background) {
            if (GearmanJobInterface::PRIORITY_LOW == $priority) {
                $jobHandle = $this->gearmanClient->doLowBackground($functionToCall, $workload);
            } elseif (GearmanJobInterface::PRIORITY_NORMAL == $priority) {
                $jobHandle = $this->gearmanClient->doBackground($functionToCall, $workload);
            } elseif (GearmanJobInterface::PRIORITY_HIGH == $priority) {
                $jobHandle = $this->gearmanClient->doHighBackground($functionToCall, $workload);
            } else {
                throw new \InvalidArgumentException("Priority not valid: {$priority}");
            }
        } else {
            if (GearmanJobInterface::PRIORITY_LOW == $priority) {
                $jobHandle = $this->gearmanClient->doLow($functionToCall, $workload);
            } elseif (GearmanJobInterface::PRIORITY_NORMAL == $priority) {
                $jobHandle = $this->gearmanClient->doNormal($functionToCall, $workload);
            } elseif (GearmanJobInterface::PRIORITY_HIGH == $priority) {
                $jobHandle = $this->gearmanClient->doHigh($functionToCall, $workload);
            } else {
                throw new \InvalidArgumentException("Priority not valid: {$priority}");
            }
        }

        return $jobHandle;
    }

    /**
     * Sets the Gearman environment.
     * @param string $environment Gearman Environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Return Gearman Environment.
     * @return string Gearman Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Inject environment values for jobs that expect it.
     * @param  string $workload Serialized workload from GearmanJobInterface object
     * @return string Serialized workload with environment values injected
     */
    public function injectWorkloadEnvironment($workload)
    {
        $workload = unserialize($workload);
        $workload['site_env'] = $this->getEnvironment();
        $workload = serialize($workload);

        return $workload;
    }
}
