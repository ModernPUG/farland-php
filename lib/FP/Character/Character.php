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
    private $map = null;
    private $direction = 'bottom';

    final public function __construct(\FP\Map $map, $id, $name, $team)
    {
        $this->id = $id;
        $this->map = $map;
        $this->name = $name;
        $this->team = $team;
    }

    final public function info()
    {
        list($x, $y) = $this->position();

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
        list($pos_x, $pos_y) = $this->map->positionOfCharacter($this);
        return $this->_action($this->map->tiles(), $pos_x, $pos_y);
    }

    final public function takeDamage($damage)
    {
        $this->hp -= $damage;
        if ($this->hp < 0) {
            $this->hp = 0;
        }
    }

    abstract protected function _action($map_tiles, $pos_x, $pos_y);
}
