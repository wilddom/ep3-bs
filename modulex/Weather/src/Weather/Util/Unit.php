<?php

namespace Weather\Util;

use JsonSerializable;

class Unit implements JsonSerializable
{
    private ?float $value;
    private string $unit;

    public function __construct(?float $value = 0.0, string $unit = "")
    {
        $this->value = is_null($value) ? $value : (float)$value;
        $this->unit = (string)$unit;
    }

    public function isValid(): bool {
        return !is_null($this->value);
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }

    public function getUnit(): string
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

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function getFormatted(?int $precision = null): string
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

    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->getValue(),
            'unit' => $this->getUnit(),
        ];
    }
}