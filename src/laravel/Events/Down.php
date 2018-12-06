<?php

namespace GBradley\Updown\Laravel\Events;

use GBradley\Updown\Check;

class Down
{

    public $check;
    public $downtime;

    /**
     * Create a new event instance.
     */
    public function __construct(Check $check, array $downtime)
    {
        $this->check = $check;
        $this->downtime = $downtime;
    }
}