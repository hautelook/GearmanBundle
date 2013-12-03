<?php

namespace Hautelook\GearmanBundle\Tests\Functional;

/**
 * @group functional
 */
class ControllerTest extends TestCase
{
    public function testServiceSetup()
    {
        $client = $this->createClient();
        $gearmanService = $client->getContainer()->get('hautelook_gearman.service.gearman');
        $this->assertInstanceOf('Hautelook\GearmanBundle\Service\Gearman', $gearmanService);
    }

    public function testServiceAlias()
    {
        $client = $this->createClient();
        $gearmanService = $client->getContainer()->get('gearman');
        $this->assertInstanceOf('Hautelook\GearmanBundle\Service\Gearman', $gearmanService);
    }
}
