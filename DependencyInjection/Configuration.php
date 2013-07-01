<?php

namespace HappyR\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('happyr_mailer');

        $rootNode
          ->children()
            ->scalarNode('class')->defaultValue('HappyR\MailerBundle\Services\MailerService')->cannotBeEmpty()->end()
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
