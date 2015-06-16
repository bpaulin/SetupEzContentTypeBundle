<?php

namespace Bpaulin\SetupEzContentTypeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'bpaulin_setup_ez_content_type' );

        $rootNode
            ->children()
                ->arrayNode( 'groups' )
                    ->isRequired()
                    ->prototype( 'array' ) // content type group
                        ->prototype( 'array' ) // content type
                            ->children()
                                ->booleanNode( 'virtual' )->defaultValue( false )->end()
                                ->scalarNode( 'extends' )->defaultValue( false )->end()
                                ->scalarNode( 'mainLanguageCode' )->end() // required
                                ->scalarNode( 'nameSchema' )->end()
                                ->arrayNode( 'names' ) // required
                                    ->prototype( 'scalar' )
                                    ->end()
                                ->end()
                                ->arrayNode( 'descriptions' )
                                    ->prototype( 'scalar' )
                                    ->end()
                                ->end()
                                ->arrayNode( 'fields' ) // ->isRequired()->requiresAtLeastOneElement()
                                    ->prototype( 'array' )
                                        ->children()
                                            ->scalarNode( 'type' )->end() // required
                                            ->arrayNode( 'names' )
                                                ->prototype( 'scalar' )
                                                ->end()
                                            ->end()
                                            ->arrayNode( 'descriptions' )
                                                ->prototype( 'scalar' )
                                                ->end()
                                            ->end()
                                            ->scalarNode( 'fieldGroup' )->end()
                                            ->integerNode( 'position' )->end() // required
                                            ->scalarNode( 'isTranslatable' )->end()
                                            ->scalarNode( 'isRequired' )->end()
                                            ->scalarNode( 'isSearchable' )->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
