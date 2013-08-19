<?php
namespace Hautelook\GearmanBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GearmanClearCommand
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hautelook:gearman:empty')
            ->setDescription('Clear a gearman queue')
            ->addArgument(
                'job_name',
                InputArgument::REQUIRED,
                'The name of the gearman job'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobName = $input->getArgument('job_name');

        $gearman = $this->getContainer()->get('hautelook_gearman.service.gearman');
        $worker = $gearman->createNoopWorker($jobName);

        $output->writeln("<info>Worker created</info>");
        $output->writeln("<info>Clearing queue {$jobName}...</info>");

        /** @var $progress \Symfony\Component\Console\Helper\ProgressHelper */
        $progress = $this->getHelperSet()->get('progress');
        $progress->setRedrawFrequency(10);

        $progress->setFormat(ProgressHelper::FORMAT_VERBOSE_NOMAX);
        $progress->start($output);

        while (true === $worker->work()) {
            $progress->advance();
        }

        $progress->finish();
    }
}
