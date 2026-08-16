<?php

namespace Weather\Util;

class Weather
{
    public int $id;
    public string $description;
    public string $icon;

    private static string $iconUrl = "https://openweathermap.org/img/wn/%s.png";

    public function __construct(int $id, string $description, string $icon)
    {
        $this->id = (int)$id;
        $this->description = (string)$description;
        $this->icon = (string)$icon;
    }

    public function __toString(): string
    {
        return $this->description;
    }

    public function getIconUrl(): string
    {
        return sprintf(self::$iconUrl, $this->icon);
    }

    public static function setIconUrlTemplate(string $iconUrl): void
    {
        self::$iconUrl = $iconUrl;
    }
}