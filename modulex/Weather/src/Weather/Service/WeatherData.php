<?php

namespace Weather\Service;

use Weather\Util\Location;
use Weather\Util\Sun;
use Weather\Util\Temperature;
use Weather\Util\Unit;
use Weather\Util\Weather;
use Weather\Util\Wind;

class WeatherData
{
    public $dt;
    public $location;
    public $temperature;
    public $feels_like;
    public $humidity;
    public $pressure;
    public $wind;
    public $clouds;
    public $precipitation;
    public $pop;
    public $sun;
    public $weather;
    public $visibility;

    public function __construct($data, $units)
    {
        // This is kind of a hack, because the units are missing in the document.
        if ($units == 'metric') {
            $windSpeedUnit = 'm/s';
        } else {
            $windSpeedUnit = 'mph';
        }

        $tz = $timezone = new \DateTimeZone($data->main->timezone);
        $this->dt = \DateTime::createFromFormat('U', $data->dt);
        $this->dt->setTimeZone($tz);

        $this->location = new Location($data->main->lat, $data->main->lon);
        $this->temperature = $this->prepareTemperature($data->temp, $units);
        $this->feels_like = $this->prepareTemperature($data->feels_like, $units);
        $this->dew_point = new Unit(isset($data->dew_point) ? $data->dew_point : null, $units);
        $this->humidity = new Unit(isset($data->humidity) ? $data->humidity : null, '%');
        $this->pressure = new Unit(isset($data->pressure) ? $data->pressure : null, 'hPa');
        $this->wind = new Wind(
            new Unit(isset($data->wind_speed) ? $data->wind_speed : null, $windSpeedUnit),
            new Unit(isset($data->wind_deg) ? $data->wind_deg : null, '°')
        );
        $this->clouds = new Unit(isset($data->clouds) ? $data->clouds : null, '%');
        $this->visibility = new Unit(isset($data->visibility) ? ((float)$data->visibility/1000) : null, 'km');

        // the rain field is not always present in the JSON response
        // and sometimes it contains the field '1h', sometimes the field '3h'
        $rain = isset($data->rain) ? (array) $data->rain : array();
        $rainUnit = !empty($rain) ? key($rain) : '';
        $rainValue = !empty($rain) ? current($rain) : 0.0;
        $this->precipitation = new Unit($rainValue, empty($rainUnit) ? 'mm' : 'mm/'.$rainUnit);
        $this->pop = new Unit(isset($data->pop) ? $data->pop*100 : null, '%');
        $this->uvi = new Unit(isset($data->uvi) ? $data->uvi : null);

        if (isset($data->sunrise) && isset($data->sunset)) {
            $sunrise = \DateTime::createFromFormat('U', $data->sunrise);
            $sunrise->setTimeZone($tz);
            $sunset = \DateTime::createFromFormat('U', $data->sunset);
            $sunset->setTimeZone($tz);
            $this->sun = new Sun($sunrise, $sunset);
        }
        else {
            $this->sun = new Sun();
        }
        $this->weather = new Weather($data->weather[0]->id, str_replace('ß', 'ss', $data->weather[0]->description), $data->weather[0]->icon);
    }

    private function prepareTemperature($data, $units) {
        if (!($data instanceof \stdClass)) {
            return new Temperature(new Unit($data, $units));
        }
        if (!isset($data->max)) {
            $max = null;
            foreach ($data as $key => $val) {
                if (is_null($max)) {
                    $max = $val;
                }
                else {
                    $max = max($max, $val);
                }
            }
            if (!is_null($max)) {
                $data->max = $max;
            }
        }
        if (!isset($data->min)) {
            $min = null;
            foreach ($data as $key => $val) {
                if (is_null($min)) {
                    $min = $val;
                }
                else {
                    $min = min($min, $val);
                }
            }
            if (!is_null($min)) {
                $data->min = $min;
            }
        }
        $values = array();
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $values[] = $val;
            }
        }
        $now = array_sum($values)/count($values);
        return new Temperature(
            new Unit($now, $units),
            new Unit($data->min, $units),
            new Unit($data->max, $units),
            new Unit($data->day, $units),
            new Unit($data->morn, $units),
            new Unit($data->eve, $units),
            new Unit($data->night, $units)
        );
    }
}