<?php

namespace GBradley\Updown\Laravel\Events;

use GBradley\Updown\Check;

abstract class Event
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