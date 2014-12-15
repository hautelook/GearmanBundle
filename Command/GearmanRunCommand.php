<?php

namespace Hautelook\GearmanBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class GearmanRunCommand
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanRunCommand extends AbstractGearmanRunCommand
{
    protected function configure()
    {
        $this
            ->setName('hautelook:gearman:run')
            ->setDescription('Run a gearman worker')
            ->addArgument(
                'fq_worker_class',
                InputArgument::REQUIRED,
                'The Fully qualified name space of the worker class'
            )
            ->addArgument(
                'method',
                InputArgument::REQUIRED,
                'The method name of the worker function'
            )
        ;
        parent::configure();
    }

    protected function getCallback(InputInterface $input, OutputInterface $output)
    {
        $fqWorkerClass = $input->getArgument('fq_worker_class');
        $method = $input->getArgument('method');

        if (!class_exists($fqWorkerClass)) {
            throw new \InvalidArgumentException("Class {$fqWorkerClass} does not exist");
        }

        $worker = new $fqWorkerClass();

        if (!method_exists($worker, $method)) {
            throw new \InvalidArgumentException("Method {$method} does not exist in {$fqWorkerClass}");
        }

        $workerReflection = new \ReflectionObject($worker);

        if ($workerReflection->implementsInterface('\Symfony\Component\DependencyInjection\ContainerAwareInterface')) {
            /** @var $workerObj ContainerAwareInterface */
            $workerObj->setContainer($this->getContainer());
        }

        return array($worker, $method);
    }
}
