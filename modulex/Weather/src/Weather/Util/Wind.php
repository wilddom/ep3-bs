<?php

namespace Weather\Util;

class Wind
{
    public $speed;
    public $direction;

    public function __construct(Unit $speed, Unit $direction = null)
    {
        $this->speed = $speed;
        $this->direction = $direction;
    }

    public function __toString()
    {
        return $this->getFormatted();
    }

    public function isValid()
    {
        return $this->speed->isValid();
    }

    public function getFormatted()
    {
        if (!$this->speed->isValid()) {
            return '';
        }
        if (!$this->direction->isValid()) {
            return $this->speed->getFormatted();
        }
        return $this->speed->getFormatted().' '.$this->getDirectionDescription();
    }

    public function getDirectionDescription() {
        $directions = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N');
	    return $directions[round($this->direction->getValue() / 22.5)];
    }
}