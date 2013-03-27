<?php

namespace Hautelook\GearmanBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RootController extends Controller
{
    public function testAction(Request $request)
    {
        $this->get('hautelook_gearman.service.gearman');

        return new Response("TestResponse");
    }
}
