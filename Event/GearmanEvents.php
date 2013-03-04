<?php

namespace Hautelook\GearmanBundle\Event;

final class GearmanEvents
{
    /**
     * This event is thrown before workload data is passed to the gearman server.
     * It allows a listener to modify the workload data before it is sent.
     */
    const BIND_WORKLOAD =   'gearman.bind.workload';
}
