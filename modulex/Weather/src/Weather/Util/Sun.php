<?php

namespace Weather\Util;

class Sun
{
    public $rise;
    public $set;

    public function __construct(\DateTime $rise=null, \DateTime $set=null)
    {
        if ($set < $rise) {
            throw new \LogicException('Sunset cannot be before sunrise!');
        }
        $this->rise = $rise;
        $this->set = $set;
    }

    public function isValid() {
        return !is_null($this->rise) && !is_null($this->set);
    }
}