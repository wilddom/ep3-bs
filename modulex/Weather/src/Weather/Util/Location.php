<?php

namespace Weather\Util;

class Location
{
    public ?float $lat;
    public ?float $lon;

    public function __construct(?float $lat = null, ?float $lon = null)
    {
        $this->lat = isset($lat) ? (float)$lat : null;
        $this->lon = isset($lon) ? (float)$lon : null;
    }
}