<?php
namespace FP\User;

class UserM extends \FP\Character\Character
{
    const MAX_TURN_COUNT = 3;

    private $turn;
    private $x;
    private $y;
    protected $id;
    private $team;
    private $hp;
    private $current_map;

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $this->turn = is_null($this->turn) ? 1 : $this->turn+1;

        if (get_class($this) !== 'FP\User\UserM')
        {
            return new \FP\Action('nil', 'here');
        }

        $this->id = $this->info()['id'];
        $this->team = $this->info()['team'];
        $this->x = $this->info()['x'];
        $this->y = $this->info()['y'];
        $this->hp = $this->info()['hp'];

        $direction = $this->findNextEnemy($map_tiles);
        if ($direction)
        {
            return new \FP\Action('attack', $direction);
        }

        if ($this->turn % self::MAX_TURN_COUNT === 3)
        {
            return new \FP\Action('nil', 'here');
        }

        $enemies = $this->getRemainEnemies($map_tiles);

        // $cand_enemy = $this->availableGroupAttackEnemy($map_tiles, $enemies);
        // if ($cand_enemy)
        // {
        //     $direction = $this->chase($map_tiles, $enemy['x'], $enemy['y']);
        //     if ($direction)
        //     {
        //         return new \FP\Action('move', $direction);
        //     }
        // }

        foreach($enemies as $enemy)
        {
            if ($enemy['hp'] > $this->hp)
            {
                continue;
            }

            $direction = $this->chase($map_tiles, $enemy['x'], $enemy['y']);
            if ($direction)
            {
                return new \FP\Action('move', $direction);
            }
        }

        $team = $this->getRemainTeam($map_titles);

        foreach($team as $member)
        {
            $direction = $this->chase($map_tiles, $member['x'], $member['y']);
            if ($direction)
            {
                return new \FP\Action('move', $direction);
            }
        }

        $rand = ['up', 'bottom', 'left', 'right'];
        return new \FP\Action('move', $rand[rand(0, 3)]);
    }

    private function findNextEnemy($map_tiles)
    {
        $cand = [];
        $next = @$map_tiles[$this->y-1][$this->x];
        if ($this->isEnemy($next))
        {
            $cand['top'] = $next['hp'];
        }
        $next = @$map_tiles[$this->y+1][$this->x];
        if ($this->isEnemy($next))
        {
            $cand['bottom'] = $next['hp'];
        }
        $next = @$map_tiles[$this->y][$this->x-1];
        if ($this->isEnemy($next))
        {
            $cand['left'] = $next['hp'];
        }
        $next = @$map_tiles[$this->y][$this->x+1];
        if ($this->isEnemy($next))
        {
            $cand['right'] = $next['hp'];
        }

        asort($cand);

        $enemy = key($cand);
        return $enemy;
    }

    private function findNextTeam($map_tiles, $x, $y)
    {
        $cand = [];
        $next = @$map_tiles[$y-1][$x];
        if (!$this->isEnemy($next))
        {
            $cand['top'] = $next['hp'];
        }
        $next = @$map_tiles[$y+1][$x];
        if (!$this->isEnemy($next))
        {
            $cand['bottom'] = $next['hp'];
        }
        $next = @$map_tiles[$y][$x-1];
        if (!$this->isEnemy($next))
        {
            $cand['left'] = $next['hp'];
        }
        $next = @$map_tiles[$y][$x+1];
        if (!$this->isEnemy($next))
        {
            $cand['right'] = $next['hp'];
        }

        arsort($cand);

        return !empty($cand);
    }

    private function isEnemy($info)
    {
        return ($info && $info['team'] !== $this->team);
    }

    private function availableGroupAttackEnemy($map_tiles, $enemies)
    {
        foreach($enemies as $enemy)
        {
            if ($this->findNextTeam($map_tiles, $enemy['x'], $enemy['y']))
            {
                return $enemy;
            }
        }
    }

    private function getRemainEnemies($map_tiles)
    {
        $enemies = [];

        for ($i=0; $i < $this->maxWidth(); $i++)
        {
            for ($j=0; $j < $this->maxHeight(); $j++)
            {
                $next = $map_tiles[$j][$i];
                if ($next && $next['team'] !== $this->team)
                {
                    $enemies[] = $next;
                }
            }
        }

        uasort($enemies, function($a, $b) {
            $dist_a = abs($a['x'] - $this->x) + abs($a['y'] - $this->y);
            $dist_b = abs($b['x'] - $this->x) + abs($b['y'] - $this->y);
            if ($dist_a == $dist_b)
            {
                return 0;
            }

            return ($dist_a < $dist_b);
        });

        return $enemies;
    }

    private function getRemainTeam($map_tiles)
    {
        $team = [];

        for ($i=0; $i < $this->maxWidth(); $i++)
        {
            for ($j=0; $j < $this->maxHeight(); $j++)
            {
                $next = $map_tiles[$j][$i];
                if ($next && $next['team'] === $this->team)
                {
                    $team[] = $next;
                }
            }
        }

        uasort($team, function($a, $b) {
            $dist_a = abs($a['x'] - $this->x) + abs($a['y'] - $this->y);
            $dist_b = abs($b['x'] - $this->x) + abs($b['y'] - $this->y);
            if ($dist_a == $dist_b)
            {
                return 0;
            }

            return ($dist_a < $dist_b);
        });

        return $team;
    }

    private function chase($map_tiles, $x, $y)
    {
        $dist_x = abs($this->x - $x);
        $dist_y = abs($this->y - $y);

        if ($dist_x > $dist_y)
        {
            $next = ($this->x > $x) ? $this->x - 1 : $this->x + 1;
            if (is_null($map_tiles[$this->y][$next]))
            {
                return ($this->x > $x) ? 'left' : 'right';
            }
        }

        if ($this->y != $y)
        {
            $next = ($this->y > $y) ? $this->y - 1 : $this->y + 1;
            if (is_null($map_tiles[$next][$this->x]))
            {
                return ($this->y > $y) ? 'top' : 'bottom';
            }
        }
    }

    private function maxWidth()
    {
        return \FP\Map::TILE_COUNT_X;
    }

    private function maxHeight()
    {
        return \FP\Map::TILE_COUNT_Y;
    }
}
