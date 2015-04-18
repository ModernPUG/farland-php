<?php
namespace FP\User;

class UserA2 extends \FP\Character\Character
{
    private $turn_count = 0;
    private $danger_count = 0;

    private function vaildPosition($pos_x, $pos_y)
    {
        // 맵을 벗어난 위치
        return !(
            $pos_x < 0
            || $pos_x >= \FP\Map::TILE_COUNT_X
            || $pos_y < 0
            || $pos_y >= \FP\Map::TILE_COUNT_Y
        );
    }

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

            if (!$target) {
                continue;
            }

            if ($target['team'] != $info['team']) {
                ++$count;
            }
        }

        return $count;
    }

    private function nearWeakEnemy($map_tiles)
    {
        $info = $this->info();

        $count = 0;

        $xy_list = [
            [$info['x'] - 1, $info['y']],
            [$info['x'] + 1, $info['y']],
            [$info['x'], $info['y'] - 1],
            [$info['x'], $info['y'] + 1],
        ];

        $min_hp = 101;
        $target_x = -1;
        $target_y = -1;

        foreach ($xy_list as list($x, $y)) {
            $target = $map_tiles[$y][$x];

            if (!$target) {
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

    private function findTeam($map_tiles)
    {
        $info = $this->info();

        $max_hp = 0;
        $target_x = -1;
        $target_y = -1;

        foreach ($this->scanMap($map_tiles) as list($x, $y, $target)) {
            if (is_null($target)) {
                continue;
            } elseif ($target['id'] == $info['id']) { // 본인
                continue;
            } elseif ($target['team'] != $info['team']) { // 다른팀
                continue;
            }

            if ($target['hp'] > $max_hp) {
                $max_hp = $target['hp'];
                $target_x = $target['x'];
                $target_y = $target['y'];
            }
        }

        return [
            $target_x,
            $target_y,
        ];
    }

    private function findEnemy($map_tiles)
    {
        $info = $this->info();

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

        $fn_horizon = function () use ($map_tiles, $target_x, $target_y, $info) {
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

            return '';
        };

        $fn_vertical = function () use ($map_tiles, $target_x, $target_y, $info) {
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
        };

        $fn_list = [];
        if (abs($me_x - $target_x) > abs($me_y - $target_y)) {
            $fn_list = [
                $fn_vertical,
                $fn_horizon,
            ];
        } else {
            $fn_list = [
                $fn_horizon,
                $fn_vertical,
            ];
        }

        $direction = $fn_list[0]();
        if ($direction) {
            return $direction;
        }

        $direction = $fn_list[1]();
        if ($direction) {
            return $direction;
        }

        return '';
    }

    private function moveAnywhere($map_tiles)
    {
        $info = $this->info();

        $xy_list = [
            [$info['x'] - 1, $info['y'], 'left'],
            [$info['x'] + 1, $info['y'], 'right'],
            [$info['x'], $info['y'] - 1, 'top'],
            [$info['x'], $info['y'] + 1, 'bottom'],
        ];

        foreach ($xy_list as list($x, $y, $direction)) {
            if (!$this->vaildPosition($x, $y)) {
                continue;
            }

            $target = $map_tiles[$y][$x];
            if (is_null($target)) {
                return new \FP\Action('move', $direction);
            }
        }

        return null;
    }

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $info = $this->info();

        ++$this->turn_count;

        $enemy_count = $this->nearEnemyCount($map_tiles);

        if ($this->danger_count == 0) {
            if ($enemy_count >= 2) {
                $this->danger_count = 6;
            }
        } else {
            --$this->danger_count;
        }

        // 위험하면
        if ($this->danger_count) {
            if (!$enemy_count && $info['hp'] < 50) {
                return new \FP\Action('recovery', '');
            }

            // 팀에게 도망
            $target = $this->findTeam($map_tiles);
            if ($target) {
                list($target_x, $target_y) = $target;
                $direction = $this->directionToTarget($map_tiles, $target_x, $target_y);

                if ($direction) {
                    return new \FP\Action('move', $direction);
                } else {
                    $action = $this->moveAnywhere($map_tiles);
                    if ($action) {
                        return $action;
                    }
                }
            }
        }

        // 바로 옆 약한 적 찾기
        $target = $this->nearWeakEnemy($map_tiles);
        if ($target) {
            if ($this->turn_count % 3 == 0) {
                if ($info['hp'] < 65) {
                    $action = $this->moveAnywhere($map_tiles);
                    if ($action) {
                        return $action;
                    }
                }
            }

            list($target_x, $target_y) = $target;

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
        }

        // 어딘가의 약한 적을 찾기
        list($target_x, $target_y) = $this->findEnemy($map_tiles);

        $direction = $this->directionToTarget($map_tiles, $target_x, $target_y);

        if ($direction) {
            return new \FP\Action('move', $direction);
        } else {
            $action = $this->moveAnywhere($map_tiles);
            if ($action) {
                return $action;
            }
        }

        return new \FP\Action('recovery', '');
    }
}
