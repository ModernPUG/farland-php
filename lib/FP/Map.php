<?php
namespace FP;

class Map
{
    const TILE_COUNT_X = 10;
    const TILE_COUNT_Y = 8;

    private $tiles = [[]];
    private $character_list = [];

    public function __construct()
    {
        for ($y = 0; $y < self::TILE_COUNT_Y; $y++) {
            for ($x = 0; $x < self::TILE_COUNT_X; $x++) {
                $this->tiles[$y][$x] = null;
            }
        }
    }

    public function tiles()
    {
        $tiles = [[]];

        for ($y = 0; $y < self::TILE_COUNT_Y; $y++) {
            for ($x = 0; $x < self::TILE_COUNT_X; $x++) {
                if ($this->tiles[$y][$x] instanceof \FP\Character\Character) {
                    $tiles[$y][$x] = $this->tiles[$y][$x]->info();
                } else {
                    $tiles[$y][$x] = null;
                }
            }
        }

        return $tiles;
    }

    public function addCharacter(\FP\Character\Character $character)
    {
        $this->character_list[] = $character;

        $info = $character->info();

        for ($y = 1; $y < self::TILE_COUNT_Y; $y = $y + 2) {
            if ($info['team'] == 1) {
                $x = 0;
                $y2 = $y;
            } else {
                $x = self::TILE_COUNT_X - 1;
                $y2 = $y + 1;
            }

            if (is_null($this->tiles[$y2][$x])) {
                $this->tiles[$y2][$x] = $character;
                break;
            }
        }
    }

    public function removeCharacter(\FP\Character\Character $character)
    {
        foreach ($this->character_list as $i => $_character) {
            if ($_character === $character) {
                unset($this->character_list[$i]);
            }
        }

        $pos = $this->positionOfCharacter($character);
        if ($pos) {
            $this->tiles[$pos['y']][$pos['x']] = null;
        }
    }

    private function xyDirection($direction)
    {
        $x = 0;
        $y = 0;

        switch ($direction) {
            case 'left':
                $x = -1;
                break;

            case 'right':
                $x = 1;
                break;

            case 'top':
                $y = -1;
                break;

            case 'bottom':
                $y = 1;
                break;
        }

        return [
            $x,
            $y,
            'x' => $x,
            'y' => $y,
        ];
    }

    private function vaildPosition($pos_x, $pos_y)
    {
        // 맵을 벗어난 위치
        return !(
            $pos_x < 0
            || $pos_x >= self::TILE_COUNT_X
            || $pos_y < 0
            || $pos_y >= self::TILE_COUNT_Y
        );
    }

    public function positionFromDirection($pos_x, $pos_y, $direction)
    {
        list($x, $y) = $this->xyDirection($direction);
        $pos_x += $x;
        $pos_y += $y;

        if (!$this->vaildPosition($pos_x, $pos_y)) {
            return [];
        }

        return [
            $pos_x,
            $pos_y,
            'x' => $pos_x,
            'y' => $pos_y,
        ];
    }

    public function positionOfCharacter(\FP\Character\Character $character)
    {
        for ($y = 0; $y < self::TILE_COUNT_Y; $y++) {
            for ($x = 0; $x < self::TILE_COUNT_X; $x++) {
                if ($this->tiles[$y][$x] === $character) {
                    return [
                        $x,
                        $y,
                        'x' => $x,
                        'y' => $y,
                    ];
                }
            }
        }

        return [];
    }

    public function moveCharacter(\FP\Character\Character $character, $direction)
    {
        $position = $this->positionOfCharacter($character);
        $old_x = isset($position[0]) ? $position[0] : 0;
        $old_y = isset($position[1]) ? $position[1] : 0;

        $new_pos = $this->positionFromDirection($old_x, $old_y, $direction);
        if (!$new_pos) {
            return;
        }

        list($new_x, $new_y) = $new_pos;

        // 비어있지 않은 위치
        if (!is_null($this->tiles[$new_y][$new_x])) {
            return;
        }

        $this->tiles[$old_y][$old_x] = null;
        $this->tiles[$new_y][$new_x] = $character;

        $character->setDirection($direction);
    }

    public function objectFromDirectionOfCharacter(\FP\Character\Character $character, $direction)
    {
        //list($old_x, $old_y) = $this->positionOfCharacter($character);
    	$position = $this->positionOfCharacter($character);
    	$old_x = isset($position[0]) ? $position[0] : 0;
    	$old_y = isset($position[1]) ? $position[1] : 0;

        $pos = $this->positionFromDirection($old_x, $old_y, $direction);
        if (!$pos) {
            return false;
        }

        list($pos_x, $pos_y) = $pos;

        return $this->tiles[$pos_y][$pos_x];
    }

    public function objectAt($pos_x, $pos_y)
    {
        if ($this->vaildPosition($pos_x, $pos_y)) {
            return $this->tiles[$pos_y][$pos_x];
        } else {
            return false;
        }
    }
}
