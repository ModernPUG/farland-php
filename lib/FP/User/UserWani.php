<?php
namespace FP\User;

use FP\Action;

class UserWani extends \FP\Character\Character
{	
	protected $myTurn = 0;
	protected $myStartX = 0;
	protected $myStartY = 0;

	protected $myDirection = 'right';

    /**
     * @param $map
     * @param $x
     * @param $y
     */
    protected function init($map, $x, $y)
    {
        $this->myStartX = $x;
        $this->myStartY = $y;
    }

    /**
     * @param $map
     * @param $x
     * @param $y
     * @return Action
     */
    protected function _action($map, $x, $y)
    {
        if ($this->myTurn === 0) {
            $this->init($map, $x, $y);
        }
        $this->myTurn++;

        $users = $this->findArroundUsers($map, $x, $y);
        if (count($users)) {
            return new Action('attack', $users[0][0]);
        }

        return new Action('move', $this->getDirectionByOtherPositions($map, $x, $y));
    }// action move , atack

    protected function getDirectionByOtherPositions($map, $myX, $myY)
    {
        $team = $this->getMyTeam();

        foreach ($map as $y => $row) {
            foreach ($row as $x => $col) {
                if (isset($map[$y][$x]) && $this->getOtherTeam($map[$y][$x]) != $team) {
                    // echo "[", $x, $myY, $y, $myY, "]\n";
                    if (abs($x - $myX) > abs($y - $myY)) {
                        return ($x - $myX > 0) ? 'right' : 'left';
                    } else {
                        return ($y - $myY > 0) ? 'bottom' : 'top';
                    }
                }
            }
        }
        return null;
    }

    protected function getDirectionByEdge()
    {
                // if ('nope' !== $edge = $this->isEdge($x, $y)) {
        //     // switch ($edge) {
        //     //     case 'left' :
        //     //         $this->myDirection = 'right';
        //     //         break;
        //     //     case 'right' :
        //     //         $this->myDirection = 'left';
        //     //         break;
        //     //     case 'top' :
        //     //         $this->myDirection = 'bottom';
        //     //         break;
        //     //     case 'bottom' :
        //     //         $this->myDirection = 'top';
        //     //         break;
        //     // }
        // }

    }
    protected function isEdge($x, $y)
    {
        if ($x >= 9) return 'right';
        if ($x <= 0) return 'left';
        if ($y >= 7) return 'bottom';
        if ($y <= 0) return 'top';
        return 'nope';
    }

    protected function getMyTeam()
    {
        $info = $this->info();
        return $info['team'];
    }

    protected function findArroundUsers($map, $x, $y)
    {
        // echo $x, $y,"\n";
        $team = $this->getMyTeam();
        $users = [];

        if (isset($map[$y][$x-1])) {
            if ($this->getOtherTeam($map[$y][$x-1]) != $team) {
            // echo "left";
                $users[] = ['left', $map[$y][$x-1]];
            }
        }
        if (isset($map[$y][$x+1])) {
            if ($this->getOtherTeam($map[$y][$x+1]) != $team) {
            // echo "right";
                $users[] = ['right', $map[$y][$x+1]];
            }
        }
        if (isset($map[$y-1][$x])) {
            if ($this->getOtherTeam($map[$y-1][$x]) != $team) {
            // echo "top";
                $users[] = ['top', $map[$y-1][$x]];
            }
        }
        if (isset($map[$y+1][$x])) {
            if ($this->getOtherTeam($map[$y+1][$x]) != $team) {
            // echo "bottom";
                $users[] = ['bottom', $map[$y+1][$x]];
            }
        }
        return $users;
    }

    protected function getOtherTeam($user)
    {
        if (isset($user['team'])) {
    		return $user['team'];
    	}
    	return '?';
    	return $user['team'];
    }
}
