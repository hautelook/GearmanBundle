<?php

namespace Hautelook\GearmanBundle\Model;

/**
 * Class GearmanWorker
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanWorker
{
    /**
     * @var \GearmanWorker
     */
    private $worker;

    /**
     * @param array<string, array> $servers
     */
    public function __construct(array $servers)
    {
        $this->worker = new \GearmanWorker();

        foreach (array_values($servers) as $serverInfo) {
            $this->worker->addServer($serverInfo['host'], $serverInfo['port']);
        }
    }

    /**
     * @param string   $jobName
     * @param callable $callback
     */
    public function addCallbackFunction($jobName, $callback)
    {
        $this->worker->addFunction($jobName, $callback);
    }

    /**
     * Have the worker work and return the return code.
     *
     * @return int
     */
    public function work()
    {
        $this->worker->work();

        return $this->worker->returnCode();
    }
}
