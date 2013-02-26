GearmanBundle
=============

A bundle that provides an interface to submit Gearman jobs

[![Build Status](https://magnum.travis-ci.com/hautelook/GearmanBundle.png?token=xYuNngLNi9oEKhdeDuxP&branch=master)](https://magnum.travis-ci.com/hautelook/GearmanBundle)

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

To start submitting a job, first create a class that represents the job:
```php
<?php

namespace Acme\DemoBundle\GearmanJob;

use namespace Hautelook\GearmanBundle\GearmanJobInterface;

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
        return serialize(array('str' => $string));
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctionName()
    {
        return 'string_reverse';
    }
}

```
Then, in order to submit a job, you can do something like:
```php
$job = new Acme\DemoBundle\GearmanJob\StringReverse();
$job->setString('string to reverse');
$jobHandle = $this->get('hautelook_gearman.service.gearman')->addJob($job);
```

## To Do & Future plans

- Functional tests
- Ability to define the priority and background/foreground via interfaces
- Add Gearman Exceptions back in
- Add Gearman Monitor
- Add service alias "gearman"