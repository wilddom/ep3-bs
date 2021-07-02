<?php

namespace Weather\View\Helper;

use DateTime;
use IntlDateFormatter;
use Zend\View\Helper\AbstractHelper;
use Weather\Service\WeatherData;
use Weather\Service\WeatherService;

class WeatherHourly extends AbstractHelper
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function __invoke(DateTime $dt)
    {
        $view = $this->getView();

        $weatherCol = $this->weatherService->get();

        $weather = $weatherCol->getHourly($dt);

        if (is_null($weather)) {
            return '';
        }

        $rain = '';
        if ($weather->precipitation->isValid() && $weather->pop->isValid()) {
            $rain = 'Niederschlag: '.$weather->precipitation.' ('.$weather->pop.')';
        }
        else if ($weather->precipitation->isValid()) {
            $rain = 'Niederschlag: '.$weather->precipitation;
        }
        else if ($weather->pop->isValid()) {
            $rain = 'Niederschlag: '.$weather->pop;
        }

        return sprintf('<p class="weather-info"><img src="%s" alt="%s"><span class="weather-temperature">%s</span><br/>%s<br/>%s</p>',
            $weather->weather->getIconUrl(), $weather->weather->description, $weather->temperature->getFormatted(0), $weather->weather->description, $rain);
    }

}