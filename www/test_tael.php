<?php
error_reporting(E_ALL - E_NOTICE);
require_once '../vendor/autoload.php';

$map = new \FP\Map();

$player01 = new \FP\User\User01($map, 1, '아무개1', 1);
$player02 = new \FP\User\User01($map, 2, '아무개2', 1);
$player03 = new \FP\User\User01($map, 3, '아무개3', 1);
$player04 = new \FP\User\UserTaeL($map, 4, 'TaeL', 2);

/** @var \FP\Character[] $player_list */
$player_list = [
    $player01,
    $player02,
    $player03,
    $player04,
];

foreach ($player_list as $player) {
    $map->addCharacter($player);
}

$log_list = [];
foreach ($player_list as $player) {
    $log_list[] = $player->info();
}

for ($turn_count = 0; $turn_count < 300; $turn_count++) {
    $team_check = [];
    foreach ($player_list as $player) {
        $info = $player->info();
        $team_check[$info['team']] = true;
    }

    if (count($team_check) == 1) {
        break;
    }

    shuffle($player_list);

    foreach ($player_list as $pi => $player) {
        for ($i = 0; $i < 3; $i++) {
            $action = $player->action();

            switch ($action->type) {
                case 'move':
                    $map->moveCharacter($player, $action->direction);
                    $log_list[] = $player->info();
                    break;

                case 'attack':
                    $obj = $map->objectFromDirectionOfCharacter($player, $action->direction);
                    if ($obj instanceof \FP\Character\Character) {
                        $player->setDirection($action->direction);
                        $log_list[] = $player->info();

                        $obj->takeDamage(rand(7, 10));
                        $info = $obj->info();
                        $log_list[] = $info;

                        if (!$info['hp']) {
                            $map->removeCharacter($player);
                            unset($player_list[$pi]);
                        }
                    }
                    break;
            }
//            echo $player->info()['name'] . 'player----------<br>';
//            var_dump($action);
        }
    }
}

