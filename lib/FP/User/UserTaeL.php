<?php
namespace FP\User;

class UserTaeL extends \FP\Character\Character
{
    private $x_limit = \FP\Map::TILE_COUNT_X;
    private $y_limit = \FP\Map::TILE_COUNT_Y;

    const LEFT = 'left';
    const RIGHT = 'right';
    const TOP = 'top';
    const BOTTOM = 'bottom';

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $directions = $this->getDirection($map_tiles, $pos_x, $pos_y);
//        shuffle($directions);
        foreach ($directions as $direction => $target) {
            if ($target) {
                if ($this->isMyTeam($target)) {
//                    echo 'MY TEAM' . '<br>';
                    continue;
                } else {
//                    var_dump($direction);
//                    echo 'ATTACK ' . $direction . '<br>';
                    return new \FP\Action('attack', $direction);
                }
            } else {
//                echo 'NO-ONE' . '<br>';
            }
        }
//        var_dump($this->map);
//        $position = $this->map->positionOfCharacter($this);
//        list($pos_x, $pos_y) = $this->map->positionOfCharacter($this);
//        $this->findMoveableXY($map_tiles, $pos_x, $pos_y);
//        left|right|top|bottom
//        return new \FP\Action('attack', self::LEFT);
    }

    protected function getDirection($map_tiles, $pos_x, $pos_y)
    {
        if ($this->info()['hp'] < 100) {
            $this->takeDamage(-1);
        }
        $left = @$map_tiles[$pos_y][$pos_x - 1];
        $right = @$map_tiles[$pos_y][$pos_x + 1];
        $top = @$map_tiles[$pos_y - 1][$pos_x];
        $bottom = @$map_tiles[$pos_y + 1][$pos_x];

        return [self::LEFT => $left, self::RIGHT => $right, self::TOP => $top, self::BOTTOM => $bottom,];
    }

    /**
     * @param $target
     * @return bool
     */
    protected function isMyTeam($target)
    {
        return $this->info()['team'] == $target['team'];
    }
}
