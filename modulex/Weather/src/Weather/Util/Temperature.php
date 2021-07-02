<?php

namespace Weather\Util;

class Temperature
{
    public $now;
    public $min;
    public $max;
    public $day;
    public $morning;
    public $evening;
    public $night;

    public function __toString()
    {
        return $this->now->__toString();
    }

    public function getUnit()
    {
        return $this->now->getUnit();
    }

    public function getValue()
    {
        return $this->now->getValue();
    }

    public function getDescription()
    {
        return $this->now->getDescription();
    }

    public function getFormatted($precision = null)
    {
        return $this->now->getFormatted($precision);
    }

    public function __construct(Unit $now, Unit $min = null, Unit $max = null, Unit $day = null, Unit $morning = null, Unit $evening = null, Unit $night = null)
    {
        $this->now = $now;
        $this->min = $min;
        $this->max = $max;
        $this->day = $day;
        $this->morning = $morning;
        $this->evening = $evening;
        $this->night = $night;
    }
}