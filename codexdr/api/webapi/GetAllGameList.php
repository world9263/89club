<?php
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
header("Access-Control-Allow-Origin: " . $origin);
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
date_default_timezone_set('Asia/Kolkata');
$serviceNowTimeFormatted = date('Y-m-d H:i:s');

$jsonData = '{
    "data": {
        "popular": {
            "platformList": [
                {
                "gameID": "chicken-road",
                "gameNameEn": "Money Coming",
                "imgUrl": "https://wuttsghdijsbbsh.yrehdjsfiafkjgkjgfsasc.yachts/images/chicken-road.png",
                "vendorId": 18,
                "vendorCode": "chicken",
                "imgUrl2": null,
                "customGameType": 0
            }, {
                "gameID": "chicken-road-two",
                "gameNameEn": "Money Coming",
                "imgUrl": "https://wuttsghdijsbbsh.yrehdjsfiafkjgkjgfsasc.yachts/images/chicken-road-two.png",
                "vendorId": 18,
                "vendorCode": "chicken",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "22001",
                "gameNameEn": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/SPRIBE/aviator_20250210120506414.png",
                "vendorId": 18,
                "vendorCode": "SPRIBE",
                "imgUrl2": null,
                "customGameType": 0
            },{
    "gameID": "22002",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/102_20250210111705541.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22003",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/105_20250210113618898.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22004",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/103_20250210111953841.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22005",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/100.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22006",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/101_20250210111652965.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22007",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/106.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22009",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/107.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  },
  {
    "gameID": "22008",
    "gameNameEn": "Aviator-1Min",
    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/TB_Chess/104_20250210111939890.png",
    "vendorId": 23,
    "vendorCode": "SPRIBE",
    "imgUrl2": "",
    "customGameType": 0
  }, {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "lucky-mines",
        "gameNameEn": "luckymines",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_lucky-mines.png",
        "imgUrl2": null,
        "customGameType": 0
    }, 
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "squid-game",
        "gameNameEn": "squidgame",
        "imgUrl": "https://wuttsghdijsbbsh.yrehdjsfiafkjgkjgfsasc.yachts/images/squid-game.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "ballonix",
        "gameNameEn": "ballonix",
        "imgUrl": "https://wuttsghdijsbbsh.yrehdjsfiafkjgkjgfsasc.yachts/images/ballonix.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "new-hilo",
        "gameNameEn": "newhilo",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_new-hilo.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "bubbles",
        "gameNameEn": "bubbles",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_bubbles.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "plinko-aztec",
        "gameNameEn": "plinkoaztec",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_plinko-aztec.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "platform-mines",
        "gameNameEn": "platformmines",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_platform-mines.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "roulette",
        "gameNameEn": "roulette",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_roulette.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "chicken-road-two",
        "gameNameEn": "chickenroadtwo",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_chicken-road-two.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "keno",
        "gameNameEn": "keno",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_keno.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "penalty-unlimited",
        "gameNameEn": "penaltyunlimited",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_penalty-unlimited.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "triple",
        "gameNameEn": "triple",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_triple.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "crash",
        "gameNameEn": "crash",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_crash.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "cryptos",
        "gameNameEn": "cryptos",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_cryptos.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "diver",
        "gameNameEn": "diver",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_diver.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "new-double",
        "gameNameEn": "newdouble",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_new-double.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "sugar-daddy",
        "gameNameEn": "sugardaddy",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_sugar-daddy.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "robo-dice",
        "gameNameEn": "robodice",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_robo-dice.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "coinflip",
        "gameNameEn": "coinflip",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_coinflip.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "stairs",
        "gameNameEn": "stairs",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_stairs.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "hamster-run",
        "gameNameEn": "hamsterrun",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_hamster-run.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "forest-fortune-v1",
        "gameNameEn": "forestfortunev1",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_forest-arrow.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "goblin-tower",
        "gameNameEn": "goblintower",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_goblin-tower.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "joker-poker",
        "gameNameEn": "jokerpoker",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_joker-poker.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "aviafly",
        "gameNameEn": "aviafly",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_aviafly.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "tower",
        "gameNameEn": "tower",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_tower.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "wheel",
        "gameNameEn": "wheel",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_wheel.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "limbo",
        "gameNameEn": "limbo",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_limbo.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "jogo-do-bicho",
        "gameNameEn": "jogodobicho",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_jogo-do-bicho.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "hot-mines",
        "gameNameEn": "hotmines",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_hot-mines.png",
        "imgUrl2": null,
        "customGameType": 0
    },
    {
        "vendorId": "18",
        "vendorCode": "INOUT",
        "gameID": "plinko",
        "gameNameEn": "plinko",
        "imgUrl": "https:\/\/icons.inout.games\/408_544\/io_plinko.png",
        "imgUrl2": null,
        "customGameType": 0
    }
            ],
            "clicksTopList": [
                {
                "gameID": "14027",
                "gameNameEn": "Lucky Seven",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14027.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14045",
                "gameNameEn": "Super Niubi Deluxe",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14045.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14006",
                "gameNameEn": "Billionaire",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14006.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14036",
                "gameNameEn": "Super Niubi",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14036.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14025",
                "gameNameEn": "Lucky Racing",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14025.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14080",
                "gameNameEn": "Elemental Link Fire",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14080.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14070",
                "gameNameEn": "Book of Mystery",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14070.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14008",
                "gameNameEn": "Dragon Warrior",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14008.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14042",
                "gameNameEn": "Treasure Bowl",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14042.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14054",
                "gameNameEn": "Lucky Diamond",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14054.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8023",
                "gameNameEn": "Olympian Temple",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8023.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14034",
                "gameNameEn": "Go Lai Fu",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14034.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14055",
                "gameNameEn": "Kong",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14055.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14033",
                "gameNameEn": "Birds Party",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14033.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14041",
                "gameNameEn": "Mjolnir",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14041.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14065",
                "gameNameEn": "Blossom Of Wealth",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14065.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14035",
                "gameNameEn": "Dragons World",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14035.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14029",
                "gameNameEn": "Orient Animals",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14029.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14061",
                "gameNameEn": "Maya Gold Crazy",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14061.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14043",
                "gameNameEn": "Golden Disco",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14043.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14039",
                "gameNameEn": "Fortune Treasure",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14039.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14059",
                "gameNameEn": "Marvelous IV",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14059.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14038",
                "gameNameEn": "Egypt Treasure",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14038.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8048",
                "gameNameEn": "OpenSesame II",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8048.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14051",
                "gameNameEn": "Dragons Gate",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14051.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8020",
                "gameNameEn": "Open Sesame",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8020.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14075",
                "gameNameEn": "Fortune Neko",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14075.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14082",
                "gameNameEn": "Elemental Link Water",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14082.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14081",
                "gameNameEn": "Birds Party Deluxe",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14081.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8022",
                "gameNameEn": "MahJong",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8022.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14079",
                "gameNameEn": "Moneybags Man 2",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14079.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14063",
                "gameNameEn": "Big Three Dragons",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14063.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14060",
                "gameNameEn": "Lantern Wealth",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14060.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14077",
                "gameNameEn": "Trump Card",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14077.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "15005",
                "gameNameEn": "Lucky Fuwa",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/15005.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8051",
                "gameNameEn": "Xi Yang Yang",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8051.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14021",
                "gameNameEn": "Rolling In Money",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14021.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8050",
                "gameNameEn": "Fortune Horse",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8050.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8047",
                "gameNameEn": "Winning Mask II",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8047.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "15001",
                "gameNameEn": "Rooster In Love",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/15001.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14058",
                "gameNameEn": "Wonder Elephant",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14058.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14007",
                "gameNameEn": "One Punch Man",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14007.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14016",
                "gameNameEn": "Kingsman",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14016.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "15002",
                "gameNameEn": "Monkey King",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/15002.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8001",
                "gameNameEn": "Lucky Dragons",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8001.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "15012",
                "gameNameEn": "Legendary 5",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/15012.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14083",
                "gameNameEn": "CooCoo Farm",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14083.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14018",
                "gameNameEn": "DaJi",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14018.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14067",
                "gameNameEn": "Glamorous Girl",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14067.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14046",
                "gameNameEn": "Miner Babe",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14046.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8003",
                "gameNameEn": "Winning Mask",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8003.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14022",
                "gameNameEn": "Mining Upstart",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14022.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14030",
                "gameNameEn": "Triple King Kong",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14030.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8028",
                "gameNameEn": "Lucky Miner",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8028.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14052",
                "gameNameEn": "Jungle Jungle",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14052.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8017",
                "gameNameEn": "New Year",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8017.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14012",
                "gameNameEn": "Street Fighter",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14012.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8035",
                "gameNameEn": "Lucky Phoenix",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8035.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8015",
                "gameNameEn": "Moonlight Treasure",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8015.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "15010",
                "gameNameEn": "Chef Panda",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/15010.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8044",
                "gameNameEn": "Beauty And The Kingdom",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8044.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14068",
                "gameNameEn": "Prosperity Tiger",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14068.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14010",
                "gameNameEn": "Dragon",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14010.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14053",
                "gameNameEn": "Spindrift 2",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14053.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8006",
                "gameNameEn": "Formosa Bear",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8006.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8014",
                "gameNameEn": "Lucky Lion",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8014.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14040",
                "gameNameEn": "Pirate Treasure",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14040.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8007",
                "gameNameEn": "Lucky Qilin",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8007.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14048",
                "gameNameEn": "Double Wilds",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14048.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8019",
                "gameNameEn": "Four Treasures",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8019.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14050",
                "gameNameEn": "Spindrift",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14050.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8004",
                "gameNameEn": "Wu Kong",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8004.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14064",
                "gameNameEn": "Boom Fiesta",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14064.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8021",
                "gameNameEn": "Banana Saga",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8021.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8005",
                "gameNameEn": "Llama Adventure",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8005.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8002",
                "gameNameEn": "Flirting Scholar Tang",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8002.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8046",
                "gameNameEn": "Guan Gong",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8046.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14044",
                "gameNameEn": "Funky King Kong",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14044.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14003",
                "gameNameEn": "Panda Panda",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14003.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8049",
                "gameNameEn": "Flirting Scholar Tang II",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8049.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14005",
                "gameNameEn": "Mr. Bao",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14005.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "14047",
                "gameNameEn": "Moneybags Man",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/14047.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "8018",
                "gameNameEn": "Napoleon",
                "imgUrl": "https://ossimg.bdgadminbdg.com/IndiaBDG/gamelogo/JDB/8018.png",
                "vendorId": 23,
                "vendorCode": "JDB",
                "imgUrl2": null,
                "customGameType": 0
            }
            ],
            "clicksVideoTopList": [
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                    "gameCode": "SMG_titaniumLiveGamesAutoRoulette",
                    "gameNameEn": "Auto Roulette ",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGamesAutoRoulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                    "gameCode": "SMG_titaniumLiveGames_Roulette",
                    "gameNameEn": "Roulette ",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Roulette.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                    "gameCode": "SMG_titaniumLiveGames_Sicbo",
                    "gameNameEn": "Sicbo",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Sicbo.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "38",
                    "vendorCode": "MG_Video",
                    "gameCode": "SMG_titaniumLiveGames_Baccarat",
                    "gameNameEn": "Bonus Baccarat",
                    "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Baccarat.png",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    "gameCode": "CBAC",
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    "gameCode": "SBAC",
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    "gameCode": "NN",
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    "gameCode": "BJ",
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                },
                {
                    "vendorId": "10",
                    "vendorCode": "AG_Video",
                    "gameCode": "ZJH",
                    "gameNameEn": null,
                    "imgUrl": "",
                    "imgUrl2": "",
                    "winOdds": 0.0
                }
            ]
        },
        "sport": [
            
  {
    "slotsTypeID": 14,
    "slotsName": "Lucky Sports",
    "vendorId": 14,
    "gameCode": "vip_ak_cricket_sabasports",
    "vendorCode": "Lucky Sports",
    "state": 1,
    "vendorImg": "https://i.ibb.co/pjdr1Db2/51615615151516.png"
  },
  {
    "slotsTypeID": 16,
    "slotsName": "Lucky Sports",
    "vendorId": 14,
    "gameCode": "vip_ak_cricket_sabasports",
    "vendorCode": "Lucky Sports",
    "state": 1,
    "vendorImg": "https://i.ibb.co/mCx3gt9v/511115615151551565.png"
  },
  {
    "slotsTypeID": 15,
    "slotsName": "Lucky Sports",
    "vendorId": 14,
    "gameCode": "vip_ak_cricket_sabasports",
    "vendorCode": "Lucky Sports",
    "state": 1,
    "vendorImg": "https://i.ibb.co/N2DrC559/6551362071879e70233bff57-1.png"
  }
        ],
        "video": [
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/jPQPXVZK/111425000.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/zhZhj0Kt/3333333344545.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/23wsQ1fN/222222222.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/XxrF4G8y/155522224448555.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/CKbLXRwJ/144555222000.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/PvCZ0MsK/5646546454444465.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/JFqjS2Fr/515454545456545.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/C5qq5McJ/super6.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://i.ibb.co/kVf56PsJ/45645456465465.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Baccarat.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGamesAutoRoulette.png"
            },
            {
                "slotsTypeID": 38,
                "slotsName": "MG_Video",
                "gameCode": "GINKGO03",
                "vendorId": 38,
                "vendorCode": "MT",
                "state": 1,
                "vendorImg": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Video/SMG_titaniumLiveGames_Roulette.png"
            }
            
            
        ],
        "slot": [
           
            
             {
                "slotsTypeID": 41,
                "slotsName": "G9",
                "vendorId": 41,
                "vendorCode": "G9",
                "state": 1,
                "vendorImg": "https://ossimg.91admin123admin.com/91club/vendorlogo/vendorlogo_20250210101325vpri.png"
            },
            {
                "slotsTypeID": 6,
                "slotsName": "JDB",
                "vendorId": 6,
                "vendorCode": "JDB",
                "state": 1,
                "vendorImg": "https://i.ibb.co/Nd5F26tz/jdb.png"
            },
            {
                "slotsTypeID": 2,
                "slotsName": "CQ9",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "state": 1,
                "vendorImg": "https://i.ibb.co/NnTR8BRy/cq9.png"
            },
            {
                "slotsTypeID": 5,
                "slotsName": "INOUT",
                "vendorId": 5,
                "vendorCode": "PG",
                "state": 1,
                "vendorImg": "https://i.ibb.co/bgQ2Sz8t/inout-game.png"
            },
            {
                "slotsTypeID": 23,
                "slotsName": "SPRIBE",
                "vendorId": 23,
                "vendorCode": "SPRIBE",
                "state": 1,
                "vendorImg": "https://i.ibb.co/TMrwtyq1/aviator.png"
            }
        ],
        "chess": [
            {
                "slotsTypeID": 5,
                "slotsName": "INOUT",
                "vendorId": 5,
                "vendorCode": "PG",
                "state": 1,
                "vendorImg": "https://icons.inout.games/408_544/io_penalty-unlimited.png"
            },
            {
                "slotsTypeID": 5,
                "slotsName": "INOUT",
                "vendorId": 5,
                "vendorCode": "PG",
                "state": 1,
                "vendorImg": "https://wuttsghdijsbbsh.yrehdjsfiafkjgkjgfsasc.yachts/images/ballonix.png"
            },
            {
                "slotsTypeID": 5,
                "slotsName": "INOUT",
                "vendorId": 5,
                "vendorCode": "PG",
                "state": 1,
                "vendorImg": "https://icons.inout.games/408_544/io_forest-arrow.png"
            }
        ],
        "fish": [
            {
                "gameID": "AT05",
                "gameNameEn": "LuckyFishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDFuWaFishing.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "AT01",
                "gameNameEn": "OneShotFishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldBlastFishing.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "GO02",
                "gameNameEn": "herofishing",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldenFortuneFishing.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "29",
                "gameNameEn": "waterworld",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldenFuwaFishing.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                "gameID": "GB8",
                "gameNameEn": "dragonkoi",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/MG_Fish/SFG_WDGoldenTyrantFishing.png",
                "vendorId": 2,
                "vendorCode": "CQ9",
                "imgUrl2": "",
                "customGameType": 0
            },
            {
                    "gameID": "AT05",
                    "gameNameEn": "LuckyFishing",
                    "img": "https://ossimg.yuk87k786d.com/sikkim/gamelogo/CQ9/AT05.png",
                    "vendorId": 2,
                    "vendorCode": "CQ9",
                    "imgUrl2": null,
                    "customGameType": 0
                }
        ],
        "flash": [
              {
                "gameID": "slots-BlessingOfShiva",
                "gameNameEn": "Blessing of Shiva",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-BlessingOfShiva.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-AngryOlympusplus",
                "gameNameEn": "King Of The Gods",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-AngryOlympusplus.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "MG-RocketSpaceX",
                "gameNameEn": "Rocket SpaceX",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/MG-RocketSpaceX.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-777Diamonds",
                "gameNameEn": "Double Diamonds",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-777Diamonds.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "777res",
                "gameNameEn": "Fruit 777",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/777res.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-BengalTiger",
                "gameNameEn": "Bengal Gold",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-BengalTiger.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Ganesha",
                "gameNameEn": "Lucky elephant",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Ganesha.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-FortuneWild",
                "gameNameEn": "Fortune Wild",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-FortuneWild.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Phoenix",
                "gameNameEn": "Phoenix Legend",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Phoenix.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Athena",
                "gameNameEn": "Athena Gold",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Athena.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "GoldRushMaster",
                "gameNameEn": "Gold rush",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/GoldRushMaster.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "CardSlots",
                "gameNameEn": "Card slot",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/CardSlots.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-MajiongWays4",
                "gameNameEn": "Dragon God Mahjong",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-MajiongWays4.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Egypt-jewel",
                "gameNameEn": "Egypt Jewel Hunt",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Egypt-jewel.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Classic777",
                "gameNameEn": "Classic 777",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Classic777.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-MoneyTree",
                "gameNameEn": "Fortune Tree",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-MoneyTree.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-AgeofSteam",
                "gameNameEn": "Age of Steam",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-AgeofSteam.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Aladdin",
                "gameNameEn": "Aladdin lamp",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Aladdin.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-TempleOfZeus",
                "gameNameEn": "Temple of Zeus",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-TempleOfZeus.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-DragonsRivalry",
                "gameNameEn": "Dragons Rivalry",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-DragonsRivalry.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "SlotsGhostBride",
                "gameNameEn": "Ghost",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/SlotsGhostBride.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Cleopatra",
                "gameNameEn": "Cleopatras Gold",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Cleopatra.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-LockMary",
                "gameNameEn": "Fruit chain",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-LockMary.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-TycoonPig",
                "gameNameEn": "Piggy Bank",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-TycoonPig.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-India",
                "gameNameEn": "Riches of India",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-India.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Super777",
                "gameNameEn": "Regal 777",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Super777.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-BeerFestival",
                "gameNameEn": "Beer Fortune",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-BeerFestival.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-WitchyRiches",
                "gameNameEn": "Witchy Riches",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-WitchyRiches.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-RisingMedusa",
                "gameNameEn": "Rising Medusa",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-RisingMedusa.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-ProsperousTree",
                "gameNameEn": "Tree of Fortune",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-ProsperousTree.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Xiyouji",
                "gameNameEn": "Monkey King",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Xiyouji.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "SlotsCaiShen",
                "gameNameEn": "God Of Wealth",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/SlotsCaiShen.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-FuLuShou",
                "gameNameEn": "Fortune",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-FuLuShou.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Demoness",
                "gameNameEn": "Hot Devil",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Demoness.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-PenaltyShootout",
                "gameNameEn": "Penalty Shootout",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-PenaltyShootout.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-TigerWild",
                "gameNameEn": "Tiger Wild 3D",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-TigerWild.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-MoonlightWolves",
                "gameNameEn": "Moonlight Wolves ",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-MoonlightWolves.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Miner",
                "gameNameEn": "Old Miners Gold",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Miner.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-Samba",
                "gameNameEn": "Samba Mania",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-Samba.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-ThreeDragons",
                "gameNameEn": "Dragons",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-ThreeDragons.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-JurassicPark",
                "gameNameEn": "Jurassic World",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-JurassicPark_20250210163540867.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "SlotsYearsEve",
                "gameNameEn": "Imperial city",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/SlotsYearsEve.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-JurassicParknew",
                "gameNameEn": "Jurassic Park",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-JurassicParknew_20250210163551218.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slotZodiac",
                "gameNameEn": "12 zodiac",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slotZodiac.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-MexicanChilli",
                "gameNameEn": "Chilli Blaze",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-MexicanChilli.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-WuSong",
                "gameNameEn": "Wu Song",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-WuSong.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "SlotsShyLock",
                "gameNameEn": "Sherlock",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/SlotsShyLock.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-west-cowboy",
                "gameNameEn": "West World",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-west-cowboy.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "SuperFruitSlots",
                "gameNameEn": "Fruit Mary",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/SuperFruitSlots.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-VegasJourney",
                "gameNameEn": "Vegas Journey",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-VegasJourney.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-PussyCastle",
                "gameNameEn": "Pussy Castle",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-PussyCastle.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-WesternDuel",
                "gameNameEn": "Western Duel",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-WesternDuel.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-VegasTycoon",
                "gameNameEn": "Vegas Tycoon",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-VegasTycoon.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-CrazyRabbits",
                "gameNameEn": "Crazy Rabbits",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-CrazyRabbits_20250709143714580.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-SnakeFortune",
                "gameNameEn": "Snake Fortune",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-SnakeFortune_20250709143736076.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "slots-MadScienceMouse",
                "gameNameEn": "MScienceMouse",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/slots-MadScienceMouse_20250709143923761.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "MG-PandaCuisineMines",
                "gameNameEn": "Panda Restaurant",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/MG-PandaCuisineMines_20250709143859645.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "SL-ORNGNOR-SuperBull",
                "gameNameEn": "Super Bull",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/SL-ORNGNOR-SuperBull.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            },
            {
                "gameID": "MG-GrandmasterPandaMines",
                "gameNameEn": "Grandmaster Panda Mines",
                "img": "https://ossimg.91admin123admin.com/91club/gamelogo/G9/MG-GrandmasterPandaMines.png",
                "vendorId": 41,
                "vendorCode": "G9",
                "imgUrl2": null,
                "customGameType": 0
            }
        ],
        "lottery": [
            {
                "id": 1,
                "categoryCode": "Win Go",
                "categoryName": "WinGo彩票",
                "state": 1,
                "sort": 10,
                "categoryImg": "https://ossimg.91admin123admin.com/91club/lotterycategory/lotterycategory_202502101011154e3a.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null,
                "motoRaceAmount": null,
                "videoWinGoAmount": null,
                "gameCode": "WinGo_30S"
            },
            {
                "id": 2,
                "categoryCode": "K3",
                "categoryName": "K3彩票",
                "state": 1,
                "sort": 8,
                "categoryImg": "https://ossimg.91admin123admin.com/91club/lotterycategory/lotterycategory_20250210101053ntrf.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null,
                "motoRaceAmount": null,
                "videoWinGoAmount": null,
                "gameCode": "K3_1M"
            },
            {
                "id": 3,
                "categoryCode": "5D",
                "categoryName": "5D彩票",
                "state": 1,
                "sort": 1,
                "categoryImg": "https://ossimg.91admin123admin.com/91club/lotterycategory/lotterycategory_20250210101042iwui.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null,
                "motoRaceAmount": null,
                "videoWinGoAmount": null,
                "gameCode": "D5_1M"
            },
            {
                "id": 4,
                "categoryCode": "Trx Win Go",
                "categoryName": "TrxWinGo彩票",
                "state": 1,
                "sort": 0,
                "categoryImg": "https://ossimg.91admin123admin.com/91club/lotterycategory/lotterycategory_20250210101104jtse.png",
                "wingoAmount": null,
                "k3Amount": null,
                "fiveDAmount": null,
                "trxWingoAmount": null,
                "motoRaceAmount": null,
                "videoWinGoAmount": null,
                "gameCode": "TrxWinGo_1M"
            }
        ],
        "awardRecordList": [
            {
                "orderId": 9718293,
                "userId": 12605270,
                "userPhoto": "1",
                "userName": "918853451860",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 20.00,
                "bonusAmount": 100.00,
                "multipleName": "20-29",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718292,
                "userId": 1276302,
                "userPhoto": "1",
                "userName": "919401169794",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 50.00,
                "bonusAmount": 300.00,
                "multipleName": "40-59",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718291,
                "userId": 169929,
                "userPhoto": "4",
                "userName": "919813473272",
                "gameName": "Fortune Gems",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/109.png",
                "imgUrl2": "",
                "multiple": 10.67,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718290,
                "userId": 5160891,
                "userPhoto": "5",
                "userName": "917503916092",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 41.00,
                "bonusAmount": 300.00,
                "multipleName": "40-59",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718289,
                "userId": 3864680,
                "userPhoto": "1",
                "userName": "916351724933",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 30.00,
                "bonusAmount": 200.00,
                "multipleName": "30-39",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718288,
                "userId": 12985588,
                "userPhoto": "1",
                "userName": "918088072814",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 10.00,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718287,
                "userId": 9552754,
                "userPhoto": "7",
                "userName": "919429848853",
                "gameName": "Crazy777",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/35.png",
                "imgUrl2": "",
                "multiple": 36.67,
                "bonusAmount": 200.00,
                "multipleName": "30-39",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718286,
                "userId": 9841016,
                "userPhoto": "6",
                "userName": "918690536005",
                "gameName": "Royal Fishing",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/1.png",
                "imgUrl2": "",
                "multiple": 11.67,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718285,
                "userId": 13598097,
                "userPhoto": "1",
                "userName": "918953816512",
                "gameName": "Fortune Gems",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/109.png",
                "imgUrl2": "",
                "multiple": 26.67,
                "bonusAmount": 100.00,
                "multipleName": "20-29",
                "createTime": "2025-02-18 13:39:01"
            },
            {
                "orderId": 9718284,
                "userId": 12605270,
                "userPhoto": "1",
                "userName": "918853451860",
                "gameName": "Money Coming",
                "imgUrl": "https://ossimg.91admin123admin.com/91club/gamelogo/JILI/51.png",
                "imgUrl2": "",
                "multiple": 11.00,
                "bonusAmount": 50.00,
                "multipleName": "10-19",
                "createTime": "2025-02-18 13:39:01"
            }
        ]
    },
    "code": 0,
    "msg": "Succeed",
    "msgCode": 0,
    "serviceNowTime": "' . $serviceNowTimeFormatted . '"
}';

$data = json_decode($jsonData, true);

$response = json_encode($data, JSON_PRETTY_PRINT);

header('Content-Type: application/json');
echo $response;

?>
