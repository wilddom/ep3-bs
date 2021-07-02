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
        else {
            return '';
        }

        if (is_null($weather)) {
            return sprintf('<div class="weather-%s"></div>', $type);
        }

        if ($type == 'day') {
            $tooltip = '';
            $tooltip = '<p class="weather-info">';
            $tooltip .= 'Wetter: '.$weather->weather->description;
            $tooltip .= '<br>Wind: '.$weather->wind;
            $tooltip .= '<br>Bedeckt: '.$weather->clouds;
            $tooltip .= '<br>Niederschlag: '.$weather->precipitation.' ('.$weather->pop.')';
            $tooltip .= '<br>Luftfeuchtigkeit: '.$weather->humidity;
            $tooltip .= '<br>Druck: '.$weather->pressure;
            $tooltip .= '<br>Taupunkt: '.$weather->dew_point->getFormatted(1);
            $tooltip .= '<br>UV: '.$weather->uvi;
            $tooltip .= '</p>';
            $tooltip .= '<table class="weather-info">';
            $tooltip .= '<tr><td></td><td>Temperatur</td><td>Gefühlt</td></tr>';
            $tooltip .= '<tr><td>Morgen</td><td>'.$weather->temperature->morning->getFormatted(0).'</td><td>'.$weather->feels_like->morning->getFormatted(0).'</td></tr>';
            $tooltip .= '<tr><td>Nachmittag</td><td>'.$weather->temperature->day->getFormatted(0).'</td><td>'.$weather->feels_like->day->getFormatted(0).'</td></tr>';
            $tooltip .= '<tr><td>Abend</td><td>'.$weather->temperature->evening->getFormatted(0).'</td><td>'.$weather->feels_like->evening->getFormatted(0).'</td></tr>';
            $tooltip .= '<tr><td>Nacht</td><td>'.$weather->temperature->night->getFormatted(0).'</td><td>'.$weather->feels_like->night->getFormatted(0).'</td></tr>';
            $tooltip .= '</table>';
            $tooltip .= '<table class="weather-info">';
            $tooltip .= '<tr><td>Sonnenaufgang</td><td>'.$weather->sun->rise->format('H:i').'</td></tr>';
            $tooltip .= '<tr><td>Sonnenuntergang</td><td>'.$weather->sun->set->format('H:i').'</td></tr>';
            $tooltip .= '</table>';

            return sprintf('<div class="weather-%s" data-tooltip="%s"><img src="%s" alt="%s"><span class="weather-temperature">%s</span></div>',
                $type, htmlentities($tooltip), $weather->weather->getIconUrl(), $weather->weather->description, $weather->temperature->getFormatted(0));
        }
        else if ($type == 'hour') {
            return sprintf('<div class="weather-%s"><img src="%s" alt="%s"><span class="weather-temperature">%s</span></div>',
                $type, $weather->weather->getIconUrl(), $weather->weather->description, $weather->temperature->getFormatted(0));
        }
        return '';
    }

}