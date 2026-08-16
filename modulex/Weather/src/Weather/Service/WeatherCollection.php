<?php

namespace Weather\Service;

use DateTime;

class WeatherCollection {
    public ?WeatherData $current;
    public array $daily;
    public array $hourly;

    public function __construct() {
        $this->current = null;
        $this->daily = array();
        $this->hourly = array();
    }
    
    public function setCurrent(WeatherData $weather): void {
        $this->current = $weather;
    }

    public function hasCurrent(DateTime $dt): bool {
        return !is_null($this->current) && $this->current->dt->format('Y-m-d') == $dt->format('Y-m-d');
    }

    public function getCurrent(DateTime $dt): ?WeatherData {
        if ($this->hasCurrent($dt)) {
            return $this->current;
        }
        return null;
    }

    public function setDaily(WeatherData $weather): void {
        $this->daily[$weather->dt->format('Y-m-d')] = $weather;
    }

    public function hasDaily(DateTime $dt): bool {
        return array_key_exists($dt->format('Y-m-d'), $this->daily);
    }

    public function getDaily(DateTime $dt): mixed {
        if ($this->hasDaily($dt)) {
            return $this->daily[$dt->format('Y-m-d')];
        }
        return null;
    }

    public function setHourly(WeatherData $weather): void {
        $this->hourly[$weather->dt->format('Y-m-d')][$weather->dt->format('H')] = $weather;
    }

    public function hasDayHourly(DateTime $dt): bool {
        return array_key_exists($dt->format('Y-m-d'), $this->hourly);
    }

    public function hasHourly(DateTime $dt): bool {
        if (!$this->hasDayHourly($dt)) {
            return false;
        }
        return array_key_exists($dt->format('H'), $this->hourly[$dt->format('Y-m-d')]);
    }

    public function getHourly(DateTime $dt): mixed {
        if ($this->hasHourly($dt)) {
            return $this->hourly[$dt->format('Y-m-d')][$dt->format('H')];
        }
        return null;
    }
}

?>