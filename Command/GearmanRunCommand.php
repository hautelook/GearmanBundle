<?php

namespace Hautelook\GearmanBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

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
                'job_names',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'The name of one or multiple gearman jobs'
            )
            ->addOption(
                'count',
                'c',
                InputOption::VALUE_REQUIRED,
                'The count of jobs after which the worker should exit'
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
        $jobNames = $input->getArgument('job_names');

        $count = (int) $input->getOption('count');

        if ($count < 1) {
            $count = false;
        }

        /** @var $gearman \Hautelook\GearmanBundle\Service\Gearman */
        $gearman = $this->getContainer()->get('hautelook_gearman.service.gearman');
        /** @var $worker \Hautelook\GearmanBundle\Model\GearmanWorker */
        $worker = $gearman->createWorker($jobNames, $fqWorkerClass, $method, $this->getContainer());

        $jobNamesString = implode(', ', $jobNames);
        $output->writeln("<info>Gearman worker created for: {$jobNamesString}</info>");

        $jobsDone = 0;

        try {
            while (($count === false || $jobsDone < $count) && $worker->work()) {
                $jobsDone++;
            }

            $output->writeln("<info>Gearman worker finished after {$count} jobs</info>");
        } catch (\RuntimeException $e) {
            $output->writeln("<error>Error running job: {$worker->getErrorNumber()}: {$worker->getError()}</error>");
        }
    }
}
