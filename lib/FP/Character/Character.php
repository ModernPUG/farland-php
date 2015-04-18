<?php
namespace FP\Character;

/**
 *
 */
abstract class Character
{
    private $name;
    private $team;
    private $hp = 100;
    private $auth = null;
    private $map = null;
    private $direction = 'bottom';

    final public function __construct($auth, \FP\Map $map, $id, $name, $team)
    {
        $this->id = $id;
        $this->map = $map;
        $this->auth = $auth;
        $this->name = $name;
        $this->team = $team;
    }

    final public function info()
    {
        $position = $this->position();
        $x = isset($position[0]) ? $position[0] : 0;
        $y = isset($position[1]) ? $position[1] : 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'team' => $this->team,
            'x' => $x,
            'y' => $y,
            'direction' => $this->direction,
            'hp' => $this->hp,
        ];
    }

    final public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    final public function position()
    {
        return $this->map->positionOfCharacter($this);
    }

    final public function action()
    {
        $position = $this->map->positionOfCharacter($this);
        $pos_x = isset($position[0]) ? $position[0] : 0;
        $pos_y = isset($position[1]) ? $position[1] : 0;

        return $this->_action($this->map->tiles(), $pos_x, $pos_y);
    }

    final public function takeDamage($auth)
    {
        if ($this->auth !== $auth) {
            return;
        }

        $this->hp -= rand(3, 5);
        if ($this->hp < 0) {
            $this->hp = 0;
        }
    }

    final public function recovery($auth)
    {
        if ($this->auth !== $auth) {
            return;
        }

        $this->hp += rand(2, 3);
        if ($this->hp > 100) {
            $this->hp = 100;
        }
    }

    abstract protected function _action($map_tiles, $pos_x, $pos_y);
}
