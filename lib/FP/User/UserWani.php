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

        if ($this->getHp() < 10) {
            $direction = $this->getDirectionNullWay($map, $x, $y);
            return new Action('move', $direction);
        }

        $users = $this->findArroundUsers($map, $x, $y);
        if (count($users)) {
            return new Action('attack', $users[0][0]);
        }

        $direction = $this->getDirectionByOtherPositions($map, $x, $y);
        if ($this->getHp() < 40) {
            $direction = $this->getDirectionNullWay($map, $x, $y);
//            if ($direction == 'left') $direction = 'right';
//            if ($direction == 'top') $direction = 'bottom';
        }

        return new Action('move', $direction);
    }

    protected function getDirectionNullWay($map, $x, $y)
    {
        if (!isset($map[$y][$x-1])) {
            return 'left';
        }
        if (!isset($map[$y][$x+1])) {
            return 'right';
        }
        if (!isset($map[$y-1][$x])) {
            return 'top';
        }
        if (!isset($map[$y+1][$x])) {
            return 'bottom';
        }
        return '??';
    }

    protected function getDirectionByOtherPositions($map, $myX, $myY)
    {
        $team = $this->getMyTeam();
        $otherUsers = $this->getOthersideUserPositons($map, $myX, $myY);

        $shortestDistance = 100000;
        $shortestUser = $otherUsers[0];
        foreach ($otherUsers as $user) {
            //print_r($this->getDistance($user));
            if ($shortestDistance > $this->getDistance($user)) {
                $shortestDistance = $this->getDistance($user);
                $shortestUser = $user;
            }
        }

        // go to shortest
        if (abs($shortestUser['x'] - $myX) > abs($shortestUser['y'] - $myY)) {
            return ($shortestUser['x'] - $myX > 0) ? 'right' : 'left';
        } else {
            return ($shortestUser['y'] - $myY > 0) ? 'bottom' : 'top';
        }
        return null;
    }

    /**
     * @param $otherUser
     * @return number
     */
    protected function getDistance($otherUser)
    {
        $info = $this->info();
        return abs($info['x'] - $otherUser['x']) + abs($info['y'] - $otherUser['y']);
    }

    protected function getOthersideUserPositons($map, $myX, $myY)
    {
        $users = [];
        $team = $this->getMyTeam();
        foreach ($map as $y => $row) {
            foreach ($row as $x => $col) {
                if (isset($map[$y][$x]) && $this->getOtherTeamByUser($map[$y][$x]) != $team) {
                    $users[] = $map[$y][$x];
                }
            }
        }
        return $users;
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
            if ($this->getOtherTeamByUser($map[$y][$x-1]) != $team) {
            // echo "left";
                $users[] = ['left', $map[$y][$x-1]];
            }
        }
        if (isset($map[$y][$x+1])) {
            if ($this->getOtherTeamByUser($map[$y][$x+1]) != $team) {
            // echo "right";
                $users[] = ['right', $map[$y][$x+1]];
            }
        }
        if (isset($map[$y-1][$x])) {
            if ($this->getOtherTeamByUser($map[$y-1][$x]) != $team) {
            // echo "top";
                $users[] = ['top', $map[$y-1][$x]];
            }
        }
        if (isset($map[$y+1][$x])) {
            if ($this->getOtherTeamByUser($map[$y+1][$x]) != $team) {
            // echo "bottom";
                $users[] = ['bottom', $map[$y+1][$x]];
            }
        }
        return $users;
    }

    protected function getOtherTeamByUser($user)
    {
        if (isset($user['team'])) {
    		return $user['team'];
    	}
    	return '?';
    	return $user['team'];
    }


    protected function getHp()
    {
        $info = $this->info();
        return $info['hp'];
    }


}
