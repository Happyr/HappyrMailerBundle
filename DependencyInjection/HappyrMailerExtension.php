<?php

namespace Happyr\MailerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HappyrMailerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('happyr_mailer.from.email', $config['from']['email']);
        $container->setParameter('happyr_mailer.from.name', $config['from']['name']);
        $container->setParameter('happyr_mailer.error_type', $config['error_type']);
        $container->setParameter('happyr_mailer.fake_request', $config['fake_request']);

        if (!empty($config['request_provider_service'])) {
            $def = $container->getDefinition('happyr.mailer');
            $def->replaceArgument(3, new Reference($config['request_provider_service']));
        }
    }
}
