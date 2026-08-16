<?php

namespace Weather\Util;

class Wind
{
    public Unit $speed;
    public ?Unit $direction;

    public function __construct(Unit $speed, ?Unit $direction = null)
    {
        $this->speed = $speed;
        $this->direction = $direction;
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }

    public function isValid(): bool
    {
        return $this->speed->isValid();
    }

    public function getFormatted(): string
    {
        if (!$this->speed->isValid()) {
            return '';
        }
        if (!$this->direction->isValid()) {
            return $this->speed->getFormatted();
        }
        return $this->speed->getFormatted().' '.$this->getDirectionDescription();
    }

    public function getDirectionDescription(): string
    {
        $directions = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N');
	    return $directions[(int)round($this->direction->getValue() / 22.5)];
    }
}