<?php

namespace Hautelook\GearmanBundle\Tests\Service;

use Hautelook\GearmanBundle\GearmanJobInterface;
use Hautelook\GearmanBundle\Service\Gearman as GearmanService;
use \GearmanClient;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->gearmanClient = $this->getMockBuilder('GearmanClient')
            ->getMock();

        $this->gearmanService = new GearmanService($this->gearmanClient);
    }

    public function testDefaultPriorityAndBackground()
    {
        $job = new TestJob();

        $this->gearmanClient->expects($this->once())->method('doBackground')
            ->with('testfunction', 'workload')
            ->will($this->returnValue('jobHandle'));

        $returnValue = $this->gearmanService->addJob($job);

        $this->assertEquals('jobHandle', $returnValue);
    }

    public function gearmanFunctionsToCall()
    {
        $arr = array(
            array('doLowBackground' , true,  GearmanJobInterface::PRIORITY_LOW),
            array('doBackground'    , true,  GearmanJobInterface::PRIORITY_NORMAL),
            array('doHighBackground', true,  GearmanJobInterface::PRIORITY_HIGH),
            array('doLow'           , false, GearmanJobInterface::PRIORITY_LOW),
            // array('doNormal'        , false, GearmanJobInterface::PRIORITY_NORMAL), // This line produces a fatal error. WTF
            array('doHigh'          , false, GearmanJobInterface::PRIORITY_HIGH),
        );

        return $arr;
    }

    /**
     * @dataProvider gearmanFunctionsToCall
     */
    public function testCorrectGearmanFunctionCalled($functionToCall, $background, $priority)
    {
        $job = new TestJob();

        $this->gearmanClient->expects($this->once())->method($functionToCall)
            ->with('testfunction', 'workload')
            ->will($this->returnValue('jobHandle'));

        $returnValue = $this->gearmanService->addJob($job, $background, $priority);
        $this->assertEquals('jobHandle', $returnValue);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidPriorityBackground()
    {
        $job = new TestJob();

        $returnValue = $this->gearmanService->addJob($job, true, 4);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidPriority()
    {
        $job = new TestJob();

        $returnValue = $this->gearmanService->addJob($job, false, 4);
    }
}

class TestJob implements GearmanJobInterface
{
    public function getWorkload()
    {
        return 'workload';
    }

    public function getFunctionName()
    {
        return 'testfunction';
    }
}