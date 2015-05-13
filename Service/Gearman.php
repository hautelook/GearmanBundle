<?php

namespace Hautelook\GearmanBundle\Service;

use Hautelook\GearmanBundle\Event\BindWorkloadDataEvent;
use Hautelook\GearmanBundle\Event\GearmanEvents;
use Hautelook\GearmanBundle\Model\GearmanJobInterface;
use Hautelook\GearmanBundle\Model\GearmanJobStatus;
use Hautelook\GearmanBundle\Model\GearmanWorker;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array<string, array>
     */
    protected $servers;

    /**
     * @param \GearmanClient           $gearmanClient
     * @param EventDispatcherInterface $dispatcher
     * @param                          $servers
     */
    public function __construct(\GearmanClient $gearmanClient, EventDispatcherInterface $dispatcher, $servers)
    {
        $this->gearmanClient = $gearmanClient;
        $this->dispatcher = $dispatcher;
        $this->servers = $servers;
    }

    /**
     * This adds a job to the Gearman queue.
     *
     * @param GearmanJobInterface $job        The job to be done
     * @param boolean             $background Whether the job should be run in the background
     * @param int                 $priority   What priority the job should be run as
     *
     * @throws \InvalidArgumentException
     * @return GearmanJobStatus          Object containing the job handle and return code for the
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

        //GEARMAN_COULD_NOT_CONNECT: 26
        if (@$this->gearmanClient->ping($workload) !== 26) {
            return new GearmanJobStatus($job, null, $this->gearmanClient->returnCode());
        }

        if ($background) {
            if (GearmanJobInterface::PRIORITY_LOW == $priority) {
                $jobHandle = $this->gearmanClient->doLowBackground($functionToCall, $workload, $job->getUnique());
            } elseif (GearmanJobInterface::PRIORITY_NORMAL == $priority) {
                $jobHandle = $this->gearmanClient->doBackground($functionToCall, $workload, $job->getUnique());
            } elseif (GearmanJobInterface::PRIORITY_HIGH == $priority) {
                $jobHandle = $this->gearmanClient->doHighBackground($functionToCall, $workload, $job->getUnique());
            } else {
                throw new \InvalidArgumentException("Priority not valid: {$priority}");
            }
        } else {
            if (GearmanJobInterface::PRIORITY_LOW == $priority) {
                $jobHandle = $this->gearmanClient->doLow($functionToCall, $workload, $job->getUnique());
            } elseif (GearmanJobInterface::PRIORITY_NORMAL == $priority) {
                if (method_exists($this->gearmanClient, 'doNormal')) {
                    $jobHandle = $this->gearmanClient->doNormal($functionToCall, $workload, $job->getUnique());
                } else {
                    $jobHandle = $this->gearmanClient->do($functionToCall, $workload, $job->getUnique());
                }
            } elseif (GearmanJobInterface::PRIORITY_HIGH == $priority) {
                $jobHandle = $this->gearmanClient->doHigh($functionToCall, $workload, $job->getUnique());
            } else {
                throw new \InvalidArgumentException("Priority not valid: {$priority}");
            }
        }

        return new GearmanJobStatus($job, $jobHandle, $this->gearmanClient->returnCode());
    }

    /**
     * Creates a worker with the given job name(s). The worker will call the $callBackName function
     * on a $fqClassName object.
     *
     * @param array              $jobNames
     * @param string             $fqClassName
     * @param string             $callBackName
     * @param ContainerInterface $container
     *
     * @throws \InvalidArgumentException if the callback is invalid
     *
     * @return GearmanWorker
     */
    public function createWorker(array $jobNames, $fqClassName, $callBackName, ContainerInterface $container = null)
    {
        $worker = new GearmanWorker($this->servers);

        if (!class_exists($fqClassName)) {
            throw new \InvalidArgumentException("Class {$fqClassName} does not exist");
        }

        $workerObj = new $fqClassName();

        if (!method_exists($workerObj, $callBackName)) {
            throw new \InvalidArgumentException("Method {$callBackName} does not exist in {$fqClassName}");
        }

        $workerReflObj = new \ReflectionObject($workerObj);

        if ($workerReflObj->implementsInterface('\Symfony\Component\DependencyInjection\ContainerAwareInterface')) {
            /** @var $workerObj ContainerAwareInterface */
            $workerObj->setContainer($container);
        }

        foreach ($jobNames as $jobName) {
            $worker->addCallbackFunction($jobName, array($workerObj, $callBackName));
        }

        return $worker;
    }

    /**
     * This function creates a worker that does a NOOP with the job, i.e. just consumes the workload
     *
     * @param string $jobName
     *
     * @return GearmanWorker
     */
    public function createNoopWorker($jobName)
    {
        $worker = new GearmanWorker($this->servers);

        $noop = function () {
            // Do nothing
        };

        $worker->addCallbackFunction($jobName, $noop);

        return $worker;
    }

    /**
     * Returns the original GearmanClient to expose all available functionality.
     *
     * @return \GearmanClient
     */
    public function getGearmanClient()
    {
        return $this->gearmanClient;
    }
}
