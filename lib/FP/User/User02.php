<?php
namespace FP\User;

class User02 extends \FP\Character\Character
{
    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        return new \FP\Action('attack', 'left');
    }
}
