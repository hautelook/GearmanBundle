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
                foreach ($statusInformation as $queueInfo) {
                    if (!empty($this->thresholds[$queueInfo['name']])) {

                        $threshold = $this->thresholds[$queueInfo['name']];

                        if (isset($threshold['queue_size']) && $threshold['queue_size'] < $queueInfo['queue']) {
                            if ($status != CheckResult::CRITICAL) {
                                $status = CheckResult::WARNING;
                            }
                            $message .= "{$server}: {$queueInfo['name']}: queue size should be less then {$threshold['queue_size']}, but count is {$queueInfo['queue']}";
                        }
                        if (isset($threshold['workers']) && $threshold['workers'] > $queueInfo['workers']) {
                            $status = CheckResult::CRITICAL;
                            $message .= "{$server}: {$queueInfo['name']}: queue should have at least {$threshold['workers']}, but only {$queueInfo['workers']} available";
                        }
                    }
                }
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
}
