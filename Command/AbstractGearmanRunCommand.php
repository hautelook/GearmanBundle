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
abstract class AbstractGearmanRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->addArgument(
                'job_names',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'The name of one or multiple gearman jobs'
            )
            ->addOption(
                'max-jobs',
                'm',
                InputOption::VALUE_REQUIRED,
                'The maximum number of jobs to be run by a worker after which the worker should exit'
            )
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException in case there is no or an invalid feed url is given.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobNames = $input->getArgument('job_names');
        $maxJobs = (int) $input->getOption('max-jobs');

        if ($maxJobs < 1) {
            $maxJobs = false;
        }

        /** @var $gearman \Hautelook\GearmanBundle\Service\Gearman */
        $gearman = $this->getContainer()->get('hautelook_gearman.service.gearman');
        /** @var $worker \Hautelook\GearmanBundle\Model\GearmanWorker */
        $worker = $gearman->createWorker($jobNames, $this->getCallback($input, $output));

        $jobNamesString = implode(', ', $jobNames);
        $output->writeln("<info>Gearman worker created for: {$jobNamesString}</info>");

        $jobsDone = 0;

        try {
            while (($maxJobs === false || $jobsDone < $maxJobs) && $worker->work()) {
                $jobsDone++;
            }

            if ($maxJobs) {
                $output->writeln("<info>Gearman worker finished after {$maxJobs} jobs</info>");
            }
        } catch (\RuntimeException $e) {
            $output->writeln("<error>Error running job: {$worker->getErrorNumber()}: {$worker->getError()}</error>");
        }
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return callable
     */
    abstract protected function getCallback(InputInterface $input, OutputInterface $output);
}
