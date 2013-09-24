<?php

namespace Hautelook\GearmanBundle\Monitor;

use Liip\Monitor\Check\Check;
use Liip\Monitor\Result\CheckResult;
use TweeGearmanStat\Queue\Gearman;

/**
 * Class GearmanMonitor
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanMonitor extends Check
{
    /**
     * @var Gearman
     */
    protected $gearman;

    /**
     * @var array
     */
    protected $thresholds;

    /**
     * @param Gearman $gearman
     * @param array   $thresholds
     */
    public function __construct(Gearman $gearman, array $thresholds)
    {
        $this->gearman = $gearman;
        $this->thresholds = $thresholds;
    }

    /**
     * @return CheckResult
     */
    public function check()
    {
        $statusInfo = $this->gearman->status();
        $status = null;
        $message = "";

        if (empty($statusInfo)) {
            $status = CheckResult::UNKNOWN;
            $message = "Unknown";
        } else {
            foreach ($statusInfo as $server => $statusInformation) {
                $this->checkForServer($server, $statusInformation, $status, $message);
            }

            if (empty($status)) {
                $status = CheckResult::OK;
                $message = "OK";
            }
        }

        return $this->buildResult($message, $status);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Gearman Queue";
    }

    /**
     * @param string $server
     * @param string $queueName
     * @param int    $threshold
     * @param int    $queueCount
     *
     * @return string
     */
    private function generateQueueSizeWarning($server, $queueName, $threshold, $queueCount)
    {
        $message = "{$server}: {$queueName}: queue size should be less then {$threshold}, but count is {$queueCount}";

        return $message;
    }

    /**
     * @param string $server
     * @param string $queueName
     * @param int    $threshold
     * @param int    $workers
     *
     * @return string
     */
    private function generateWorkerWarning($server, $queueName, $threshold, $workers)
    {
        $message = "{$server}: {$queueName}: queue should have at least {$threshold}, but only {$workers} available";

        return $message;
    }

    /**
     * @param string $server
     * @param string $queueName
     *
     * @return string
     */
    private function generateMissingQueueWarning($server, $queueName)
    {
        $message = "{$server}: {$queueName}: queue is not present.";

        return $message;
    }

    /**
     * @param string $server
     * @param array  $statusInformation
     * @param int    $status
     * @param string $message
     */
    public function checkForServer($server, $statusInformation, &$status, &$message)
    {
        $serverStatus = array();
        $messages = array();

        foreach ($statusInformation as $job) {
            $serverStatus[$job['name']] = $job;
        }

        foreach ($this->thresholds as $job => $threshold) {
            if (empty($serverStatus[$job])) {
                // There are configured thresholds for this job, but the job was not present
                $status = CheckResult::CRITICAL;
                $messages[] = $this->generateMissingQueueWarning($server, $job);
            } else {
                $threshold = $this->thresholds[$job];
                $queueInfo = $serverStatus[$job];

                if (isset($threshold['queue_size']) && $threshold['queue_size'] < $queueInfo['queue']) {
                    if ($status != CheckResult::CRITICAL) {
                        $status = CheckResult::WARNING;
                    }
                    $messages[] = $this->generateQueueSizeWarning(
                        $server,
                        $queueInfo['name'],
                        $threshold['queue_size'],
                        $queueInfo['queue']
                    );
                }
                if (isset($threshold['workers']) && $threshold['workers'] > $queueInfo['workers']) {
                    $status = CheckResult::CRITICAL;
                    $messages[] = $this->generateWorkerWarning(
                        $server,
                        $queueInfo['name'],
                        $threshold['workers'],
                        $queueInfo['workers']
                    );
                }
            }
        }

        $message = implode(' ', $messages);
    }
}
