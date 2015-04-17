<?php

namespace Happyr\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
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
                ->arrayNode('from')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email')->defaultValue('webmaster@example.com')->cannotBeEmpty()->end()
                        ->scalarNode('name')->defaultValue('webmaster')->cannotBeEmpty()->end()
                    ->end()
            ->end()
            ->booleanNode('fake_request')->defaultFalse()->cannotBeEmpty()->end()
            ->scalarNode('request_provider_service')->end()
            ->scalarNode('error_type')->defaultValue('exception')->validate()
                ->ifNotInArray(array('exception', 'error', 'warning', 'notice', 'none'))
                ->thenInvalid(
                    'Invalid error type "%s", must be "exception", "error", "warning", "notice" or "none".'
                )->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
