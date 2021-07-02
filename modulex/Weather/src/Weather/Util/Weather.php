<?php

namespace Weather\Util;

class Weather
{
    public $id;
    public $description;
    public $icon;

    private static $iconUrl = "https://openweathermap.org/img/wn/%s.png";

    public function __construct($id, $description, $icon)
    {
        $this->id = (int)$id;
        $this->description = (string)$description;
        $this->icon = (string)$icon;
    }

    public function __toString()
    {
        return $this->description;
    }

    public function getIconUrl()
    {
        return sprintf(self::$iconUrl, $this->icon);
    }

    public static function setIconUrlTemplate($iconUrl)
    {
        self::$iconUrl = $iconUrl;
    }
}