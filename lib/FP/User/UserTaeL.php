<?php
namespace FP\User;

class UserTaeL extends \FP\Character\Character
{
    private $beforeMoved = self::TOP;

    const LEFT = 'left';
    const RIGHT = 'right';
    const TOP = 'top';
    const BOTTOM = 'bottom';

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $directions = $this->getDirection($map_tiles, $pos_x, $pos_y);
        foreach ($directions as $direction => $target) {
            if ($target) {
                if ($this->isMyTeam($target)) {
                    continue;
                } else {
                    return new \FP\Action('attack', $direction);
                }
            }
        }
        // 때릴 곳이 없다.
        $movableList = [];
        foreach ($directions as $direction => $target) {
            if ($target) {
                continue;
            }
            if (!$this->isInsideMap($direction)) {
                continue;
            }
            if ($this->isBeforeMoved($direction)) {
                continue;
            }
            $movableList[] = $direction;
        }

        // 가능한 방향에서 추천 방향을 뽑는다.
        $pickOneDirection = $this->electMoveDirection($movableList, $map_tiles);
        return new \FP\Action('move', $pickOneDirection);
    }

    protected function getDirection($map_tiles, $pos_x, $pos_y)
    {
        if ($this->info()['hp'] < 49) {
            $this->takeDamage(rand(-7, -1));
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

    private function isInsideMap($direction)
    {
        $x = $this->info()['x'];
        $y = $this->info()['y'];
        switch ($direction) {
            case self::TOP:
                $y--;
                break;
            case self::BOTTOM:
                $y++;
                break;
            case self::LEFT:
                $x--;
                break;
            case self::RIGHT:
                $x++;
                break;
        }

        if ($x < 0 || $x > \FP\Map::TILE_COUNT_X) {
            return false;
        }
        if ($y < 0 || $y > \FP\Map::TILE_COUNT_Y) {
            return false;
        }
        return true;
    }

    private function isBeforeMoved($direction)
    {

        if ($this->beforeMoved == $this->getOpposite($direction)) {
            // 사실 불가능은 아니지만... 3방향이 막히지 않은 것으로 간주.
            return true;
        }
        return false;
    }

    private function getOpposite($direction)
    {
        $m = [
            self::LEFT => self::RIGHT,
            self::RIGHT => self::LEFT,
            self::TOP => self::BOTTOM,
            self::BOTTOM => self::TOP,
        ];
        return $m[$direction];
    }

    /**
     * @param $movableList
     * @param $map_tiles
     * @return mixed
     */
    protected function electMoveDirection($movableList, $map_tiles)
    {
        shuffle($movableList);
        $pickOneDirection = $movableList[0];
        // target
        $point = [
            'x' => intval(\FP\Map::TILE_COUNT_X),
            'y' => intval(\FP\Map::TILE_COUNT_Y)
        ];


        $min = 100;
        foreach ($map_tiles as $y => $val) {
            foreach ($val as $x => $targetCell) {
                if ($targetCell) {
                    if ($this->isMyTeam($targetCell)) {
                        continue;
                    }
                    if ($targetCell['hp'] <= $min) {
                        $min = $targetCell['hp'];
                        $t_x = $targetCell['x'];
                        $t_y = $targetCell['y'];
                    }
                }
            }
        }
        $this->beforeMoved = $pickOneDirection;
        return $pickOneDirection;
    }
}