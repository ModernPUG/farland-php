<?php
namespace FP\User;

class User01 extends \FP\Character\Character
{
    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        return new \FP\Action('move', 'right');
    }
}
