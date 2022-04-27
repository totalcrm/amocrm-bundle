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
                ->scalarNode('client_id')->end()
                ->scalarNode('client_secret')->end()
                ->scalarNode('redirect_uri')->end()
                ->scalarNode('base_domain')->end()
                ->scalarNode('homepage_route')->end()
                ->scalarNode('subscription_uri')->end()
                ->scalarNode('field_emails')->end()
                ->scalarNode('field_phones')->end()
                ->arrayNode('field_names')
                    ->arrayPrototype()
                        ->children()
                            ->integerNode('id')->end()
                            ->scalarNode('name')->end()
                            ->scalarNode('field_name')->end()
                            ->scalarNode('type')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
