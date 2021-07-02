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
        if ($data->temp instanceof \stdClass) {
            if (!isset($data->temp->max)) {
                $max = null;
                foreach ($data->temp as $key => $val) {
                    if (is_null($max)) {
                        $max = $val;
                    }
                    else {
                        $max = max($max, $val);
                    }
                }
                if (!is_null($max)) {
                    $data->temp->max = $max;
                }
            }
            if (!isset($data->temp->min)) {
                $min = null;
                foreach ($data->temp as $key => $val) {
                    if (is_null($min)) {
                        $min = $val;
                    }
                    else {
                        $min = min($min, $val);
                    }
                }
                if (!is_null($min)) {
                    $data->temp->min = $min;
                }
            }
            $value = round((floatval($data->temp->max) + floatval($data->temp->min)) / 2, 2);
            $this->temperature = new Temperature(
                new Unit($value, $units),
                new Unit($data->temp->min, $units),
                new Unit($data->temp->max, $units),
                new Unit($data->temp->day, $units),
                new Unit($data->temp->morn, $units),
                new Unit($data->temp->eve, $units),
                new Unit($data->temp->night, $units)
            );
        }
        else {
            $this->temperature = new Temperature(new Unit($data->temp, $units));
        }
        if ($data->feels_like instanceof \stdClass) {
            if (!isset($data->feels_like->max)) {
                $max = null;
                foreach ($data->feels_like as $key => $val) {
                    if (is_null($max)) {
                        $max = $val;
                    }
                    else {
                        $max = max($max, $val);
                    }
                }
                if (!is_null($max)) {
                    $data->feels_like->max = $max;
                }
            }
            if (!isset($data->feels_like->min)) {
                $min = null;
                foreach ($data->feels_like as $key => $val) {
                    if (is_null($min)) {
                        $min = $val;
                    }
                    else {
                        $min = min($min, $val);
                    }
                }
                if (!is_null($min)) {
                    $data->feels_like->min = $min;
                }
            }
            $value = round((floatval($data->feels_like->max) + floatval($data->feels_like->min)) / 2, 2);
            $this->feels_like = new Temperature(
                new Unit($value, $units),
                new Unit($data->feels_like->min, $units),
                new Unit($data->feels_like->max, $units),
                new Unit($data->feels_like->day, $units),
                new Unit($data->feels_like->morn, $units),
                new Unit($data->feels_like->eve, $units),
                new Unit($data->feels_like->night, $units)
            );
        }
        else {
            $this->feels_like = new Temperature(new Unit($data->feels_like, $units));
        }
        $this->dew_point = new Unit($data->dew_point, $units);
        $this->humidity = new Unit($data->humidity, '%');
        $this->pressure = new Unit($data->pressure, 'hPa');
        $this->wind = new Wind(
            new Unit($data->wind_speed, $windSpeedUnit),
            new Unit($data->wind_deg, '°')
        );
        $this->clouds = new Unit($data->clouds, '%');

        // the rain field is not always present in the JSON response
        // and sometimes it contains the field '1h', sometimes the field '3h'
        $rain = isset($data->rain) ? (array) $data->rain : array();
        $rainUnit = !empty($rain) ? key($rain) : '';
        $rainValue = !empty($rain) ? current($rain) : 0.0;
        $this->precipitation = new Unit($rainValue, empty($rainUnit) ? 'mm' : 'mm/'.$rainUnit);
        $this->pop = new Unit(isset($data->pop) ? $data->pop*100 : null, '%');
        $this->uvi = $data->uvi;

        if (isset($data->sunrise) && isset($data->sunset)) {
            $sunrise = \DateTime::createFromFormat('U', $data->sunrise);
            $sunrise->setTimeZone($tz);
            $sunset = \DateTime::createFromFormat('U', $data->sunset);
            $sunset->setTimeZone($tz);
            $this->sun = new Sun($sunrise, $sunset);
        }
        $this->weather = new Weather($data->weather[0]->id, str_replace('ß', 'ss', $data->weather[0]->description), $data->weather[0]->icon);
    }
}