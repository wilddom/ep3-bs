<?php

namespace Weather\View\Helper;

use DateTime;
use IntlDateFormatter;
use Zend\View\Helper\AbstractHelper;
use Weather\Service\WeatherData;

class WeatherInfo extends AbstractHelper
{
    public function __invoke(WeatherData $weather=null, $description=null)
    {
        $view = $this->getView();

        if (is_null($weather)) {
            return '<div class="weather-info"></div>';
        }

        $tooltip = '<div class="weather-tooltip">';
        if (!is_null($description)) {
            $tooltip .= '<p><b>'.$description.'</b></p>';
        }
        $tooltip .= '<p>';
        $tooltip .= 'Wetter: '.$weather->weather->description;
        if ($weather->wind->isValid()) {
            $tooltip .= '<br>Wind: '.$weather->wind;
        }
        if ($weather->clouds->isValid()) {
            $tooltip .= '<br>Bedeckt: '.$weather->clouds;
        }
        if ($weather->precipitation->isValid() && $weather->pop->isValid()) {
            $tooltip .= '<br>Niederschlag: '.$weather->precipitation.' ('.$weather->pop.')';
        }
        else if ($weather->precipitation->isValid()) {
            $tooltip .= '<br>Niederschlag: '.$weather->precipitation;
        }
        else if ($weather->pop->isValid()) {
            $tooltip .= '<br>Niederschlag: '.$weather->pop;
        }
        if ($weather->humidity->isValid()) {
            $tooltip .= '<br>Luftfeuchtigkeit: '.$weather->humidity;
        }
        if ($weather->pressure->isValid()) {
            $tooltip .= '<br>Druck: '.$weather->pressure;
        }
        if ($weather->dew_point->isValid()) {
            $tooltip .= '<br>Taupunkt: '.$weather->dew_point->getFormatted(1);
        }
        if ($weather->uvi->isValid()) {
            $tooltip .= '<br>UV: '.$weather->uvi;
        }
        if ($weather->visibility->isValid()) {
            $tooltip .= '<br>Sicht: '.$weather->visibility;
        }
        $tooltip .= '</p>';
        if ($weather->temperature->isComplete()) {
            $tooltip .= '<table>';
            $tooltip .= '<tr><td></td><td>Temperatur</td><td>Gefühlt</td></tr>';
            $tooltip .= '<tr><td>Morgen</td><td>'.$weather->temperature->morning->getFormatted(0).'</td><td>'.$weather->feels_like->morning->getFormatted(0).'</td></tr>';
            $tooltip .= '<tr><td>Nachmittag</td><td>'.$weather->temperature->day->getFormatted(0).'</td><td>'.$weather->feels_like->day->getFormatted(0).'</td></tr>';
            $tooltip .= '<tr><td>Abend</td><td>'.$weather->temperature->evening->getFormatted(0).'</td><td>'.$weather->feels_like->evening->getFormatted(0).'</td></tr>';
            $tooltip .= '<tr><td>Nacht</td><td>'.$weather->temperature->night->getFormatted(0).'</td><td>'.$weather->feels_like->night->getFormatted(0).'</td></tr>';
            $tooltip .= '</table>';
        }
        if ($weather->sun->isValid()) {
            $tooltip .= '<table>';
            $tooltip .= '<tr><td>Sonnenaufgang</td><td>'.$weather->sun->rise->format('H:i').'</td></tr>';
            $tooltip .= '<tr><td>Sonnenuntergang</td><td>'.$weather->sun->set->format('H:i').'</td></tr>';
            $tooltip .= '</table>';
        }
        $tooltip .= '</div>';

        $now = new \DateTime('now');
        $temp = $weather->temperature;
        if ($weather->dt->format('Y-m-d') == $now->format('Y-m-d')) {
            $temp = $weather->temperature->getCurrent();
        }
        return sprintf('<div class="weather-info" data-tooltip="%s"><img src="%s" alt="%s"><span class="weather-temperature">%s</span></div>',
            htmlentities($tooltip), $weather->weather->getIconUrl(), $weather->weather->description, $temp->getFormatted(0));
    }

}