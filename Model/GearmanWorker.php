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
     * Have the worker work. If this function returns fails, call GearmanWorker::getError() and
     * GearmanWorker::getErrorNumber() to get more detail about the failure.
     *
     * @return bool true always
     * @throws \RuntimeException if the worker returned an error.
     */
    public function work()
    {
        if (!$this->worker->work()) {
            throw new \RuntimeException();
        }

        return true;
    }

    /**
     * Returns a string describing the last error
     *
     * @return string
     */
    public function getError()
    {
        return $this->worker->error();
    }

    /**
     * Returns an int representing the last error
     *
     * @return int
     */
    public function getErrorNumber()
    {
        return $this->worker->getErrno();
    }
}
