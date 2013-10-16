GearmanBundle
=============

A bundle that provides an interface to submit Gearman jobs

[![Build Status](https://travis-ci.org/hautelook/GearmanBundle.png?branch=master)](https://travis-ci.org/hautelook/GearmanBundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/hautelook/GearmanBundle/badges/quality-score.png?s=65f1b0f6666f52666a6a038fe4b7c32190b1af39)](https://scrutinizer-ci.com/g/hautelook/GearmanBundle/)

## Introduction

This bundle provides a service to submit Gearman jobs to. The jobs are objects that need to implement the `GearmmanJobInterface`.

## Installation

Of course, you need to have the Gearman [PECL Extension](http://pecl.php.net/package/gearman) installed.
Simply run assuming you have installed composer.phar or composer binary (or add to your `composer.json` and run composer install:

```bash
$ composer require hautelook/gearman-bundle
```

You can follow `dev-master`, or use a more stable tag (recommended for various reasons). On the [Github repository](https://github.com/hautelook/GearmanBundle), or on [Packagist](http://www.packagist.org), you can always find the latest tag.

Now add the Bundle to your Kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Hautelook\GearmanBundle\HautelookGearmanBundle(),
        // ...
    );
}
```

## Configuration

To configure the bundle, edit your `config.yml`, or `config_{environment}.yml`:

```yml
# Hautelook Gearman Bundle
hautelook_gearman:
    servers:
        server1:
            host: localhost
            port: 1234
        server2:
            host: localhost
            port: 4567
```

## Usage

### Jobs

To start submitting a job, first create a class that represents the job:

```php
<?php

namespace Acme\DemoBundle\GearmanJob;

use Hautelook\GearmanBundle\GearmanJobInterface;

class StringReverse implements GearmanJobInterface
{
    private $string;

    public function setString($string)
    {
        $this->string = $string;
    }

    /**
     * {@inheritDoc}
     */
    public function getWorkload()
    {
        return array('str' => $this->string);
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionName()
    {
        return 'string_reverse';
    }

    /**
     * {@inheritDoc}
     */
    public function setWorkload(array $workload)
    {
        if (isset($workload['str'])) {
            $this->string = $str;
        }
    }
}

```

Then, in order to submit a job, you can do something like:

```php
$job = new Acme\DemoBundle\GearmanJob\StringReverse();
$job->setString('string to reverse');
$jobStatus = $this->get('hautelook_gearman.service.gearman')->addJob($job);
if (!$jobStatus->isSuccessful()) {
    $logger->err("Gearman Job " . $jobStatus->getFunctionName() . " failed with " . $jobStatus->getReturnCode());
}
```

### Event Listener

The bundle will dispatch an event of type `gearman.bind.workload` right before binding the workload to the job.
You can add a listener, to add additional information to the workload, do logging, etc.

#### Example Listener

```php
<?php

namespace Acme\DemoBundle\EventListener;

use Hautelook\GearmanBundle\Event\BindWorkloadDataEvent;

class GearmanListener
{
    public function onBindWorkload(BindWorkloadDataEvent $event)
    {
        $job = $event->getJob();

        $this->injectWorkloadEnvironment($job);
    }

    private function injectWorkloadEnvironment($job)
    {
        // Do something
    }
}
```
Define the service, and tag it as a listener:

```xml
<service id="acme.gearman.listener" class="Acme\DemoBundle\EventListener\GearmanListener">
    <tag name="kernel.event_listener" event="gearman.bind.workload" method="onBindWorkload" />
</service>
```

### Gearman Workers

You can use a command to run a single gearman worker. For example:

```bash
$ app/console hautelook:gearman:run Fully\\Qualified\\NameSpace\\ToYour\\WorkerClass
\             functionToCall jobName[, alternativeJobName,...]
```

This will then invoke the `functionToCall` on an instantiated `WorkerClass` after having picked up a job from the
`job_name`, `alternativeJobName` queues. Your worker class should look like:

```php
<?php

namespace Fully\\Qualified\\NameSpace\\To\\Your;

class WorkerClass
{
    public function functionToCall(\GearmanJob $job)
    {
        // Do the work here
    }
}
```

#### Accessing the container from the worker

If your worker needs access to the Symfony DI container, you can simply make your worker class implement
`Symfony\Component\DependencyInjection\ContainerAwareInterface`, and the container will be set for you.

### Gearman Monitor Command

The bundle provides a console command that you can run like this:

```bash
$ app/console hautelook:gearman:status
Status for Server server1

solr_update_item_inventory Jobs: 0 Workers: 0 / 0
solr_update_item_popularity Jobs: 0 Workers: 0 / 0
solr_update_item Jobs: 4261 Workers: 1 / 1
```

#### Gearman Monitor

The bundle provides a Liip compatible monitor. You can enable it by adding the following to the configuration:

```yml
# Hautelook Gearman Bundle
hautelook_gearman:
    monitor:
        solr_update_item:
            queue_size: 10,     # Maximum number of jobs in the queue
            workers: 1          # Minimum number of workers
```

Queue size violations result in a `WARNING` status, whereas worker size violations result in a CRITICAL status.

## To Do & Future plans

- Ability to define the priority and background/foreground via the job
- Add Gearman Exceptions back in
- Add service alias "gearman"
