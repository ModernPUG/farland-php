<?php
namespace FP\User;

class UserA2 extends \FP\Character\Character
{
    private function findTarget($map_tiles)
    {
        $info = $this->info();

        $xy_list = [
            [$info['x'] - 1, $info['y']],
            [$info['x'] + 1, $info['y']],
            [$info['x'], $info['y'] - 1],
            [$info['x'], $info['y'] + 1],
        ];

        foreach ($xy_list as list($x, $y)) {
            $target = $map_tiles[$y][$x];
            if (!$target) {
                continue;
            }

            if ($target['team'] != $info['team']) {
                return [
                    $x,
                    $y,
                ];
            }
        }

        $min_hp = 101;
        $target_x = -1;
        $target_y = -1;

        for ($y = 0; $y < \FP\Map::TILE_COUNT_Y; $y++) {
            for ($x = 0; $x < \FP\Map::TILE_COUNT_X; $x++) {
                $target = $map_tiles[$y][$x];
                if (is_null($target)) {
                    continue;
                }

                if ($target['team'] != $info['team']) {
                    if ($target['hp'] < $min_hp) {
                        $min_hp = $target['hp'];
                        $target_x = $x;
                        $target_y = $y;
                    }
                }
            }
        }

        if ($target_x < 0) {
            return [];
        } else {
            return [
                $target_x,
                $target_y,
            ];
        }
    }

    private function directionToTarget($map_tiles, $target_x, $target_y)
    {
        $info = $this->info();

        if ($info['x'] < $target_x) {
            return 'right';
        }

        if ($info['x'] > $target_x) {
            return 'left';
        }

        if ($info['y'] < $target_y) {
            return 'bottom';
        }

        if ($info['y'] > $target_y) {
            return 'top';
        }

        return 'left';
    }

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $info = $this->info();

        list($target_x, $target_y) = $this->findTarget($map_tiles);

        $remainder_x = $info['x'] - $target_x;
        $remainder_y = $info['y'] - $target_y;

        if ($remainder_x == 0 && abs($remainder_y) == 1) {
            if ($remainder_y == -1) {
                return new \FP\Action('attack', 'bottom');
            } else {
                return new \FP\Action('attack', 'top');
            }
        }

        if ($remainder_y == 0 && abs($remainder_x) == 1) {
            if ($remainder_x == -1) {
                return new \FP\Action('attack', 'right');
            } else {
                return new \FP\Action('attack', 'left');
            }
        }

        $move_direction = $this->directionToTarget($map_tiles, $target_x, $target_y);

        return new \FP\Action('move', $move_direction);
    }
}
