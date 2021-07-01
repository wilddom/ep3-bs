<?php

namespace Calendar\View\Helper\Cell;

use DateTime;
use IntlDateFormatter;
use Zend\View\Helper\AbstractHelper;
use Weather\Service\WeatherService;

class WeatherCell extends AbstractHelper
{

    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function __invoke(DateTime $date, $type)
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

        if (!is_null($weather)) {
            $tooltip = $weather->description;
            return sprintf('<div class="weather-%s" data-tooltip="%s"><img src="%s" alt="%s"><span class="weather-temperature">%s °C</span></div>',
                $type, $tooltip, $weather->getIconUrl(), $weather->description, round($weather->temperature, 1));
        }
        return '';
    }

}