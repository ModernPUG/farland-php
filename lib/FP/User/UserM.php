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
    private $current_map;

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $this->turn = is_null($this->turn) ? 1 : $this->turn+1;

        if (get_class($this) !== 'FP\User\UserM')
        {
            return new \FP\Action('move', 'up');
        }

        $this->id = $this->info()['id'];
        $this->team = $this->info()['team'];
        $this->x = $this->info()['x'];
        $this->y = $this->info()['y'];

        $direction = $this->findNextEnemy($map_tiles);
        if ($direction)
        {
            return new \FP\Action('attack', $direction);
        }

        if ($this->turn % self::MAX_TURN_COUNT === 3)
        {
            return new \FP\Action('nil', 'here');
        }

        $rand = ['up', 'bottom', 'left', 'right'];
        shuffle($rand);
        return new \FP\Action('move', $rand[0]);
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

    private function isEnemy($info)
    {
        return ($info && $info['team'] !== $this->team);
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
            if ($a['hp'] == $b['hp'])
            {
                return 0;
            }

            return ($a['hp'] < $b['hp']);
        });

        return $enemies;
    }

    private function isReachable($pos_x, $pos_y)
    {
        $next_x = ($this->x > $pos_x) ? $this->x - 1 : $this->x + 1;
        $next_y = ($this->y > $pos_y) ? $this->y - 1 : $this->y + 1;
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
