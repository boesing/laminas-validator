<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

class Module
{
    /**
     * Return default laminas-validator configuration for laminas-mvc applications.
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register a specification for the ValidatorManager with the ServiceListener.
     *
     * @param \Laminas\ModuleManager\ModuleEvent
     * @return void
     */
    public function init($event)
    {
        $container = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'ValidatorManager',
            'validators',
            'Laminas\ModuleManager\Feature\ValidatorProviderInterface',
            'getValidatorConfig'
        );
    }
}
