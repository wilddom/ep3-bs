<?php

namespace Backend\Form\Booking;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EditFormFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $sm)
    {
        return new EditForm(
            $sm->getServiceLocator()->get('Booking\Service\BookingStatusService'),
            $sm->getServiceLocator()->get('Square\Manager\SquareManager'),
            $sm->getServiceLocator()->get('Base\Manager\OptionManager')
        );
    }

}
