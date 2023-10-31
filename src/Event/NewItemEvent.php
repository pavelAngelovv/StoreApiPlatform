<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NewItemEvent extends Event
{
    private $createdAlcohol;

    public function __construct($createdAlcohol)
    {
        $this->createdAlcohol = $createdAlcohol;
    }

    public function getCreatedAlcohol()
    {
        return json_encode($this->createdAlcohol);
    }
}
