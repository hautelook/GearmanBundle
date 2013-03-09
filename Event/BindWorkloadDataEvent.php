<?php

namespace Hautelook\GearmanBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Hautelook\GearmanBundle\Model\GearmanJobInterface;

class BindWorkloadDataEvent extends Event
{
    protected $job;

    public function __construct(GearmanJobInterface $job)
    {
        $this->job = $job;
    }

    public function getJob()
    {
        return $this->job;
    }
}
