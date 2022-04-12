<?php

namespace TotalCRM\AmoCRM\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package TotalCRM\AmoCRM\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('amo_crm');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->scalarNode('tenant_id')->end()
            ->scalarNode('client_id')->end()
            ->scalarNode('client_secret')->end()
            ->scalarNode('redirect_uri')->end()
            ->scalarNode('home_page')->end()
            ->scalarNode('prefer_time_zone')->end()
            ->scalarNode('version')->end()
            ->scalarNode('storage_manager')->end()
            ->scalarNode('stateless')->end()
            ->scalarNode('contact_folder')->end()
            ->variableNode('scopes')->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
