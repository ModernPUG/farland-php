<?php
namespace FP\User;

class UserBeer extends \FP\Character\Character
{
    protected function _action($map_tiles, $pos_x, $pos_y)
    {
    	//phpinfo();
    	// 주변에 타팀이 붙어있는지 확인
    	$nearEnemyDirection = $this->getNearEnemyDirection($map_tiles, $pos_x, $pos_y);
    	if ( $nearEnemyDirection == null ) {
    		// 이동
    		$nextdirection = $this->getTargetEnemyDirection($map_tiles, $pos_x, $pos_y);
    		return new \FP\Action('move', $nextdirection);
    	} else {
    		// 공격
    		$nextdirection = $nearEnemyDirection;
    		return new \FP\Action('attack', $nextdirection);
    	}
    }

    private function getMyTeamNum()
    {
    	$info = $this->info();
    	return $info['team'];
    }

    private function isEnemy($map_tiles, $pos_y, $pos_x)
    {
    	$targetTypeNum = $map_tiles[$pos_y][$pos_x]['team'];

    	// echo "me: ".$this->getMyTeamNum()."<br/>";
    	// echo "target: ".($targetTypeNum === null ? "null" : $targetTypeNum)."<br/>";
    	if ( $targetTypeNum == $this->getMyTeamNum() ) {
    		return false;
    	} else if ( $targetTypeNum == null ) {
    		return false;
    	}
    	return true;
    }

    /**
     * 적 찾기 (붙어있어서 때릴 애)
     */
    private function getNearEnemyDirection($map_tiles, $pos_x, $pos_y) {
    	if ( $this->validArrayPosition($pos_x, $pos_y - 1) && $this->isEnemy($map_tiles, $pos_y - 1, $pos_x) ) {
    		return 'top';
    	} else if ( $this->validArrayPosition($pos_x - 1, $pos_y) && $this->isEnemy($map_tiles, $pos_y, $pos_x - 1)) {
    		return 'left';
    	} else if ( $this->validArrayPosition($pos_x, $pos_y + 1) && $this->isEnemy($map_tiles, $pos_y + 1, $pos_x)) {
    		return 'bottom';
    	} else if ( $this->validArrayPosition($pos_x + 1, $pos_y) && $this->isEnemy($map_tiles, $pos_y, $pos_x + 1))  {
    		return 'right';
    	}
    	return null;
    }

    private function validArrayPosition($pos_x, $pos_y)
    {
    	return ($pos_x >= 0 && $pos_x < 10 && $pos_y >= 0 && $pos_y < 8 );
    }

    /**
     * 적 찾기 (때릴려고 다가갈 가까운애)
     */
    private function getTargetEnemyDirection($map_tiles, $pos_x, $pos_y) {
    	$enemyArray = [];
    	for ( $y = 0 ; $y < 8 ; $y++ ) {
    		for ( $x = 0 ; $x < 10 ; $x++ ) {
    			if ( $map_tiles[$y][$x]['team'] != $this->getMyTeamNum() )
    				array_push($enemyArray, ['x' => $x, 'y' => $y, 'value' => $x + $y]);
    		}
    	}

    	$enemyArray = $this->aasort($enemyArray, "value");
    	$target_x = $enemyArray[0]['x'];
    	$target_y = $enemyArray[0]['y'];
    	// echo "------------{<br/>";
    	// echo nl2br(print_r($enemyArray[0], true));
    	// echo "}------------<br/>";

    	$direction = "left";
    	if ( abs($pos_x - $target_x) > abs($pos_y - $target_y) ) {
    		// y로 움직여
    		// echo "test: ($pos_x , $target_x)";
	    	if ( $pos_x < $target_x ) {
	    		$direction = 'right';
	    	} else {
	    		$direction = 'left';
	    	}
    	} else {
    		if ( $pos_y < $target_y ) {
    			$direction = 'bottom';
    		} else {
    			$direction = 'top';
    		}
    	}
    	return $direction;
    }

    private function aasort (&$array, $key) {
	    $sorter=array();
	    $ret=array();
	    reset($array);
	    foreach ($array as $ii => $va) {
	        $sorter[$ii]=$va[$key];
	    }
	    asort($sorter);
	    foreach ($sorter as $ii => $va) {
	        $ret[$ii]=$array[$ii];
	    }
	    $array=$ret;
	    return $array;
		}
}
