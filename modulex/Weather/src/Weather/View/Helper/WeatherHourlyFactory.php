<?php

namespace Weather\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WeatherHourlyFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $sm)
    {
        return new WeatherHourly($sm->getServiceLocator()->get('Weather\Service\WeatherService'));
    }

}
