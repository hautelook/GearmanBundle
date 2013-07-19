<?php

namespace Hautelook\GearmanBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GearmanRunCommand
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hautelook:gearman:run')
            ->setDescription('Run a gearman worker')
            ->addArgument(
                'job_name',
                InputArgument::REQUIRED,
                'The name of the gearman job'
            )
            ->addArgument(
                'fq_worker_class',
                InputArgument::REQUIRED,
                'The Fully qualified domain name of the worker class'
            )
            ->addArgument(
                'method',
                InputArgument::REQUIRED,
                'The method name of the worker function'
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException in case there is no or an invalid feed url is given.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobName = $input->getArgument('job_name');
        $fqWorkerClass = $input->getArgument('fq_worker_class');
        $method = $input->getArgument('method');

        $gearman = $this->getContainer()->get('hautelook_gearman.service.gearman');
        $worker = $gearman->createWorker($jobName, $fqWorkerClass, $method);

        $output->writeln("<info>Gearman worker created for $jobName</info>");

        while ($returnCode = $worker->work()) {
            if ($returnCode != \GEARMAN_SUCCESS) {
                $output->writeln("<error>Error running job: $returnCode</error>");
                break;
            }
        }
    }
}
