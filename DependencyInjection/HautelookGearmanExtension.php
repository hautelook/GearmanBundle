<?php

namespace Hautelook\GearmanBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class HautelookGearmanExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // Add all gearman servers
        $gearmanClient = $container->getDefinition('hautelook_gearman.service.gearman_client');
        foreach ($config['servers'] as $serverName => $server) {
            $gearmanClient->addMethodCall('addServer', array($server['host'], $server['port']));
        }
        if ($config['environment']) {
            $container
                ->getDefinition('hautelook_gearman.service.gearman')
                ->addMethodCall('setEnvironment', array($config['environment']));
        }
    }
}
