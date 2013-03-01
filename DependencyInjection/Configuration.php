<?php

namespace Hautelook\GearmanBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hautelook_gearman');

        $rootNode
            ->children()
                ->arrayNode('servers')
                    ->info('Defines the gearman servers')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('environment')
                    ->info('Defines the Gearman environment.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
