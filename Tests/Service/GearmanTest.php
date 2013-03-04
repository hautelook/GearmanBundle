<?php

namespace Hautelook\GearmanBundle\Tests\Service;


use \GearmanClient;

use Hautelook\GearmanBundle\Model\GearmanJobInterface;
use Hautelook\GearmanBundle\Service\Gearman as GearmanService;
use Hautelook\GearmanBundle\Event\GearmanEvents;
use Hautelook\GearmanBundle\Event\BindWorkloadDataEvent;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanTest extends \PHPUnit_Framework_TestCase
{
    protected $gearmanClient;
    protected $gearmanService;
    protected $eventDispatcher;

    protected function setUp()
    {
        $this->gearmanClient = $this->getMockBuilder('GearmanClient')
            ->getMock();
        $this->gearmanClient->addServer('localhost', 4730);

        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->getMock();

        $this->gearmanService = new GearmanService($this->gearmanClient, $this->eventDispatcher);
    }

    public function testDefaultPriorityAndBackground()
    {
        $job = new TestJob();

        $this->gearmanClient->expects($this->once())->method('doBackground')
            ->with('testfunction', serialize('workload'))
            ->will($this->returnValue('jobHandle'));

        $jobStatus = $this->gearmanService->addJob($job);

        $this->assertTrue($jobStatus->isSuccessful());
    }

    public function gearmanFunctionsToCall()
    {
        // $doNormal = method_exists(new GearmanClient(), 'doNormal') ? 'doNormal' : 'do';
        $arr = array(
            array('doLowBackground' , true,  GearmanJobInterface::PRIORITY_LOW),
            array('doBackground'    , true,  GearmanJobInterface::PRIORITY_NORMAL),
            array('doHighBackground', true,  GearmanJobInterface::PRIORITY_HIGH),
            array('doLow'           , false, GearmanJobInterface::PRIORITY_LOW),
            // The following line causes issues:
            // 1) Hautelook\GearmanBundle\Tests\Service\GearmanTest::testCorrectGearmanFunctionCalled with
            // data set #4 ('do', false, 1)
            // GearmanClient::do(): _client_run_task:no servers added
            // array($doNormal         , false, GearmanJobInterface::PRIORITY_NORMAL),
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
            ->with('testfunction', serialize('workload'))
            ->will($this->returnValue('jobHandle'));

        $jobStatus = $this->gearmanService->addJob($job, $background, $priority);
        $this->assertTrue($jobStatus->isSuccessful());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidPriorityBackground()
    {
        $job = new TestJob();

        $this->gearmanService->addJob($job, true, 4);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidPriority()
    {
        $job = new TestJob();

        $this->gearmanService->addJob($job, false, 4);
    }

    public function testEventDispatch()
    {
        $job = new TestJob();

        $this->gearmanClient->expects($this->once())->method('doBackground')
            ->with('testfunction', serialize('workload'))
            ->will($this->returnValue('jobHandle'));

        $event = new BindWorkloadDataEvent($job);

        // Verify that the dipatcher was called with the appropriate event
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(GearmanEvents::BIND_WORKLOAD, $event);

        $this->gearmanService->addJob($job);
    }

    public function testGearmanJobStatus()
    {
        $job = new TestJob();

        $this->gearmanClient->expects($this->once())->method('doBackground')
            ->with('testfunction', serialize('workload'))
            ->will($this->returnValue('jobHandle'));

        $jobStatus = $this->gearmanService->addJob($job);

        $this->assertEquals('jobHandle', $jobStatus->getHandle());
        $this->assertEquals($job->getWorkload(), $jobStatus->getWorkload());
        $this->assertEquals($job->getFunctionName(), $jobStatus->getFunctionName());
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
