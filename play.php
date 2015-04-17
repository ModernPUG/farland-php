<?php
require_once './vendor/autoload.php';

$map = new \FP\Map();

$player01 = new \FP\User\User01($map, 0, '아무개1', 1);
$player02 = new \FP\User\User02($map, 1, '아무개2', 2);

$player_list = [
    $player01,
    $player02,
];

foreach ($player_list as $player) {
    $map->addCharacter($player);
}

//print_r($map->tiles());

$log_list = [];
foreach ($player_list as $player) {
    $log_list[] = $player->info();
}

for ($i = 0; $i < 3; $i++) {
    foreach ($player_list as $player) {
        $action = $player->action();

        switch ($action->type) {
            case 'move':
                $map->moveCharacter($player, $action->direction);
                $log_list[] = $player->info();
                break;

            case 'attack':
                $obj = $map->objectFromDirectionOfCharacter($player, $action->direction);
                if ($obj instanceof \FP\Character\Character) {
                    $obj->takeDamage(10);
                    $log_list[] = $obj->info();
                }
                break;
        }

        //$tiles = $map->tiles();
        //print_r($tiles);
    }
}

print_r($log_list);
