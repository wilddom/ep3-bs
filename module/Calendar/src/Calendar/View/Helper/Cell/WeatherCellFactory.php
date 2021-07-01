<?php

namespace Calendar\View\Helper\Cell;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WeatherCellFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $sm)
    {
        return new WeatherCell($sm->getServiceLocator()->get('Weather\Service\WeatherService'));
    }

}
