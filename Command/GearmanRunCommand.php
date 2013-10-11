<?php

namespace Hautelook\GearmanBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GearmanRunMultiCommand
 *
 * Other than then GearmanRunCommand class, this console command takes multiple
 * job_names as last (not first) argument.
 *
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 * @author Anton Stöckl <anton@stoeckl.de>
 */
class GearmanRunMultiCommand extends ContainerAwareCommand
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
            ->addArgument(
                'job_name',
                InputArgument::REQUIRED,
                'The name of the gearman job'
            )
            ->addArgument(
                'additional_job_names',
                InputArgument::IS_ARRAY,
                'The names of additional gearman jobs to bind to this callback'
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException in case there is no or an invalid feed url is given.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fqWorkerClass = $input->getArgument('fq_worker_class');
        $method = $input->getArgument('method');
        $jobName = $input->getArgument('job_name');
        $jobNames = $input->getArgument('additional_job_names');
        array_unshift($jobNames, $jobName);

        /** @var $gearman \Hautelook\GearmanBundle\Service\Gearman */
        $gearman = $this->getContainer()->get('hautelook_gearman.service.gearman');
        /** @var $worker \Hautelook\GearmanBundle\Model\GearmanWorker */
        $worker = $gearman->createWorker($jobNames, $fqWorkerClass, $method, $this->getContainer());

        $jobNamesString = implode(', ', $jobNames);
        $output->writeln("<info>Gearman worker created for: $jobNamesString</info>");

        try {
            while ($worker->work()) {
                // Nothing to do
            }
        } catch (\RuntimeException $e) {
            $output->writeln("<error>Error running job: {$worker->getErrorNumber()}: {$worker->getError()}</error>");
        }
    }
}
