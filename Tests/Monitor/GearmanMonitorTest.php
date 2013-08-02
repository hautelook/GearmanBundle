<?php

namespace Hautelook\GearmanBundle\Tests\Monitor;

use Hautelook\GearmanBundle\Monitor\GearmanMonitor;
use Liip\Monitor\Result\CheckResult;

/**
 * Class GearmanMonitorTest
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanMonitorTest extends \PHPUnit_Framework_TestCase
{
    public function testNoResultFromService()
    {
        $monitor = $this->getMonitor(array(), array());

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::UNKNOWN, $result->getStatus());
        $this->assertEquals("Unknown", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testOk()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 0,
                        'running' => 0,
                        'workers' => 0,
                    ),
                )
            ),
            array()
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::OK, $result->getStatus());
        $this->assertEquals("OK", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testQueueSizeOk()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 0,
                    ),
                )
            ),
            array(
                'queue_1' => array(
                    'queue_size' => 10
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::OK, $result->getStatus());
        $this->assertEquals("OK", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testQueueSizeWarning()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 11,
                        'running' => 0,
                        'workers' => 0,
                    ),
                )
            ),
            array(
                'queue_1' => array(
                    'queue_size' => 10
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::WARNING, $result->getStatus());
        $this->assertEquals("server_1: queue_1: queue size should be less then 10, but count is 11", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testWorkersOk()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 1,
                    ),
                )
            ),
            array(
                'queue_1' => array(
                    'workers' => 1
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::OK, $result->getStatus());
        $this->assertEquals("OK", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testWorkersCritical()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 1,
                    ),
                )
            ),
            array(
                'queue_1' => array(
                    'workers' => 2
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::CRITICAL, $result->getStatus());
        $this->assertEquals("server_1: queue_1: queue should have at least 2, but only 1 available", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testQueueSizeWorkersOk()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 1,
                    ),
                )
            ),
            array(
                'queue_1' => array(
                    'queue_size' => 10,
                    'workers' => 1
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::OK, $result->getStatus());
        $this->assertEquals("OK", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testQueueSizeWorkersCritical()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 1,
                    ),
                )
            ),
            array(
                'queue_1' => array(
                    'queue_size' => 9,
                    'workers' => 2
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::CRITICAL, $result->getStatus());
        $this->assertEquals("server_1: queue_1: queue size should be less then 9, but count is 10server_1: queue_1: queue should have at least 2, but only 1 available", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    public function testMultipleQueue()
    {
        $monitor = $this->getMonitor(
            array(
                'server_1' => array(
                    array(
                        'name' => 'queue_1',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 1,
                    ),
                    array(
                        'name' => 'queue_2',
                        'queue' => 10,
                        'running' => 0,
                        'workers' => 1,
                    )
                )
            ),
            array(
                'queue_2' => array(
                    'queue_size' => 10,
                    'workers' => 1
                )
            )
        );

        $result = $monitor->check();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $result);
        $this->assertEquals(CheckResult::OK, $result->getStatus());
        $this->assertEquals("OK", $result->getMessage());
        $this->assertEquals('Gearman Queue', $result->getCheckName());
    }

    protected function getMonitor($statusResult, $thresholds)
    {
        $gearman = $this->getMockBuilder('TweeGearmanStat\Queue\Gearman')
            ->disableOriginalConstructor()
            ->getMock();

        $gearman->expects($this->once())->method('status')
            ->will($this->returnValue($statusResult));

        $monitor = new GearmanMonitor($gearman, $thresholds);

        return $monitor;
    }
}
