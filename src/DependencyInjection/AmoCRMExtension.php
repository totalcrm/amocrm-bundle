<?php

namespace TotalCRM\AmoCRM\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Exception;

/**
 * Class AmoCRMExtension
 * @package TotalCRM\AmoCRM\DependencyInjection
 */
class AmoCRMExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('amo_crm', $config);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yml');
    }
}
