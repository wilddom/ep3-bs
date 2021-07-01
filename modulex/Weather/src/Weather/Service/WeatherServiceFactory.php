<?php

namespace Weather\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WeatherServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $sm)
    {
        $weatherService = new WeatherService(
            $sm->get('Base\Manager\ConfigManager'),
            $sm->get('Base\Manager\OptionManager')
        );

        return $weatherService;
    }

}