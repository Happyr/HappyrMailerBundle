<?php

namespace Webfish\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('webfish_mailer');
        
        $rootNode
     	  ->children()
       			->scalarNode('class')->defaultValue('Webfish\MailerBundle\Util\Mailer')->cannotBeEmpty()->end()
        		->arrayNode('from')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email')->defaultValue('webmaster@example.com')->cannotBeEmpty()->end()
                        ->scalarNode('name')->defaultValue('webmaster')->cannotBeEmpty()->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
