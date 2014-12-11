<?php

namespace Hautelook\GearmanBundle\Event;

use Hautelook\GearmanBundle\Model\GearmanJobInterface;
use Symfony\Component\EventDispatcher\Event;

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
