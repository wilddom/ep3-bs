<?php

namespace Weather\Util;

use JsonSerializable;

class Unit implements JsonSerializable
{
    private $value;
    private $unit;

    public function __construct($value = 0.0, $unit = "")
    {
        $this->value = is_null($value) ? $value : (float)$value;
        $this->unit = (string)$unit;
    }

    public function isValid() {
        return !is_null($this->value);
    }

    public function __toString()
    {
        return $this->getFormatted();
    }

    public function getUnit()
    {
        // Units are inconsistent. Only celsius and fahrenheit are not abbreviated. This check fixes that.
        // Also, the API started to return "metric" as temperature unit recently. Also fix that.
        if ($this->unit == 'celsius' || $this->unit == 'metric') {
            return "°C";
        } elseif ($this->unit == 'fahrenheit') {
            return '°F';
        } else {
            return $this->unit;
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFormatted($precision = null)
    {
        if(!$this->isValid()) {
            return '';
        }
        $value = $this->getValue();
        if (!is_null($precision)) {
            $value = round($value, $precision);
        }
        if ($this->getUnit() != "") {
            return $value . " " . $this->getUnit();
        } else {
            return (string)$value;
        }
    }

    public function jsonSerialize()
    {
        return [
            'value' => $this->getValue(),
            'unit' => $this->getUnit(),
        ];
    }
}