<?php
namespace FP\User;

use FP\Action;

class Lhs extends \FP\Character\Character
{
    private $actionCount = 0;
    private $me;
    private $lhsMapTiles;
    private $weakEnemy;

    protected function _action($map_tiles, $pos_x, $pos_y)
    {
        $this->actionCount++;
        $this->lhsMapTiles = $map_tiles;
        $this->weakEnemy = null;

        $this->me = $this->info();

//        if($this->actionCount == 10){
//            echo "<pre>";
//        print_r($this->me);
//            print_r($this->lhsMapTiles);
//            echo "</pre>";
//            exit;
//        }

        $this->lhsLookAround();

        $result = null;

        $result = $this->lhsAttack();

        if($result)
            return $result;

        $this->findWeakEnemy();

        //error_log('weak x:'.$this->weakEnemy['x']. ', weak y: '. $this->weakEnemy['y']);
        //$this->moveToWeakEnemy();

        if(!$this->checkCanGo()){
            while(!$this->checkCanGo()){
                $this->lhsSwitchDirection();
            }
        }

        return $this->lhsMove();
    }

    function moveToWeakEnemy()
    {
        $this->chooseDirection();
    }

    function chooseDirection()
    {
        $distanceX = $this->me['x'] - $this->weakEnemy['x'];
    }

    function findWeakEnemy()
    {
        for($i=0; $i < 8; $i++) {
            for($j=0; $j < 10; $j++) {
                if($this->lhsMapTiles[$j][$i]){
                    if($this->me['team'] != $this->lhsMapTiles[$j][$i]['team']) {
                        if(!$this->weakEnemy){
                            $this->weakEnemy = $this->lhsMapTiles[$j][$i];
                        } else {
                            if($this->weakEnemy['hp'] > $this->lhsMapTiles[$j][$i]['hp']) {
                                $this->weakEnemy = $this->lhsMapTiles[$j][$i];
                            }
                        }
                    }
                }
            }
        }
    }

    function lhsAttack()
    {
        $action = null;

        //오른쪽에 적이 있으면 공격한다
        if($this->me['right']) {
            if($this->me['right']['team'] != $this->me['team']) {
                $action = new \FP\Action('attack', 'right');
            }
        }

        //왼쪽에에 적이 있으면공격한다
        if($this->me['left']) {
            if($this->me['left']['team'] != $this->me['team']) {
                $action = new \FP\Action('attack', 'left');
            }
        }

        //위쪽에 적이 있으면공격한다
        if($this->me['top']) {
            if($this->me['top']['team'] != $this->me['team']) {
                $action = new \FP\Action('attack', 'top');
            }
        }

        //아래쪽에 적이 있으면공격한다
        if($this->me['bottom']) {
            if($this->me['bottom']['team'] != $this->me['team']) {
                $action = new \FP\Action('attack', 'bottom');
            }
        }

        return $action;
    }

    function lhsLookAround()
    {
        //오른쪽을 본다
        if($this->me['x'] + 1 > 9){
            $this->me['right'] = '';
        } else {
            $this->me['right'] = $this->lhsMapTiles[$this->me['y']][$this->me['x'] + 1];
        }

        //왼쪽을 본다
        if($this->me['x'] - 1 < 0) {
            $this->me['left'] = '';
        } else {
            $this->me['left'] = $this->lhsMapTiles[$this->me['y']][$this->me['x'] - 1];
        }

        //위쪽을 본다
        if($this->me['y'] - 1 < 0) {
            $this->me['top'] = '';
        } else {
            $this->me['top'] = $this->lhsMapTiles[$this->me['y'] - 1][$this->me['x']];
        }

        //아래쪽을 본다
        if($this->me['y'] + 1 > 7) {
            $this->me['bottom'] = '';
        } else {
            $this->me['bottom'] = $this->lhsMapTiles[$this->me['y'] + 1][$this->me['x']];
        }
    }

    function checkCanGo()
    {
        switch($this->me['direction'])
        {
            case 'left':
                if($this->me['x'] == 0 || $this->me['left'])
                    return false;
                else
                    return true;
                break;
            case 'right':
                if($this->me['x'] == 9 || $this->me['right'])
                    return false;
                else
                    return true;
                break;
            case 'top':
                if($this->me['y'] == 0 || $this->me['top'])
                    return false;
                else
                    return true;
                break;
            case 'bottom':
                if($this->me['y'] == 7 || $this->me['bottom'])
                    return false;
                else
                    return true;
                break;
        }
    }

    function lhsSwitchDirection()
    {
        switch($this->me['direction'])
        {
            case 'left':
                $this->me['direction'] = 'top';
                break;
            case 'right':
                $this->me['direction'] = 'bottom';
                break;
            case 'top':
                $this->me['direction'] = 'right';
                break;
            case 'bottom':
                $this->me['direction'] = 'left';
                break;
        }
    }

    function lhsMove()
    {
        return new \FP\Action('move', $this->me['direction']);
    }
}
