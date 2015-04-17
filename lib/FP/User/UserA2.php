<?php
namespace FP\User;

class UserA2 extends \FP\Character\Character
{
    private $turn_count = 0;
    private $mode = '';

    private function scanMap($map_tiles)
    {
        for ($y = 0; $y < \FP\Map::TILE_COUNT_Y; $y++) {
            for ($x = 0; $x < \FP\Map::TILE_COUNT_X; $x++) {
                yield [
                    $x,
                    $y,
                    $map_tiles[$y][$x],
                ];
            }
        }
    }

    private function nearEnemyCount($map_tiles)
    {
        $info = $this->info();

        $count = 0;

        $xy_list = [
            [$info['x'] - 1, $info['y']],
            [$info['x'] + 1, $info['y']],
            [$info['x'], $info['y'] - 1],
            [$info['x'], $info['y'] + 1],
        ];

        foreach ($xy_list as list($x, $y)) {
            $target = $map_tiles[$y][$x];

            if ($target) {
                ++$count;
            }
        }

        return $count;
    }

    private function findTeam($map_tiles)
    {
        $info = $this->info();

        foreach ($this->scanMap($map_tiles) as list($x, $y, $target)) {
            if (is_null($target)) {
                continue;
            } elseif ($target['id'] == $info['id']) { // 본인
                continue;
            } elseif ($target['team'] != $info['team']) { // 다른팀
                continue;
            }

            return [
                $x,
                $y,
            ];
        }
    }

    private function findEnemy($map_tiles)
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

        foreach ($this->scanMap($map_tiles) as list($x, $y, $target)) {
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
        $me_x = $info['x'];
        $me_y = $info['y'];

        if ($info['x'] < $target_x) {
            if (is_null($map_tiles[$me_y][$me_x + 1])) {
                return 'right';
            }
        }

        if ($info['x'] > $target_x) {
            if (is_null($map_tiles[$me_y][$me_x - 1])) {
                return 'left';
            }
        }

        if ($info['y'] < $target_y) {
            if (is_null($map_tiles[$me_y+1][$me_x])) {
                return 'bottom';
            }
        }

        if ($info['y'] > $target_y) {
            if (is_null($map_tiles[$me_y-1][$me_x])) {
                return 'top';
            }
        }

        return '';
    }

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $info = $this->info();

        ++$this->turn_count;
        if ($this->turn_count % 3 == 1) {
            $this->mode = '';

            $enemy_count = $this->nearEnemyCount($map_tiles);
            if ($enemy_count >= 2) {
                $this->mode = 'danger';
            }
        }

        // 위험하면
        if ($this->mode == 'danger') {
            // 팀에게 도망
            $target = $this->findTeam($map_tiles);
            if ($target) {
                list($target_x, $target_y) = $target;
                $move_direction = $this->directionToTarget($map_tiles, $target_x, $target_y);

                if ($move_direction) {
                    return new \FP\Action('move', $move_direction);
                }
            }
        }

        // 적을 찾기
        list($target_x, $target_y) = $this->findEnemy($map_tiles);

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
