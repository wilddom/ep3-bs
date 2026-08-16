<?php

namespace Weather\View\Helper;

use DateTime;
use IntlDateFormatter;
use Zend\View\Helper\AbstractHelper;
use Weather\Service\WeatherService;

class WeatherCell extends AbstractHelper
{

    protected WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function __invoke(DateTime $date, string $type)
    {
        $view = $this->getView();

        $weatherCol = $this->weatherService->get();
        $weather = null;
        if ($type == 'day') {
            $weather = $weatherCol->getDaily($date);
        }
        else if ($type == 'hour') {
            $weather = $weatherCol->getHourly($date);
        }
        else {
            return '';
        }

        $content = $view->weatherInfo($weather, 'Vorhersage');
        if ($type == 'day' && $weatherCol->hasCurrent($date)) {
            $current = $weatherCol->getCurrent($date);
            $content .= $view->weatherInfo($current, 'Aktuell');
        }
        
        return sprintf('<div class="weather-%s">%s</div>',
            $type, $content);
    }

}