<?php

namespace Weather\Util;

class Location
{
    public $lat;
    public $lon;

    public function __construct($lat = null, $lon = null)
    {
        $this->lat = isset($lat) ? (float)$lat : null;
        $this->lon = isset($lon) ? (float)$lon : null;
    }
}