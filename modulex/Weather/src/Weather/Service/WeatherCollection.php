<?php

namespace Weather\Service;

use DateTime;

class WeatherCollection {
    public $current;
    public $daily;
    public $hourly;

    public function __construct() {
        $this->current = null;
        $this->daily = array();
        $this->hourly = array();
    }
    
    public function setCurrent(WeatherData $weather) {
        $this->current = $weather;
    }

    public function hasCurrent(DateTime $dt) {
        return !is_null($this->current) && $this->current->dt->format('Y-m-d') == $dt->format('Y-m-d');
    }

    public function getCurrent(DateTime $dt) {
        if ($this->hasCurrent($dt)) {
            return $this->current;
        }
        return null;
    }

    public function setDaily(WeatherData $weather) {
        $this->daily[$weather->dt->format('Y-m-d')] = $weather;
    }

    public function hasDaily(DateTime $dt) {
        return array_key_exists($dt->format('Y-m-d'), $this->daily);
    }

    public function getDaily(DateTime $dt) {
        if ($this->hasDaily($dt)) {
            return $this->daily[$dt->format('Y-m-d')];
        }
        return null;
    }

    public function setHourly(WeatherData $weather) {
        $this->hourly[$weather->dt->format('Y-m-d')][$weather->dt->format('H')] = $weather;
    }

    public function hasDayHourly(DateTime $dt) {
        return array_key_exists($dt->format('Y-m-d'), $this->hourly);
    }

    public function hasHourly(DateTime $dt) {
        if (!$this->hasDayHourly($dt)) {
            return false;
        }
        return array_key_exists($dt->format('H'), $this->hourly[$dt->format('Y-m-d')]);
    }

    public function getHourly(DateTime $dt) {
        if ($this->hasHourly($dt)) {
            return $this->hourly[$dt->format('Y-m-d')][$dt->format('H')];
        }
        return null;
    }
}

?>