<?php

namespace Weather\Util;

class City extends Location
{
    public int $id;
    public ?string $name;
    public ?string $country;
    public ?int $population;
    public ?\DateTimeZone $timezone;

    public function __construct(int $id, ?string $name = null, ?float $lat = null, ?float $lon = null, ?string $country = null, ?int $population = null, ?int $timezoneOffset = null)
    {
        $this->id = (int)$id;
        $this->name = isset($name) ? (string)$name : null;
        $this->country = isset($country) ? (string)$country : null;
        $this->population = isset($population) ? (int)$population : null;
        $this->timezone = isset($timezoneOffset) ? new \DateTimeZone(self::timezoneOffsetInSecondsToHours((int)$timezoneOffset)) : null;

        parent::__construct($lat, $lon);
    }

    private static function timezoneOffsetInSecondsToHours($offset)
    {
        $minutes = floor(abs($offset) / 60) % 60;
        $hours = floor(abs($offset) / 3600);

        $result = $offset < 0 ? "-" : "+";
        $result .= str_pad($hours, 2, "0", STR_PAD_LEFT);
        $result .= str_pad($minutes, 2, "0", STR_PAD_LEFT);

        return $result;
    }
}