<?php

namespace Weather\Util;

class Sun
{
    public $rise;
    public $set;

    public function __construct(\DateTime $rise, \DateTime $set)
    {
        if ($set < $rise) {
            throw new \LogicException('Sunset cannot be before sunrise!');
        }
        $this->rise = $rise;
        $this->set = $set;
    }
}