<?php

namespace Hautelook\GearmanBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class GearmanClientCommand
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class GearmanClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hautelook:gearman:status')
            ->setDescription('Get Gearman Manager Status information');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException in case there is no or an invalid feed url is given.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gearman = $this->getContainer()->get('hautelook_gearman.service.telnet_client');
        $status = $gearman->status();

        foreach ($status as $server => $queues) {
            $output->writeln("<info>Status for Server {$server}</info>");
            $output->writeln("");
            foreach ($queues as $queue) {
                $str  = "<comment>{$queue['name']}</comment> Jobs: {$queue['queue']}";
                $str .= " Workers: {$queue['running']} / {$queue['workers']}";
                $output->writeln($str);
            }
        }
    }
}
