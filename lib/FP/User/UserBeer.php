<?php

namespace FP\User;

use FP\Action;

class UserBeer extends \FP\Character\Character
{
    private $turnCount = 0;
    private $currentTurn = 0;

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        LogUtil::$debug = true;

        $this->currentTurn = $this->turnCount % 3;
        $this->turnCount++;

        // 붙어있는 적을 얻는다
        $nearEnemyDirection = $this->getNearEnemyDirection($map_tiles, $pos_x, $pos_y);

        // 붙어있는 적이 있는지 확인
        if ($nearEnemyDirection == null) {

            // 적당한 적을 찾기
            $nextdirection = $this->getTargetEnemyDirection($map_tiles, $pos_x, $pos_y);

            if ($nextdirection == null) {
                // 적을 향해 가기 어려우면 쉰다
                return new Action('recovery', '');
            }

            // 적을 향해 이동
            return new Action('move', $nextdirection);
        }

        // 붙어있는 적을 공격
        return new Action('attack', $nearEnemyDirection);
    }

    /**
     * 나에게 붙어있는 적 찾기
     */
    private function getNearEnemyDirection($map_tiles, $pos_x, $pos_y)
    {
        $direction = null;
        if ($this->isEnemy($map_tiles, $pos_x, $pos_y - 1)) {
            $direction = 'top';
        } else if ($this->isEnemy($map_tiles, $pos_x - 1, $pos_y)) {
            $direction = 'left';
        } else if ($this->isEnemy($map_tiles, $pos_x, $pos_y + 1)) {
            $direction = 'bottom';
        } else if ($this->isEnemy($map_tiles, $pos_x + 1, $pos_y)) {
            $direction = 'right';
        }

        return $direction;
    }

    /**
     * 나보다 약한 적 중에서 가까운 애 찾기
     */
    private function getTargetEnemyDirection($map_tiles, $pos_x, $pos_y)
    {
        $info = $this->info();

        $enemyArray = [];
        for ($y = 0; $y < 8; $y++) {

            for ($x = 0; $x < 10; $x++) {

                if ($this->isEnemy($map_tiles, $x, $y)) {

                    if ($map_tiles[$y][$x]['hp'] >= $info['hp']) {
                        // 나보다 강하므로 패스
                        continue;
                    }

                    array_push($enemyArray, [
                        'x' => $x,
                        'y' => $y,
                        'name' => $map_tiles[$y][$x]['name'],
                        'value' => abs($x - $pos_x) + abs($y - $pos_y)
                    ]);
                }
            }
        }

        if (sizeof($enemyArray) == 0) {
            return null;
        }

        AUtil::aasort($enemyArray, "value");
        $target_enemy = $enemyArray[0];
        $target_x = $target_enemy['x'];
        $target_y = $target_enemy['y'];

        $direction = null;
        if ($pos_x < $target_x) {
            $direction = "right";
        } else if ($pos_x > $target_x) {
            $direction = "left";
        } else if ($pos_y < $target_y) {
            $direction = "bottom";
        } else if ($pos_y > $target_y) {
            $direction = "top";
        }

        // 가려는데 아군 때문에 막혀있을 수 있으므로 못가면 쉰다
        if ($direction == 'top' && $this->isTeam($map_tiles, $pos_x, $pos_y - 1)) {
            return null;
        } else if ($direction == 'left' && $this->isTeam($map_tiles, $pos_x - 1, $pos_y)) {
            return null;
        } else if ($direction == 'bottom' && $this->isTeam($map_tiles, $pos_x, $pos_y + 1)) {
            return null;
        } else if ($direction == 'right' && $this->isTeam($map_tiles, $pos_x + 1, $pos_y)) {
            return null;
        }

        return $direction;
    }

    private function isEnemy($map_tiles, $pos_x, $pos_y)
    {
        if ($pos_x < 0 && $pos_x >= 10 && $pos_y < 0 && $pos_y >= 8) {
            return false;
        }

        $myTeamNum = $this->info()['team'];
        $targetTeamNum = $map_tiles[$pos_y][$pos_x]['team'];

        if ($targetTeamNum == $myTeamNum) {
            return false;
        } else if ($targetTeamNum == null) {
            return false;
        }
        return true;
    }

    private function isTeam($map_tiles, $pos_x, $pos_y)
    {
        if ($pos_x < 0 && $pos_x >= 10 && $pos_y < 0 && $pos_y >= 8) {
            return false;
        }

        $myTeamNum = $this->info()['team'];
        $targetTeamNum = $map_tiles[$pos_y][$pos_x]['team'];

        if ($targetTeamNum == $myTeamNum) {
            return true;
        }

        return false;
    }
}

class LogUtil
{
    public static $debug = false;

    public static function info($msg)
    {
        if (!self::$debug) {
            return;
        }

        echo "$msg<br/>";
    }

    public static function info2($arr)
    {
        if (!self::$debug) {
            return;
        }

        echo nl2br(print_r($arr, true))."<br/>";
    }
}

class AUtil
{
    public static function aasort(&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }
}