<?php
require_once '../vendor/autoload.php';

$map = new \FP\Map();

$player01 = new \FP\User\UserWani($map, 1, '아무개1', 1);
$player02 = new \FP\User\User02($map, 2, '아무개2', 2);
$player03 = new \FP\User\User02($map, 3, '아무개3', 1);
$player04 = new \FP\User\User02($map, 4, '아무개4', 2);

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
        $info = $player->info();
        if ($info['hp'] <= 0) {
            unset($player_list[$pi]);
        }

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
                            $map->removeCharacter($obj);
                        }
                    }
                    break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
<link rel="stylesheet" href="./index.css?__v=1" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script src="./pixi.dev.js"></script>
<title>파랜드 PHP - Ver 0.1</title>
</head>
<body>

<div>
    <div id="game-stage" style="float: left;"></div>
    <textarea id="txt-info" style="width: 300px; height: 256px; float: left; border: 2px solid #000; background: #000; color: #fff;"></textarea>
</div>

<script>
(function () {
var audioBgm = new Audio('./resource/bgm.mp3');
audioBgm.loop = true;

var logList = JSON.parse('<?=json_encode($log_list)?>');
var logPos = 0;

var TILE_WIDTH = 32;
var TILE_HEIGHT = 32;

var TILE_COUNT_X = <?=\FP\Map::TILE_COUNT_X?>;
var TILE_COUNT_Y = <?=\FP\Map::TILE_COUNT_Y?>;

var STAGE_WIDTH = TILE_WIDTH * TILE_COUNT_X;
var STAGE_HEIGHT = TILE_HEIGHT * TILE_COUNT_Y;

var stage;
var renderer;

var unitPlayerList = {};
var stageUnitList = [];

var Animate = function (
    sprite,
    tileW,
    tileH,
    tileCountX,
    tileCountY,
    startTileNumX,
    startTileNumY,
    frameDelay
) {
    var frameNum = 0;

    var tileStartX = -(startTileNumX * tileW);
    var tileStartY = -(startTileNumY * tileH);

    var tilePositionX = tileStartX;
    var tilePositionY = tileStartY;

    this.play = function () {
        if (++frameNum >= frameDelay) {
            frameNum = 0;

            if (tilePositionX > -(tileW * (tileCountX - 1)) + tileStartX) {
                tilePositionX -= tileW;
            } else {
                tilePositionX = tileStartX;

                if (tilePositionY > -(tileH * (tileCountY - 1)) + tileStartY) {
                    tilePositionY -= tileH;
                } else {
                    tilePositionY = tileStartY;
                }
            }
        }

        sprite.tilePosition.x = tilePositionX;
        sprite.tilePosition.y = tilePositionY;
    };

    this.setFrameDelay = function (value) {
        frameDelay = value;
    };
};

var UnitObject = function (sprite) {
    this.sprite = sprite;

    this.transition = function () {
        this._transition();
    };
};

var UnitPlayer = function (info) {
    var self = this;

    var tileWidth = 32;
    var tileHeight = 32;

    var tileCountX = 3;
    var tileCountY = 4;

    var frameDelay = 20;

    var texture = PIXI.Texture.fromImage('./resource/players.png');
    var sprite = new PIXI.TilingSprite(texture, tileWidth, tileHeight);

    UnitObject.call(this, sprite);

    var hpBar = new PIXI.Graphics();
    sprite.addChild(hpBar);

    this.info = {};

    var index = info.id - 1;

    var startNumX = index * tileCountX;
    var startNumY = Math.floor(index / 4) * tileCountY;

    var startX = -(startNumX * tileWidth);
    var startY = -(startNumY * tileHeight);

    var animateBottom = new Animate(sprite, tileWidth, tileHeight, 3, 1, startNumX, startNumY, frameDelay);
    var animateTop = new Animate(sprite, tileWidth, tileHeight, 3, 1, startNumX, startNumY + 3, frameDelay);
    var animateLeft = new Animate(sprite, tileWidth, tileHeight, 3, 1, startNumX, startNumY + 1, frameDelay);
    var animateRight = new Animate(sprite, tileWidth, tileHeight, 3, 1, startNumX, startNumY + 2, frameDelay);
    var animate = animateBottom;

    var motion = null;
    var motionDemage = function () {
        var count = 0;

        this.play = function () {
            if (++count > 10) {
                motion = null;

                if (self.info.hp <= 0) {
                    removeStageUnit(self);
                }
            } else {
                sprite.alpha = count % 2 ? 0.5 : 1;
            }
        };
    };

    this.changeInfo = function (info) {
        switch (info.direction) {
            case 'bottom':
                animate = animateBottom;
                break;

            case 'top':
                animate = animateTop;
                break;

            case 'left':
                animate = animateLeft;
                break;

            case 'right':
                animate = animateRight;
                break;
        }

        hpBar.clear();
        hpBar.lineStyle(2, 0x00FF00, 1);
        hpBar.moveTo(0, 1);
        hpBar.lineTo(tileWidth * info.hp / 100, 1);

        if (info.hp < this.info.hp) {
            motion = new motionDemage();
        }

        this.info = info;

        this.sprite.x = info.x * tileWidth;
        this.sprite.y = info.y * tileHeight;
    };

    this.changeInfo(info);

    this._transition = function () {
        if (animate) {
            animate.play();
        }

        if (motion) {
            motion.play();
        }
    };
};
UnitPlayer.prototype = Object.create(UnitObject.prototype);
UnitPlayer.prototype.constructor = UnitPlayer;

function addStageUnit(unit) {
    stageUnitList.push(unit);
    stage.addChild(unit.sprite);
}

function removeStageUnit(unit) {
    for (var i in stageUnitList) {
        if (stageUnitList[i] == unit) {
            stageUnitList.splice(i, 1);
        }
    }

    stage.removeChild(unit.sprite);
}

function drawMap() {
    var texture = PIXI.Texture.fromImage('./resource/map-tile.png');
    var tilingSprite = new PIXI.TilingSprite(texture, STAGE_WIDTH, STAGE_HEIGHT)
    stage.addChild(tilingSprite);
}

function drawGuideLine() {
    var graphics = new PIXI.Graphics();
    graphics.lineStyle(1, 0x000000, 0.1);

    for (var x = 1; x < TILE_COUNT_X; x++) {
        var posX = x * TILE_WIDTH;

        graphics.moveTo(posX, 0);
        graphics.lineTo(posX, STAGE_HEIGHT);
    };

    for (var y = 1; y < TILE_COUNT_Y; y++) {
        var posY = y * TILE_HEIGHT;

        graphics.moveTo(0, posY);
        graphics.lineTo(STAGE_WIDTH, posY);
    };

    stage.addChild(graphics);
}

function showInfo() {
    var txt = '';

    for (var id in unitPlayerList) {
        var unitPlayer = unitPlayerList[id];
        txt += unitPlayer.info.team + '팀\t|\t' + unitPlayer.info.name + '\t|\tHP:' + unitPlayer.info.hp + '\n';
    }

    $('#txt-info').val(txt);
}

function gameOver() {
    var teamHp = {
        1: 0,
        2: 0,
    }

    for (var id in unitPlayerList) {
        var unitPlayer = unitPlayerList[id];
        teamHp[unitPlayer.info.id] += unitPlayer.info.hp;
    }

    var msg = '';
    if (teamHp[1] > teamHp[2]) {
        msg = '1팀 승리!';
    } else if (teamHp[1] < teamHp[2]) {
        msg = '2팀 승리!';
    } else {
        msg = '비겼습니다.';
    }

    alert(msg);

    audioBgm.pause();
}

var logPlay = new (function () {
    var frameDelay = 20;
    var frameCount = frameDelay;

    this.frame = function () {
        if (logPos >= logList.length) {
            onFrame = null;
            gameOver();
            return;
        }

        var log = logList[logPos];

        if (!unitPlayerList[log.id]) {
            var unitPlayer = new UnitPlayer(log);
            addStageUnit(unitPlayer);

            unitPlayerList[log.id] = unitPlayer;

            return;
        }

        if (++frameCount < frameDelay) {
            return;
        }

        frameCount = 0;

        var unitPlayer = unitPlayerList[log.id];
        unitPlayer.changeInfo(log);

        ++logPos;

        showInfo();
    }
})();

var onFrame = null;

function animate() {
    renderer.render(stage);

    if (onFrame) {
        onFrame();
    }

    stageUnitList.forEach(function (unit) {
        unit.transition();
    });

    requestAnimFrame(animate);
}

$(function () {
    stage = new PIXI.Stage(0xFF00FF);
    renderer = PIXI.autoDetectRenderer(STAGE_WIDTH, STAGE_HEIGHT);
    document.getElementById('game-stage').appendChild(renderer.view);
    requestAnimFrame(animate);

    onFrame = logPlay.frame;

    drawMap();
    // drawGuideLine();

    audioBgm.play();
});


})();
</script>

</body>
</html>
