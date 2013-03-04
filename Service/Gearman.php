<?php

namespace Hautelook\GearmanBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Hautelook\GearmanBundle\GearmanJobInterface;
use Hautelook\GearmanBundle\Event\BindWorkloadDataEvent;
use Hautelook\GearmanBundle\Event\GearmanEvents;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class Gearman
{
    /**
     * @var \GearmanClient
     */
    protected $gearmanClient;

    /**
     * @var Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * @param \GearmanClient  $gearmanClient
     * @param EventDispatcher $dispatcher
     */
    public function __construct(\GearmanClient $gearmanClient, EventDispatcher $dispatcher)
    {
        $this->gearmanClient = $gearmanClient;
        $this->dispatcher = $dispatcher;
    }

    /**
     * This adds a job to the Gearman queue.
     * @param GearmanJobInterface $job        The job to be done
     * @param boolean             $background Whether the job should be run in the background
     * @param int                 $priority   What priority the job should be run as
     * @return string
     */
    public function addJob(
        GearmanJobInterface $job,
        $background = true,
        $priority = GearmanJobInterface::PRIORITY_NORMAL
    ) {
        $functionToCall = $job->getFunctionName();

        $event = new BindWorkloadDataEvent($job);
        $this->dispatcher->dispatch(GearmanEvents::BIND_WORKLOAD, $event);

        $workload = $job->getWorkload();
        $workload = serialize($workload);

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
                if (method_exists($this->gearmanClient, 'doNormal')) {
                    $jobHandle = $this->gearmanClient->doNormal($functionToCall, $workload);
                } else {
                    $jobHandle = $this->gearmanClient->do($functionToCall, $workload);
                }
            } elseif (GearmanJobInterface::PRIORITY_HIGH == $priority) {
                $jobHandle = $this->gearmanClient->doHigh($functionToCall, $workload);
            } else {
                throw new \InvalidArgumentException("Priority not valid: {$priority}");
            }
        }

        return $jobHandle;
    }
}
