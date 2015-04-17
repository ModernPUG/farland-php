<?php
namespace FP;

class Action
{
    public $type;
    public $direction;

    /**
     * @param string $type {move|attack}
     * @param string $direction {left|right|top|bottom}
     */
    public function __construct($type, $direction)
    {
        $this->type = $type;
        $this->direction = $direction;
    }
}
