<?php

namespace Weather\Service;

class WeatherData {
    public $dt;
    public $temperature;
    public $description;
    public $icon;

    private static $iconUrl = "https://openweathermap.org/img/wn/%s.png";
    public function getIconUrl() {
        return sprintf(self::$iconUrl, $this->icon);
    }
}

?>