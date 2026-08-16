<?php

namespace Weather\Util;

class Temperature
{
    public ?Unit $now;
    public ?Unit $min;
    public ?Unit $max;
    public ?Unit $day;
    public ?Unit $morning;
    public ?Unit $evening;
    public ?Unit $night;

    public function __toString(): string
    {
        return $this->now->__toString();
    }

    public function isComplete(): bool {
        foreach ($this as $key => $value) {
            if (!$value->isValid()) {
                return false;
            }
        }
        return true;
    }

    public function getCurrent(): ?Unit {
        $now = new \DateTime('now');
        if ($this->isComplete()) {
            $hours = (int)$now->format('H');
            if ($hours >= 6 && $hours < 12) {
                return $this->morning;
            }
            if ($hours >= 12 && $hours < 17) {
                return $this->day;
            }
            if ($hours >= 17 && $hours < 20) {
                return $this->evening;
            }
            return $this->night;
        }
        return $this->now;
    }

    public function isValid(): bool {
        return $this->now->isValid();
    }

    public function getUnit(): string
    {
        return $this->now->getUnit();
    }

    public function getValue(): ?float
    {
        return $this->now->getValue();
    }

    public function getFormatted(?int $precision = null): string
    {
        return $this->now->getFormatted($precision);
    }

    public function __construct(?Unit $now, ?Unit $min = null, ?Unit $max = null, ?Unit $day = null, ?Unit $morning = null, ?Unit $evening = null, ?Unit $night = null)
    {
        $this->now = is_null($now) ? new Unit($now) : $now;
        $this->min = is_null($min) ? new Unit($min) : $min;
        $this->max = is_null($max) ? new Unit($max) : $max;
        $this->day = is_null($day) ? new Unit($day) : $day;
        $this->morning = is_null($morning) ? new Unit($morning) : $morning;
        $this->evening = is_null($evening) ? new Unit($evening) : $evening;
        $this->night = is_null($night) ? new Unit($night) : $night;
    }
}