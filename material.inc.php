<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * kingsguild implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * kingsguild game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


$this->tiles = array(
    // 1 => array( "name" => "room", "size" => array(118,118), "x_spaces" => array(18,18,18,85,30), "start" => array(58,61),"y_spaces" => null ),
    1 => array( "name" => "room", "size" => array(126,126), "x_spaces" => array(21,21,21,92,30), "start" => array(64,66),"y_spaces" => null ),
    2 => array( "name" => "specialist", "size" => array(100,100), "x_spaces" => array(16.5,16.5,16.5,16.5), "start" => array(64,224), "y_spaces" => array(15) ),
    3 => array( "name" => "quest", "size" => array(126,174), "x_spaces" => array(16.5,16.5), "start" => array(722,256), "y_spaces" => array(28,28,28) ),
    4 => array( "name" => "treasure", "size" => array(90,128), "x_spaces" => array(19,19), "start" => array(826.5,686), "y_spaces" => null ),
    5 => array( "name" => "advresource", "size" => array(172,180), "x_spaces" => array(30), "start" => array(64,458), "y_spaces" => null ),
    6 => array( "name" => "baseresource", "size" => array(172,172), "x_spaces" => array(30,30,10), "start" => array(64,656), "y_spaces" => null ),
);

$this->playertiles = array(
    // 1 => array( "name" => "room", "size" => array(118,118), "x_spaces" => array(18,18,18,85,30), "start" => array(58,61),"y_spaces" => null ),
    1 => array( "name" => "specialist", "size" => array(135,135), "x_spaces" => array(21,20,20), "start" => array(102,-60),"y_spaces" => null ),
    2 => array( "name" => "room", "size" => array(165,165), "x_spaces" => array(6,6,6), "start" => array(66,100), "y_spaces" => array(array(4,4,4,4),array(4,4,4,4))),
);

$this->rooms = array(
    1 => array(
        "name" => "Warehouse",
        "cathegory" => "basic",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 6,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",3)),
        "position" => 0,
        "nameTr" => clienttranslate("Warehouse"),
        "text" => '',
    ),

    2 => array(
        "name" => "Library",
        "cathegory" => "basic",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 4,
        "point_value" => 0,
        "end_points" => array( "scrollCount" => 3 ),
        "ability" => array( "handsize" => array("handsize",3)),
        "position" => 3,
        "nameTr" => clienttranslate("Library"),
        "text" => array( 'log' => clienttranslate('+3 hand size ${newline} ${points} per Scroll'), 'args' => array( 'newline' => 'newline', 'points' => 'points_3') )
    ),

    3 => array(
        "name" => "Gem Workshop",
        "cathegory" => "basic",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 5,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "cangather" => array("gem")),
        "position" => 1,
        "nameTr" => clienttranslate("Gem Workshop"),
        "text" => array( 'log' => clienttranslate('You may gather ${gems}'), 'args' => array( 'gems' => 'gems_gemlog') )
    ),

    4 => array(
        "name" => "Magic Arcaenum",
        "cathegory" => "basic",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 5,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "cangather" => array("magic")),
        "position" => 2,
        "nameTr" => clienttranslate("Magic Arcaenum"),
        "text" => array( 'log' => clienttranslate('You may gather ${magic}'), 'args' => array( 'magic' => 'magic_magiclog') )
    ),

    5 => array(
        "name" => "Bedchamber",
        "cathegory" => "basic",
        "doubleroom" => false,
        "two_sided" => array(5,6),
        "value" => 8,
        "point_value" => 0,
        "end_points" => array( "occupied" => 7 ),
        "ability" => array( "tile" => array("specialist",1)),
        "position" => 4,
        "nameTr" => clienttranslate("Bedchamber"),
        "text" => '',
    ),

    6 => array(
        "name" => "Kitchen",
        "cathegory" => "basic",
        "doubleroom" => false,
        "two_sided" => array(5,6),
        "value" => 4,
        "point_value" => 0,
        "end_points" => array( "occupied" => 3 ),
        "ability" => array( "tile" => array("specialist",1)),
        "position" => 4,
        "nameTr" => clienttranslate("Kitchen"),
        "text" => '',
    ),

    7 => array(
        "name" => "Great Hall",
        "cathegory" => "basic",
        "doubleroom" => true,
        "two_sided" => array(7,8),
        "value" => 12,
        "point_value" => 0,
        "end_points" => array( "occupied" => 11 ),
        "ability" => array( "tile" => array("specialist",2)),
        "position" => 5,
        "nameTr" => clienttranslate("Great Hall"),
        "text" => '',
    ),

    8 => array(
        "name" => "Barracks",
        "cathegory" => "basic",
        "doubleroom" => true,
        "two_sided" => array(7,8),
        "value" => 6,
        "point_value" => 0,
        "end_points" => array( "occupied" => 5 ),
        "ability" => array( "tile" => array("specialist",2)),
        "position" => 5,
        "nameTr" => clienttranslate("Barracks"),
        "text" => '',
    ),

    9 => array(
        "name" => "Vault of Riches",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 14,
        "point_value" => 0,
        "end_points" => array( "goldvalue" => '' ),
        "ability" => null,
        "nameTr" => clienttranslate("Vault of Riches"),
        "text" => array( 'log' => clienttranslate('${points} per ${gold}'), 'args' => array( 'points' => 'points_1', 'gold' => 'gold_1') )
    ),

    10 => array(
        "name" => "Throne Room",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 16,
        "point_value" => 9,
        "end_points" => array( "nextToStatue" => 6 ),
        "ability" => null,
        "nameTr" => clienttranslate("Throne Room"),
        "text" => array( 'log' => clienttranslate('${points} if next to the King\'s Statue'), 'args' => array( 'points' => 'points_6' ) )
    ),

    11 => array(
        "name" => "Tavern",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 19,
        "point_value" => 0,
        "end_points" => array( "specialistCount" => 3 ),
        "ability" => null,
        "nameTr" => clienttranslate("Tavern"),
        "text" => array( 'log' => clienttranslate('${points} per ${specialist}'), 'args' => array( 'points' => 'points_3', 'specialist' => 'specialist') )
    ),

    12 => array(
        "name" => "Hall of Wisdom",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 17,
        "point_value" => 0,
        "end_points" => array( "setOfHeroes" => array('mage', 'warrior', 6) ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Wisdom"),
        "text" => array( 'log' => clienttranslate('${points} per set of ${hero1} ${hero2}'), 'args' => array( 'points' => 'points_6', 'hero1' => 'hero_mage', 'hero2' => 'hero_warrior') )
    ),

    13 => array(
        "name" => "Hall of Warriors",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 18,
        "point_value" => 0,
        "end_points" => array( "heroCount" => array('warrior', 4) ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Warriors"),
        "text" => array( 'log' => clienttranslate('${points} per ${hero}'), 'args' => array( 'points' => 'points_4', 'hero' => 'hero_warrior') )
    ),

    14 => array(
        "name" => "Hall of Strength",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 17,
        "point_value" => 0,
        "end_points" => array( "setOfHeroes" => array('rogue', 'warrior', 6) ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Strength"),
        "text" => array( 'log' => clienttranslate('${points} per set of ${hero1} ${hero2}'), 'args' => array( 'points' => 'points_6', 'hero1' => 'hero_herorogue', 'hero2' => 'hero_warrior') )
    ),

    15 => array(
        "name" => "Hall of Rogues",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 18,
        "point_value" => 0,
        "end_points" => array( "heroCount" => array('rogue', 4) ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Rogues"),
        "text" => array( 'log' => clienttranslate('${points} per ${hero}'), 'args' => array( 'points' => 'points_4', 'hero' => 'hero_rogue') )
    ),

    16 => array(
        "name" => "Hall of Mages",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 18,
        "point_value" => 0,
        "end_points" => array( "heroCount" => array('mage', 4) ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Mages"),
        "text" => array( 'log' => clienttranslate('${points} per ${hero}'), 'args' => array( 'points' => 'points_4', 'hero' => 'hero_mage') )
    ),

    17 => array(
        "name" => "Hall of Cunning",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 17,
        "point_value" => 0,
        "end_points" => array( "setOfHeroes" => array('mage', 'rogue', 6) ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Cunning"),
        "text" => array( 'log' => clienttranslate('${points} per set of ${hero1} ${hero2}'), 'args' => array( 'points' => 'points_6', 'hero1' => 'hero_rogue', 'hero2' => 'hero_mage') )
    ),

    18 => array(
        "name" => "Hall of Builders",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 19,
        "point_value" => 0,
        "end_points" => array( "roomCount" => 3 ),
        "ability" => null,
        "nameTr" => clienttranslate("Hall of Builders"),
        "text" => array( 'log' => clienttranslate('${points} per room'), 'args' => array( 'points' => 'points_3' ) )
    ),

    19 => array(
        "name" => "Gallery",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 14,
        "point_value" => 0,
        "end_points" => array( "setOfItems" => array('Weapon', 'Armor', 3) ),
        "ability" => null,
        "nameTr" => clienttranslate("Gallery"),
        "text" => array( 'log' => clienttranslate('${points} per set of ${weapon} ${armor}'), 'args' => array( 'points' => 'points_3', 'weapon' => 'weapon_weapon', 'armor' => 'armor_armor') )
    ),

    20 => array(
        "name" => "Depot",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 16,
        "point_value" => 0,
        "end_points" => array( "resourcesType" => 4 ),
        "ability" => null,
        "nameTr" => clienttranslate("Depot"),
        "text" => array( 'log' => clienttranslate('${points} per type of  ${resource}'), 'args' => array( 'points' => 'points_4', 'resource' => 'resource_plain') )
    ),

    21 => array(
        "name" => "Arsenal",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 15,
        "point_value" => 0,
        "end_points" => array( "itemCount" => array('Weapon', 2) ),
        "ability" => null,
        "nameTr" => clienttranslate("Arsenal"),
        "text" => array( 'log' => clienttranslate('${points} per ${weapon}'), 'args' => array( 'points' => 'points_2', 'weapon' => 'weapon_weapon') )
    ),

    22 => array(
        "name" => "Armory",
        "cathegory" => "master",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 15,
        "point_value" => 0,
        "end_points" => array( "itemCount" => array('Armor', 2) ),
        "ability" => null,
        "nameTr" => clienttranslate("Armory"),
        "text" => array( 'log' => clienttranslate('${points} per ${armor}'), 'args' => array( 'points' => 'points_2', 'armor' => 'armor_armor') )
    ),

    23 => array(
        "name" => "Shrine",
        "cathegory" => "guild_special",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 0,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "tile" => array("specialist",1)),
        "nameTr" => clienttranslate("Shrine"),
        "text" => '',
    ),

    24 => array(
        "name" => "King's Statue",
        "cathegory" => "special",
        "doubleroom" => false,
        "two_sided" => false,
        "value" => 0,
        "point_value" => 4,
        "end_points" => null,
        "ability" => null,
        "nameTr" => clienttranslate("King's Statue"),
        "text" => '',
    ),

    25 => array(
        "name" => "Craft Collective",
        "cathegory" => "guild_hall",
        "doubleroom" => true,
        "two_sided" => false,
        "value" => 0,
        "point_value" => null,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",6)),
        "nameTr" => clienttranslate("Craft Collective"),
        "text" => clienttranslate("Start with +1 storage"),
    ),

    26 => array(
        "name" => "Explorers League",
        "cathegory" => "guild_hall",
        "doubleroom" => true,
        "two_sided" => false,
        "value" => 0,
        "point_value" => null,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",5)),
        "nameTr" => clienttranslate("Explorers League"),
        "text" => array( 'log' => clienttranslate('Start with ${resource1}${resource1} or ${resource1}${resource2} or ${resource2}${resource2}'), 
                        'args'  => array( 'resource1' => 'resource_cloth', 'resource2' => 'resource_leather') )
    ),

    27 => array(
        "name" => "Greycastle Guard",
        "cathegory" => "guild_hall",
        "doubleroom" => true,
        "two_sided" => false,
        "value" => 0,
        "point_value" => null,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",5)),
        "nameTr" => clienttranslate("Greycastle Guard"),
        "text" => array( 'log' => clienttranslate('Start with ${resource1}${resource1} or ${resource1}${resource2} or ${resource2}${resource2}'), 
                        'args'  => array( 'resource1' => 'resource_iron', 'resource2' => 'resource_wood') )
    ),

    28 => array(
        "name" => "Mercantile Trust",
        "cathegory" => "guild_hall",
        "doubleroom" => true,
        "two_sided" => false,
        "value" => 0,
        "point_value" => null,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",5)),
        "nameTr" => clienttranslate("Mercantile Trust"),
        "text" => array( 'log' => clienttranslate('Start with an additional ${gold}'), 'args'  => array( 'gold' => 'gold_4') )
    ),

    29 => array(
        "name" => "Starfall Syndicate",
        "cathegory" => "guild_hall",
        "doubleroom" => true,
        "two_sided" => false,
        "value" => 0,
        "point_value" => null,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",5)),
        "nameTr" => clienttranslate("Starfall Syndicate"),
        "text" => array( 'log' => clienttranslate('Start with ${treasure1} or ${treasure2} or ${treasure3} '), 'args'  => array( 'treasure1' => 'treasure_blue', 'treasure2' => 'treasure_red', 'treasure3' => 'treasure_yellow') )
    ),

    30 => array(
        "name" => "The Holy Order",
        "cathegory" => "guild_hall",
        "doubleroom" => true,
        "two_sided" => false,
        "value" => 0,
        "point_value" => null,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",5)),
        "nameTr" => clienttranslate("The Holy Order"),
        "text" => clienttranslate("Starts with the Shrine room"),
    ),
);


$this->specialist = array(
    1 => array(
        "name" => "Appraiser",
        "cathegory" => "A",
        "value" => 6,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "aftertreasuregain" => array("appraisertakeTreasure") ),        //done
        "nameTr" => clienttranslate("Appraiser"),
        "text" => array( 'log' => clienttranslate('After you receive your treasure(s) from a quest, draw ${treasure} then discard a ${treasure}'), 
                        'args'  => array( 'treasure' => 'treasure_plain') )
    ),

    2 => array(
        "name" => "Builder",
        "cathegory" => "A",
        "value" => 5,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "expandbonus" => array("doublebonus","buildanywhere")),  //done
        "nameTr" => clienttranslate("Builder"),
        "text" => clienttranslate('Double your build bonuses. You may build anywhere.'), 
    ),

    3 => array(
        "name" => "Courrier",
        "cathegory" => "A",
        "value" => 3,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",1)), //done
        "nameTr" => clienttranslate("Courier"),
        "text" => '', 
        
    ),

    4 => array(
        "name" => "Inventor",
        "cathegory" => "A",
        "value" => 3,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "handsize" => array("handsize",2)), //done
        "nameTr" => clienttranslate("Inventor"),
        "text" => clienttranslate('+2 hand size'), 
    ),

    5 => array(
        "name" => "Laborer",
        "cathegory" => "A",
        "value" => 6,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "cangather" => array("any",3)), //done
        "nameTr" => clienttranslate("Laborer"),
        "text" => array( 'log' => clienttranslate('${resource} ${resource} ${resource} ${newline} (Gather any 3 resources.)'), 
                        'args'  => array( 'resource' => 'resource_plain', 'newline' => 'newline') )
    ),

    6 => array(
        "name" => "Lumberjack",
        "cathegory" => "A",
        "value" => 4,
        "point_value" => 1,
        "end_points" => null,
        "ability"  => array( "gatherBonus" => array("wood",1)), //done
        "nameTr" => clienttranslate("Lumberjack"),
        "text" => clienttranslate('Gain one wood for free.'), 
    ),

    7 => array(
        "name" => "Miner",
        "cathegory" => "A",
        "value" => 4,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "gatherBonus" => array("iron",1)), //done
        "nameTr" => clienttranslate("Miner"),
        "text" => clienttranslate('Gain one iron for free.'), 
    ),

    8 => array(
        "name" => "Oracle",
        "cathegory" => "A",
        "value" => 3,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "permanentaction" => array("questdeck",2)),     //done
        "nameTr" => clienttranslate("Oracle"),
        "text" => clienttranslate('You may look at the top 2 cards of the quest deck.'),
    ),

    9 => array(
        "name" => "Recruiter",
        "cathegory" => "A",
        "value" => 3,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "expandbonus" => array("discount")), //done
        "nameTr" => clienttranslate("Recruiter"),
        "text" => clienttranslate('Double hiring discounts.'),
    ),

    10 => array(
        "name" => "Tanner",
        "cathegory" => "A",
        "value" => 4,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "gatherBonus" => array("leather",1)), //done
        "nameTr" => clienttranslate("Tanner"),
        "text" => clienttranslate('Gain one leather for free.'),
    ),

    11 => array(
        "name" => "Thug",
        "cathegory" => "A",
        "value" => 5,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "craftaction" => array("thugaction") ),     //done
        "nameTr" => clienttranslate("Thug"),
        "text" => array( 'log' => clienttranslate('Move the thug token onto a quest. Others must pay you ${gold} to craft on that quest.'), 
                        'args'  => array( 'gold' => 'gold_2',) )
    ),

    12 => array(
        "name" => "Weaver",
        "cathegory" => "A",
        "value" => 4,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "gatherBonus" => array("cloth",1)), //done
        "nameTr" => clienttranslate("Weaver"),
        "text" => clienttranslate('Gain one cloth for free.')
    ),

    13 => array(
        "name" => "Auctioneer",
        "cathegory" => "B",
        "value" => 4,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "permanentaction" => array("sell",4)),              //done
        "nameTr" => clienttranslate("Auctioneer"),
        "text" => array( 'log' => clienttranslate('You may sell Relics, Charms, & Scrolls for ${gold} each.'), 
                            'args'  => array( 'gold' => 'gold_4',) )
    ),

    14 => array(
        "name" => "Bard",
        "cathegory" => "B",
        "value" => 4,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "permanentaction" => array("movespecialist")),          //done
        "nameTr" => clienttranslate("Bard"),
        "text" => clienttranslate('Move specialists for free.')
    ),

    15 => array(
        "name" => "Blacksmith",
        "cathegory" => "B",
        "value" => 5,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "craftbonus" => array("Weapon",'iron')),                //done
        "nameTr" => clienttranslate("Blacksmith"),
        "text" => clienttranslate('Reduce the cost of weapons.')
    ),

    16 => array(
        "name" => "Fighter",
        "cathegory" => "B",
        "value" => 6,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "craftaction" => array("goldfortreasure", "warrior",'red') ),   //done
        "nameTr" => clienttranslate("Fighter"),
        "text" => clienttranslate('Pay to gain a red treasure.')
    ),

    17 => array(
        "name" => "Leatherworker",
        "cathegory" => "B",
        "value" => 5,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "craftbonus" => array("Armor",'leather')),              //done
        "nameTr" => clienttranslate("Leatherworker"),
        "text" => clienttranslate('Reduce the cost of armor.')
    ),

    18 => array(
        "name" => "Peasant",
        "cathegory" => "B",
        "value" => 4,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "tile" => array("storage",2)), //done
        "nameTr" => clienttranslate("Peasant"),
        "text" => ''
    ),

    19 => array(
        "name" => "Ranger",
        "cathegory" => "B",
        "value" => 6,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "craftaction" => array("goldfortreasure", "rogue", 'yellow')),  //done
        "nameTr" => clienttranslate("Ranger"),
        "text" => clienttranslate('Pay to gain a yellow treasure.')
    ),

    20 => array(
        "name" => "Sorceress",
        "cathegory" => "B",
        "value" => 7,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "cangather" => array("magic", "gem")), //done
        "nameTr" => clienttranslate("Sorceress"),
        "text" => array( 'log' => clienttranslate('You maygather ${gems} and ${magic}.'), 
                            'args'  => array( 'gems' => 'gems_gemlog', 'magic' => 'magic_magiclog',) )
    ),

    21 => array(
        "name" => "Tailor",
        "cathegory" => "B",
        "value" => 5,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "craftbonus" => array("Armor",'cloth')),                //done
        "nameTr" => clienttranslate("Tailor"),
        "text" => clienttranslate('Reduce the cost of armor.')
    ),

    22 => array(
        "name" => "Warlock",
        "cathegory" => "B",
        "value" => 7,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "craftaction" => array("destroyscroll")),               //done   
        "nameTr" => clienttranslate("Warlock"),
        "text" => clienttranslate('Destroy a Scroll to take both effects for yourself.')
    ),

    23 => array(
        "name" => "Wizard",
        "cathegory" => "B",
        "value" => 6,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "craftaction" => array("goldfortreasure", "mage", 'blue')),   //done
        "nameTr" => clienttranslate("Wizard"),
        "text" => clienttranslate('Pay to gain a blue treasure.')
    ),

    24 => array(
        "name" => "Woodworker",
        "cathegory" => "B",
        "value" => 5,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "craftbonus" => array("Weapon",'wood')),                //done
        "nameTr" => clienttranslate("Woodworker"),
        "text" => clienttranslate('Reduce the cost of weapons.')
    ),

    25 => array(
        "name" => "Adventurer",
        "cathegory" => "C",
        "value" => 9,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "onetimebonus" => array('blue', 'red','yellow')), //done
        "nameTr" => clienttranslate("Adventurer"),
        "text" => clienttranslate('Draw a treasure of each color.')
    ),

    26 => array(
        "name" => "Alchemist",
        "cathegory" => "C",
        "value" => 5,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("traderesources")),  //done
        "nameTr" => clienttranslate("Alchemist"),
        "text" => clienttranslate('Transmute goods to any types.')
    ),

    27 => array(
        "name" => "Aristocrat",
        "cathegory" => "C",
        "value" => 9,
        "point_value" => 6,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("placeBaggage")), //done
        "nameTr" => clienttranslate("Aristocrat"),
        "text" => clienttranslate('Place the baggage token in a chamber space.')
    ),

    28 => array(
        "name" => "Craftswoman", 
        "cathegory" => "C",
        "value" => 7,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("craft")),     //done
        "nameTr" => clienttranslate("Craftswoman"),
        "text" => clienttranslate('Take a craft action.')
    ),

    29 => array(
        "name" => "Curator",
        "cathegory" => "C",
        "value" => 5,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("buydiscardrelics",3)),  //done
        "nameTr" => clienttranslate("Curator"),
        "text" => array( 'log' => clienttranslate('Buy any number of Relics in the discard pile for ${gold} each.'), 
                            'args'  => array( 'gold' => 'gold_3') )
    ),

    30 => array(
        "name" => "Dealer",
        "cathegory" => "C",
        "value" => 5,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("gather")),                  //done
        "nameTr" => clienttranslate("Dealer"),
        "text" => clienttranslate('Take a gather action.')
    ),

    31 => array(
        "name" => "Merchant",
        "cathegory" => "C",
        "value" => 7,
        "point_value" => 4,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("take3give3") ),           //done
        "nameTr" => clienttranslate("Merchant"),
        "text" => clienttranslate('Draw & discard 3 treasures.')
    ),

    32 => array(
        "name" => "Minstrel",
        "cathegory" => "C",
        "value" => 6,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("takeTreasure", "action_buttons" =>  array('blue','red', 'yellow'))), //done
        "nameTr" => clienttranslate("Minstrel"),
        "text" => clienttranslate('Draw a treasure of any color.')
    ),

    33 => array(
        "name" => "Overseer",
        "cathegory" => "C",
        "value" => 8,
        "point_value" => 3,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("placeSpecialist")), //done
        "nameTr" => clienttranslate("Overseer"),
        "text" => clienttranslate('Hire the oldest specialist for free. (The one on the far right.)')
    ),

    34 => array(
        "name" => "Smuggler",
        "cathegory" => "C",
        "value" => 6,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("steal")), //done
        "nameTr" => clienttranslate("Smuggler"),
        "text" => array( 'log' => clienttranslate('Take on ${resource} from an opponent.'), 
                        'args'  => array( 'resource' => 'resource_plain') )
    ),

    35 => array(
        "name" => "Vizier",
        "cathegory" => "C",
        "value" => 6,
        "point_value" => 1,
        "end_points" => null,
        "ability" => array( "automatic" => array("offering")),
        "nameTr" => clienttranslate("Vizier"),
        "text" => clienttranslate('You don’t need to spend an action to make an offering to the council.')
    ),

    36 => array(
        "name" => "Witch",
        "cathegory" => "C",
        "value" => 8,
        "point_value" => 2,
        "end_points" => null,
        "ability" => array( "onetimeaction" => array("select_questcard")),                  //done
        "nameTr" => clienttranslate("Witch"),
        "text" => array( 'log' => clienttranslate('Take a single-item quest from the board. (Don’t gain ${gold} or ${treasure} for it.)'), 
                        'args'  => array( 'gold' => 'gold_', 'treasure' => 'treasure_plain') )
    ),

    37 => array(
        "name" => "baggage",
        "cathegory" => "baggage",
        "value" => 0,
        "point_value" => 0,
        "end_points" => null,
        "ability" => array( ),
        "nameTr" => '',
        "text" => '',
    ),

);

$this->treasures = array(
    1 => array(
        "name" => "Treasure Map",
        "color" => "blue",
        "cathegory" => null,
        "count" => 2,
        "effect" =>  array( "discardTreasure" => array( array("blue", "blue"),  array("red", "red"), array("yellow", "yellow"),     //done
                                                            array("blue", "red"),  array("blue", "yellow"), array("red", "yellow"),
                                    )), 
        "sellcost" => 2,
        "nameTr" => clienttranslate('Treasure Map'),
        "text" => array( 'log' => clienttranslate('Discard this and another ${treasure} to draw ${treasure} ${treasure} of any colors.'), 
                        'args' => array('treasure' => 'treasure_plain' ) )
    ),

    2 => array(
        "name" => "Spoiling Potion",
        "color" => "blue",
        "cathegory" => null,
        "count" => 2,
        "effect" => array( "drop" => array( array('iron'),array('wood'), array('leather'), array('cloth'), array('gem'), array('magic') )),     //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Spoiling Potion'),
        "text" => array( 'log' => clienttranslate('Choose a type of ${resource}. All players lose one ${resource} of that type.'), 
                        'args' => array('resource' => 'resource_plain' ) )
    ),

    3 => array(
        "name" => "Secret Stash",
        "color" => "blue",
        "cathegory" => null,
        "count" => 1,
        "effect" => array( "gain" => array("cloth", "leather", "iron", "wood")),                        //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Secret Stash'),
        "text" => array( 'log' => clienttranslate('Gain ${resource1}${resource2}${resource3}${resource4}.'), 
                        'args' => array('resource1' => 'resource_iron','resource2' => 'resource_wood','resource3' => 'resource_leather','resource4' => 'resource_cloth', ) )
    ),

    4 => array(
        "name" => "Scroll of Plenty",
        "color" => "blue",
        "cathegory" => clienttranslate("Scroll"),
        "count" => 6,                                         
        "effect" => array( "gain2resource" => array( array( array('iron','iron'), array('wood','wood'),          //done
                                                            array('leather','leather'), array('cloth','cloth'), 
                                                            array('gem','gem'), array('magic','magic') ),  
                                                            array('iron','wood', 'leather', 'cloth', 'gem', 'magic') ), ),
        "sellcost" => 2,
        "nameTr" => clienttranslate('Scroll of Plenty'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource} of the same type. The next player gain ${resource}.'), 
                        'args' => array('resource' => 'resource_plain' ) )
    ),

    5 => array(
        "name" => "Oak Branches",
        "color" => "blue",
        "cathegory" => null,                                 
        "count" => 1,
        "effect" => array( "gain" => array("wood", "wood", "wood")),                            //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Oak Branches'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource}${resource}'), 
                        'args' => array('resource' => 'resource_wood' ) )
    ),

    6 => array(
        "name" => "Mithril Bars",
        "color" => "blue",
        "cathegory" => null,
        "count" => 1,
        "effect" => array( "gain" => array("iron", "iron", "iron")),                            //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Mithril Bars'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource}${resource}'), 
                        'args' => array('resource' => 'resource_iron' ) )
    ),

    7 => array(
        "name" => "Dragon Hides",
        "color" => "blue",
        "cathegory" => null,
        "count" => 1,
        "effect" => array( "gain" => array("leather", "leather", "leather")),               //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Dragon Hides'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource}${resource}'), 
                        'args' => array('resource' => 'resource_leather' ) )
    ),

    8 => array(
        "name" => "Contract",
        "color" => "blue",
        "cathegory" => null,
        "count" => 2,
        "effect" => array( "hireSpecialist" => ''),                                      //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Contract'),
        "text" => array( 'log' => clienttranslate('Hire a specialist. (You still pay the ${gold} cost.)'), 
                        'args' => array('gold' => 'gold_' ) )
    ),

    9 => array(
        "name" => "Brilliant Diamonds",
        "color" => "blue",
        "cathegory" => null,
        "count" => 2,
        "effect" => array( "gain" => array("gem", "gem")),                  //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Brilliant Diamonds'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource}'), 
                        'args' => array('resource' => 'resource_gem' ) )
    ),

    10 => array(
        "name" => "Bottle of Faerie Dust",
        "color" => "blue",
        "cathegory" => null,
        "count" => 2,
        "effect" => array( "gain" => array("magic", "magic")),                      //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Bottle of Faerie Dust'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource}'), 
                        'args' => array('resource' => 'resource_magic' ) )
    ),

    11 => array(
        "name" => "Bolts of Silk",
        "color" => "blue",
        "cathegory" => null,
        "count" => 1,
        "effect" => array( "gain" => array("cloth", "cloth", "cloth")),             //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Bolts of Silk'),
        "text" => array( 'log' => clienttranslate('Gain ${resource}${resource}${resource}'), 
                        'args' => array('resource' => 'resource_cloth' ) )
    ),

    12 => array(
        "name" => "Ancient Helm",
        "color" => "blue",
        "cathegory" => clienttranslate("Relic"),
        "count" => 4,
        "effect" => null,
        "sellcost" => 2,
        "nameTr" => clienttranslate('Ancient Helm'),
        "text" => clienttranslate("Get points for different relics collected"),
    ),

    13 => array(
        "name" => "Ancient Breastplate",
        "color" => "blue",
        "cathegory" => clienttranslate("Relic"),
        "count" => 3,
        "effect" => null,
        "sellcost" => 2,
        "nameTr" => clienttranslate('Ancient Breastplate'),
        "text" => clienttranslate("Get points for different relics collected"),
    ),

    14 => array(
        "name" => "Treasure Map",
        "color" => "red",
        "cathegory" => null,
        "count" => 2,
        "effect" => array( "discardTreasure" => array( array("blue", "blue"),  array("red", "red"), array("yellow", "yellow"),      //done
                                                array("blue", "red"),  array("blue", "yellow"), array("red", "yellow"),
                        )), 
        "sellcost" => 2,
        "text" => "Discard this and another {chest} to draw {chest}{chest} of any colors.",
        "nameTr" => clienttranslate('Treasure Map'),
        "text" => array( 'log' => clienttranslate('Discard this and another ${treasure} to draw ${treasure} ${treasure} of any colors.'), 
                        'args' => array('treasure' => 'treasure_plain' ) )
    ),

    15 => array(
        "name" => "Scroll of Wonders",
        "color" => "red",
        "cathegory" => "Scroll",
        "count" => 6,
        "effect" => array( "gainAndDraw" => array("blue", "red", "yellow")),                                // done
        "sellcost" => 0,
        "nameTr" => clienttranslate('Scroll of Wonders'),
        "text" => array( 'log' => clienttranslate('Gain ${gold} and draw ${treasure}. The next player may pay ${gold} to draw ${treasure} of different type.'), 
                        'args' => array('treasure' => 'treasure_plain', 'gold' => 'gold_1' ) )
    ),

    16 => array(
        "name" => "Lucky Potion",
        "color" => "red",
        "cathegory" => null,
        "count" => 3,
        "effect" => array("luckyPotion" => ''),                                                         //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Lucky Potion'),
        "text" => array( 'log' => clienttranslate('Double the ${treasure} gained on your next quest this turn.'), 
                        'args' => array('treasure' => 'treasure_plain' ) )
    ),

    17 => array(
        "name" => "Stunning Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 3,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Stunning Charm'),
        "text" => "",
    ),

    18 => array(
        "name" => "Sacred Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 3,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Sacred Charm'),
        "text" => "",
    ),

    19 => array(
        "name" => "Ornate Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 3,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Ornate Charm'),
        "text" => "",
    ),

    20 => array(
        "name" => "Ominous Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 3,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Ominous Charm'),
        "text" => "",
    ),

    21 => array(
        "name" => "Mysterious Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 3,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Mysterious Charm'),
        "text" => "",
    ),

    22 => array(
        "name" => "Glowing Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 2,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Glowing Charm'),
        "text" => "",
    ),

    23 => array(
        "name" => "Engraved Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 4,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Engraved Charm'),
        "text" => "",
    ),

    24 => array(
        "name" => "Curious Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 2,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Curious Charm'),
        "text" => "",
    ),

    25 => array(
        "name" => "Carved Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 2,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Carved Charm'),
        "text" => "",
    ),

    26 => array(
        "name" => "Bizarre Charm",
        "color" => "red",
        "cathegory" => clienttranslate("Charm"),
        "count" => 1,
        "points" => 2,
        "effect" => null,
        "sellcost" => 0,
        "nameTr" => clienttranslate('Bizarre Charm'),
        "text" => "",
    ),

    27 => array(
        "name" => "Ancient Sword",
        "color" => "red",
        "cathegory" => clienttranslate("Relic"),
        "count" => 3,
        "effect" => null,
        "sellcost" => 2,
        "nameTr" => clienttranslate('Ancient Sword'),
        "text" => clienttranslate("Get points for different relics collected"),
    ),

    28 => array(
        "name" => "Ancient Boots",
        "color" => "red",
        "cathegory" => "Relic",
        "count" => 4,
        "effect" => null,
        "sellcost" => 2,
        "nameTr" => clienttranslate('Ancient Boots'),
        "text" => clienttranslate("Get points for different relics collected"),
    ),

    29 => array(
        "name" => "Treasure Map",
        "color" => "yellow",
        "cathegory" => null,
        "count" => 2,
        "effect" => array( "discardTreasure" => array( array("blue", "blue"),  array("red", "red"), array("yellow", "yellow"),      //done
                                                            array("blue", "red"),  array("blue", "yellow"), array("red", "yellow"),
                                    )), 
        "sellcost" => 2,
        "text" => "Discard this and another {chest} to draw {chest} {chest} of any colors.",
        "nameTr" => clienttranslate('Treasure Map'),
        "text" => array( 'log' => clienttranslate('Discard this and another ${treasure} to draw ${treasure} ${treasure} of any colors.'), 
                        'args' => array('treasure' => 'treasure_plain' ) )
    ),

    30 => array(
        "name" => "Scroll of Riches",
        "color" => "yellow",
        "cathegory" => clienttranslate("Scroll"),
        "count" => 6,
        "effect" => array( "gain" => array("gold", 4, 2)),                          //done
        "sellcost" => 0,
        "nameTr" => clienttranslate('Scroll of Riches'),
        "text" => array( 'log' => clienttranslate('Gain ${gold4}. The next player gains ${gold2}'), 'args' => array('gold4' => 'gold_4', 'gold2' => 'gold_2' ) )
    ),

    31 => array(
        "name" => "Fortune Potion",
        "color" => "yellow",
        "cathegory" => null,
        "count" => 3,
        "effect" => array("fortunePotion"=> ''),                                    //done
        "sellcost" => 2,
        "nameTr" => clienttranslate('Fortune Potion'),
        "text" => array( 'log' => clienttranslate('Double the ${gold} you gain for crafting your next item.'), 'args' => array('gold' => 'gold_' ) )
    ),

    32 => array(
        "name" => "Coin Purse",
        "color" => "yellow",
        "cathegory" => null,
        "count" => 3,
        "effect" => null,
        "sellcost" => 3,
        "text" => "",
        "nameTr" => clienttranslate('Coin Purse'),
    ),

    33 => array(
        "name" => "Bag of Gold",
        "color" => "yellow",
        "cathegory" => null,
        "count" => 7,
        "effect" => null,
        "sellcost" => 4,
        "nameTr" => clienttranslate('Bag of Gold'),
        "text" => "",
    ),

    34 => array(
        "name" => "Ancient Shield",
        "color" => "yellow",
        "cathegory" => clienttranslate("Relic"),
        "count" => 3,
        "effect" => null,
        "sellcost" => 2,
        "nameTr" => clienttranslate('Ancient Shield'),
        "text" => clienttranslate("Get points for different relics collected"),
    ),

    35 => array(
        "name" => "Ancient Gauntlets",
        "color" => "yellow",
        "cathegory" => clienttranslate("Relic"),
        "count" => 4,
        "effect" => null,
        "sellcost" => 2,
        "nameTr" => clienttranslate('Ancient Gauntlets'),
        "text" =>  clienttranslate("Get points for different relics collected"),
    ),
);

$this->quest = array(
    1 => array(
        "name" => "Crush the Orc Rebellion",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Dagger"), "Weapon"), ),
        "cost" => array(1 => array('wood' => 1, 'iron' => 1) ),
        "gold" => array(1 => 3),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Crush the Orc Rebellion'),
        "text" => '',
    ),

    2 => array(
        "name" => "Explore Bluevein Isle",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Cloak"), "Armor"), 2 => array(clienttranslate("Boots"), "Armor"), ),
        "cost" => array(1 => array('cloth' => 3,), 2=> array( 'leather' => 3, 'iron' => 1), ),
        "gold" => array(1 => 3, 2 => 4),
        "reward" => array('blue', 'yellow'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Explore Bluevein Isle'),
        "text" => '',
    ),

    3 => array(
        "name" => "Fight Off the Sharkskin Gnolls",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Sword"), "Weapon"), 2 => array(clienttranslate("Robe"), "Armor"), ),
        "cost" => array(1 => array('iron' => 3,), 2=> array( 'cloth' => 2, 'leather' => 1), ),
        "gold" => array(1 => 3, 2 => 4),
        "reward" => array('red', 'blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Fight Off the Sharkskin Gnolls'),
        "text" => '',
    ),

    4 => array(
        "name" => "Help the Whitetree Coopers",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Vest"), "Armor"), ),
        "cost" => array(1 => array('leather' => 2, 'cloth' => 1) ),
        "gold" => array(1 => 4),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Help the Whitetree Coopers'),
        "text" => '',
    ),

    5 => array(
        "name" => "Claim the Briareye Gemstone",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Cloak"), "Armor"), ),
        "cost" => array(1 => array('cloth' => 2,) ),
        "gold" => array(1 => 3),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Claim the Briareye Gemstone'),
        "text" => '',
    ),

    6 => array(
        "name" => "Recover the Kingsire's Remains",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Staff"), "Weapon"), 2 => array(clienttranslate("Gauntlets"), "Armor"), ),
        "cost" => array(1 => array('wood' => 3,), 2=> array( 'cloth' => 2, 'iron' => 1), ),
        "gold" => array(1 => 3, 2 => 4),
        "reward" => array('yellow', 'red'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Recover the Kingsire\'s Remains'),
        "text" => '',
    ),

    7 => array(
        "name" => "Seize the Riches of Anduar",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Club"), "Weapon"), 2 => array(clienttranslate("Sword"), "Weapon"), ),
        "cost" => array(1 => array('leather' => 1, 'wood' => 1), 2=> array( 'iron' => 3, 'leather' => 1), ),
        "gold" => array(1 => 3, 2 => 4),
        "reward" => array('blue', 'yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Seize the Riches of Anduar'),
        "text" => '',
    ),

    8 => array(
        "name" => "Wake the Princess of Eternal Sleep",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Vest"), "Armor"), ),
        "cost" => array(1 => array('leather' => 4,) ),
        "gold" => array(1 => 4),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Wake the Princess of Eternal Sleep'),
        "text" => '',
    ),

    9 => array(
        "name" => "Brawl at Goldgrass Inn",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Pants"), "Armor"), 2 => array(clienttranslate("Helmet"), "Armor"), ),
        "cost" => array(1 => array('leather' => 3, ), 2=> array( 'cloth' => 2, 'iron' => 1, 'wood' => 1) ),
        "gold" => array(1 => 3, 2 => 4),
        "reward" => array('red', 'blue'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Brawl at Goldgrass Inn'),
        "text" => '',
    ),

    10 => array(
        "name" => "Fortify Skullbreaker Point",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Shield"), "Armor"), ),
        "cost" => array(1 => array('wood' => 2, 'leather' => 1,) ),
        "gold" => array(1 => 4),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Fortify Skullbreaker Point'),
        "text" => '',
    ),    

    11 => array(
        "name" => "Protect the Eastern Ramparts",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Sword"), "Weapon"), ),
        "cost" => array(1 => array('iron' => 2,) ),
        "gold" => array(1 => 3),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Protect the Eastern Ramparts'),
        "text" => '',
    ),
    
    12 => array(
        "name" => "Topple the Moonstone Giant",
        "cathegory" => "1N",
        "items" => array(1 => array(clienttranslate("Dagger"), "Weapon"), 2 => array(clienttranslate("Axe"), "Weapon"), ),
        "cost" => array(1 => array('iron' => 1, 'leather' => 1, ), 2=> array('iron' => 2, 'wood' => 2) ),
        "gold" => array(1 => 3, 2 => 4),
        "reward" => array('yellow', 'red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Topple the Moonstone Giant'),
        "text" => '',
    ),

    13 => array(
        "name" => "Burn the Briar Roots",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Magic Wand"), "Weapon"), 2 => array(clienttranslate("Dagger"), "Weapon"), ),
        "cost" => array(1 => array('wood' => 1, 'magic' => 1, ), 2=> array('iron' => 2, 'leather' => 1) ),
        "gold" => array(1 => 5, 2 => 4),
        "reward" => array('red', 'blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Burn the Briar Roots'),
        "text" => '',
    ),

    14 => array(
        "name" => "Find the Pinegrove Trolls",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Jeweled Staff"), "Weapon"), ),
        "cost" => array(1 => array('wood' => 1, 'leather' => 1, 'gem' => 2) ),
        "gold" => array(1 => 6),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Find the Pinegrove Trolls'),
        "text" => '',
    ),

    15 => array(
        "name" => "Guard the Faerie Saplings",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Jeweled Gauntlets"), "Armor"), ),
        "cost" => array(1 => array('iron' => 1, 'gem' => 1) ),
        "gold" => array(1 => 5),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Guard the Faerie Saplings'),
        "text" => '',
    ),

    16 => array(
        "name" => "Slay the Vile Necromancer",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Magic Robe"), "Armor"), 2 => array(clienttranslate("Staff"), "Weapon"), ),
        "cost" => array(1 => array('cloth' => 2, 'magic' => 2, ), 2=> array('wood' => 4) ),
        "gold" => array(1 => 6, 2 => 4),
        "reward" => array('blue', 'yellow'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Slay the Vile Necromancer'),
        "text" => '',
    ),

    17 => array(
        "name" => "Acquire the Rod of Ages",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Magic Sword"), "Weapon"), ),
        "cost" => array(1 => array('iron' => 2, 'magic' => 2) ),
        "gold" => array(1 => 6),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Acquire the Rod of Ages'),
        "text" => '',
    ),

    18 => array(
        "name" => "Catch the Jeweltone Fox",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Jeweled Wand"), "Weapon"), ),
        "cost" => array(1 => array('wood' => 1, 'gem' => 1) ),
        "gold" => array(1 => 5),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Catch the Jeweltone Fox'),
        "text" => '',
    ),

    19 => array(
        "name" => "Sail to Bloodsand Beach",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Jeweled Vest"), "Armor"), 2 => array(clienttranslate("Shield"), "Armor"), ),
        "cost" => array(1 => array('cloth' => 1, 'leather' => 1, 'gem' => 2, ), 2=> array('iron' => 2, 'wood' => 1) ),
        "gold" => array(1 => 6, 2 => 4),
        "reward" => array('yellow', 'red'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Sail to Bloodsand Beach'),
        "text" => '',
    ),

    20 => array(
        "name" => "Thwart the Mad Witch's Plans",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Magic Pants"), "Armor"), 2 => array(clienttranslate("Bow"), "Weapon"), ),
        "cost" => array(1 => array('leather' => 1, 'magic' => 1, ), 2=> array('wood' => 2, 'cloth' => 2) ),
        "gold" => array(1 => 5, 2 => 4),
        "reward" => array('blue', 'yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Thwart the Mad Witch\'s Plans'),
        "text" => '',
    ),

    21 => array(
        "name" => "Climb the Shimmering Mountain",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Jeweled Robe"), "Armor"), ),
        "cost" => array(1 => array('cloth' => 2, 'gem' => 2) ),
        "gold" => array(1 => 6),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Climb the Shimmering Mountain'),
        "text" => '',
    ),

    22 => array(
        "name" => "Destroy the Dragon Lord",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Magic Dagger"), "Weapon"), 2 => array(clienttranslate("Cloak"), "Armor"), ),
        "cost" => array(1 => array('iron' => 1, 'cloth' => 1, 'magic' => 2, ), 2=> array('cloth' => 2, 'leather' => 1) ),
        "gold" => array(1 => 6, 2 => 4),
        "reward" => array('red', 'blue'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Destroy the Dragon Lord'),
        "text" => '',
    ),

    23 => array(
        "name" => "Retrieve the Chalice of Power",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Jeweled Cloak"), "Armor"), 2 => array(clienttranslate("Staff"), "Weapon"), ),
        "cost" => array(1 => array('cloth' => 1, 'gem' => 1, ), 2=> array('wood' => 3, 'leather' => 1) ),
        "gold" => array(1 => 5, 2 => 4),
        "reward" => array('yellow', 'red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Retrieve the Chalice of Power'),
        "text" => '',
    ),

    24 => array(
        "name" => "Secure the Oldtown Library",
        "cathegory" => "1S",
        "items" => array(1 => array(clienttranslate("Magic Club"), "Weapon"), ),
        "cost" => array(1 => array('leather' => 1, 'magic' => 1) ),
        "gold" => array(1 => 5),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Secure the Oldtown Library'),
        "text" => '',
    ),

    25 => array(
        "name" => "Banish the Fruitfang Terrors",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Wand"), "Armor"), 2 => array(clienttranslate("Sword"), "Weapon"),  ),
        "cost" => array(1 => array('wood' => 2, ), 2=> array('iron' => 2, 'cloth' => 1) ),
        "gold" => array(1 => 4, 2 => 5),
        "reward" => array('red', 'blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Banish the Fruitfang Terrors'),
        "text" => '',
    ),

    26 => array(
        "name" => "Break the Bluevein Curse",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Staff"), "Weapon"), ),
        "cost" => array(1 => array('wood' => 2, 'magic' => 2) ),
        "gold" => array(1 => 7),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Break the Bluevein Curse'),
        "text" => '',
    ),

    27 => array(
        "name" => "Cleanse the Coalbone Cemetery",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Jeweled Club"), "Weapon"), 2 => array(clienttranslate("Robe"), "Armor"),  ),
        "cost" => array(1 => array('leather' => 1, 'gem' => 1 ), 2=> array('cloth' => 4) ),
        "gold" => array(1 => 6, 2 => 5),
        "reward" => array('red', 'blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Cleanse the Coalbone Cemetery'),
        "text" => '',
    ),

    28 => array(
        "name" => "Plunder the Warlock's Tower",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Pants"), "Weapon"), ),
        "cost" => array(1 => array('leather' => 3,) ),
        "gold" => array(1 => 4),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Plunder the Warlock\'s Tower'),
        "text" => '',
    ),

    29 => array(
        "name" => "Release the Briarsblight Cure",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Bow"), "Weapon"), 2 => array(clienttranslate("Jeweled Vest"), "Armor"),  ),
        "cost" => array(1 => array('wood' => 1, 'cloth' => 1, 'magic' => 1 ), 2=> array('leather' => 2, 'gem' => 2) ),
        "gold" => array(1 => 7, 2 => 7),
        "reward" => array('blue', 'yellow'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Release the Briarsblight Cure'),
        "text" => '',
    ),

    30 => array(
        "name" => "Revive the Fallen Queen",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Cloak"), "Armor"), ),
        "cost" => array(1 => array('cloth' => 1, 'magic' => 1) ),
        "gold" => array(1 => 6),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Revive the Fallen Queen'),
        "text" => '',
    ),

    31 => array(
        "name" => "Uncover the Sharkfang Mystery",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Jeweled Shield"), "Armor"), 2 => array(clienttranslate("Helmet"), "Armor"),  ),
        "cost" => array(1 => array('iron' => 2, 'wood' => 1, 'gem' => 1 ), 2=> array('leather' => 1, 'wood' => 1) ),
        "gold" => array(1 => 7, 2 => 4),
        "reward" => array('blue', 'yellow'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Uncover the Sharkfang Mystery'),
        "text" => '',
    ),

    32 => array(
        "name" => "Vanquish the Goblin Horde",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Axe"), "Weapon"),  ),
        "cost" => array(1 => array('iron' => 3, 'wood' => 1, ) ),
        "gold" => array(1 => 5,),
        "reward" => array('blue'),
        "hero" => array('mage'),
        "nameTr" => clienttranslate('Vanquish the Goblin Horde'),
        "text" => '',
    ),

    33 => array(
        "name" => "Discover the Hidden Valley",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Pants"), "Armor"), 2 => array(clienttranslate("Gauntlets"), "Armor"),  ),
        "cost" => array(1 => array('leather' => 2,), 2=> array('iron' => 2, 'cloth' => 2) ),
        "gold" => array(1 => 4, 2 => 5),
        "reward" => array('blue', 'yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Discover the Hidden Valley'),
        "text" => '',
    ),

    34 => array(
        "name" => "Pillage the Vault of the Ancients",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Bow"), "Weapon"),  ),
        "cost" => array(1 => array('wood' => 2, 'cloth' => 1, ) ),
        "gold" => array(1 => 5,),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Pillage the Vault of the Ancients'),
        "text" => '',
    ),

    35 => array(
        "name" => "Save the Plumtree Grangers",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Jewled Staff"), "Weapon"), 2 => array(clienttranslate("Pants"), "Armor"),  ),
        "cost" => array(1 => array('wood' => 2, 'gem' => 2), 2=> array('cloth' => 1, 'leather' => 1) ),
        "gold" => array(1 => 7, 2 => 4),
        "reward" => array('blue', 'yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Save the Plumtree Grangers'),
        "text" => '',
    ),

    36 => array(
        "name" => "Sink the Zombielord's Galley",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Dagger"), "Weapon"), 2 => array(clienttranslate("Bow"), "Weapon"),  ),
        "cost" => array(1 => array('iron' => 1, 'magic' => 1), 2=> array('wood' => 3, 'cloth' => 1) ),
        "gold" => array(1 => 6, 2 => 5),
        "reward" => array('yellow', 'red'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Sink the Zombielord\'s Galley'),
        "text" => '',
    ),

    37 => array(
        "name" => "Sneak into the Sultan's Palace",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Sword"), "Weapon"),  ),
        "cost" => array(1 => array('iron' => 3, ) ),
        "gold" => array(1 => 4,),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Sneak into the Sultan\'s Palace'),
        "text" => '',
    ),

    38 => array(
        "name" => "Steal the Golden Sapling",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Jeweled Cloak"), "Armor"),  ),
        "cost" => array(1 => array('cloth' => 1, 'gem' => 1 ) ),
        "gold" => array(1 => 6,),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Steal the Golden Sapling'),
        "text" => '',
    ),

    39 => array(
        "name" => "Track the Sludge Ents",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Vest"), "Armor"),  ),
        "cost" => array(1 => array('leather' => 2, 'cloth' => 1, 'magic' => 1 ) ),
        "gold" => array(1 => 7,),
        "reward" => array('yellow'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Track the Sludge Ents'),
        "text" => '',
    ),

    40 => array(
        "name" => "Unearth the Kingsire's Treasure",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Club"), "Weapon"), 2 => array(clienttranslate("Jeweled Boots"), "Armor"),  ),
        "cost" => array(1 => array('cloth' => 1, 'leather' => 1, 'magic' => 2), 2=> array('leather' => 1, 'iron' => 1, 'gem' => 1) ),
        "gold" => array(1 => 7, 2 => 7),
        "reward" => array('yellow', 'red'),
        "hero" => array('rogue'),
        "nameTr" => clienttranslate('Unearth the Kingsire\'s Treasure'),
        "text" => '',
    ),

    41 => array(
        "name" => "Assault the Obsidian Spire",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Jeweled Dagger"), "Weapon"), 2 => array(clienttranslate("Club"), "Weapon"),  ),
        "cost" => array(1 => array('iron' => 1, 'gem' => 1 ), 2=> array('leather' => 2, 'wood' => 1, 'cloth' => 1) ),
        "gold" => array(1 => 6, 2 => 5),
        "reward" => array('red', 'blue'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Assault the Obsidian Spire'),
        "text" => '',
    ),

    42 => array(
        "name" => "Capture the Marauding Prince",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Sword"), "Weapon"),  ),
        "cost" => array(1 => array('iron' => 4, ) ),
        "gold" => array(1 => 5,),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Capture the Marauding Prince'),
        "text" => '',
    ),

    43 => array(
        "name" => "Defend the Temple of Wonders",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Shield"), "Armor"),  ),
        "cost" => array(1 => array('wood' => 1, 'magic' => 1) ),
        "gold" => array(1 => 6,),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Defend the Temple of Wonders'),
        "text" => '',
    ),

    44 => array(
        "name" => "Locate the Cavern of Secrets",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Gauntlets"), "Armor"),  ),
        "cost" => array(1 => array('cloth' => 1, 'iron' => 1, ) ),
        "gold" => array(1 => 4,),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Locate the Cavern of Secrets'),
        "text" => '',
    ),

    45 => array(
        "name" => "Raid the Frost's Queen Castle",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Jeweled Bow"), "Weapon"),  ),
        "cost" => array(1 => array('wood' => 1, 'cloth' => 1, 'gem' => 1 ) ),
        "gold" => array(1 => 7,),
        "reward" => array('red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate("Raid the Frost's Queen Castle"),
        "text" => '',
    ),

    46 => array(
        "name" => "Rescue the Graytop Miners",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Helmet"), "Armor"), 2 => array(clienttranslate("Cloak"), "Armor"),  ),
        "cost" => array(1 => array('wood' => 1, 'leather' => 1, 'magic' => 2 ), 2=> array('cloth' => 3) ),
        "gold" => array(1 => 7, 2 => 4),
        "reward" => array('yellow', 'red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Rescue the Graytop Miners'),
        "text" => '',
    ),

    47 => array(
        "name" => "Search for the Fountain of Youth",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Staff"), "Weapon"), 2 => array(clienttranslate("Vest"), "Armor"),  ),
        "cost" => array(1 => array('wood' => 3, ), 2=> array('leather' => 2, 'cloth' => 1) ),
        "gold" => array(1 => 4, 2 => 5),
        "reward" => array('yellow', 'red'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Search for the Fountain of Youth'),
        "text" => '',
    ),

    48 => array(
        "name" => "Take Down the Red Dog Gang",
        "cathegory" => "2",
        "items" => array(1 => array(clienttranslate("Magic Sword"), "Weapon"), 2 => array(clienttranslate("Jeweled Boots"), "Armor"),  ),
        "cost" => array(1 => array('iron' => 1, 'leather' => 1, 'magic' => 1 ), 2=> array('leather' => 1, 'iron' => 1, 'gem' => 2) ),
        "gold" => array(1 => 7, 2 => 7),
        "reward" => array('red', 'blue'),
        "hero" => array('warrior'),
        "nameTr" => clienttranslate('Take Down the Red Dog Gang'),
        "text" => '',
    ),

    49 => array(
        "name" => "The King's Funeral",
        "cathegory" => "Funeral",
        "nameTr" => clienttranslate('The King\'s Funeral'),
        "text" =>  array( 'log' => clienttranslate('Each player secretly bids ${gold}. The winner pays and builds the King\'s Statue in their guild. Than draw the next card. ${newline} (King\'s Statue has ${points4})'),
                        'args' => array('gold' => 'gold_', 'newline' => 'newline', 'points4' => 'points_4') )
    ),

    50 => array(
        "name" => "Offering to the Council",
        "cathegory" => "Council",
        "nameTr" => clienttranslate('Offering to the Council'),
        "text" => array( 'log' => clienttranslate('Scoring - if you made an offering, gain ${points2} plus: ${newline} ${points1} for every ${gold} ${newline} ${points1} for every ${resource} ${resource}'),
                        'args' => array('points2' => 'points_2', 'points1' => 'points_1', 'gold' => 'gold_3', 'resource' => 'resource_plain', 'newline' => 'newline' ) )
    ),  
);

$this->playermats = array(
    'A' => array( '3_0' => 'cloth', '0_1' => 'iron', '2_1' => 'leather', '1_2' => 'wood', '3_2' => 'gold',),
    'B' => array( '2_1' => 'cloth', '3_2' => 'iron', '0_1' => 'leather', '3_0' => 'wood', '1_2' => 'gold',),
    'C' => array( '1_2' => 'cloth', '2_1' => 'iron', '3_0' => 'leather', '3_2' => 'wood', '0_1' => 'gold',),
    'D' => array( '3_1' => 'cloth', '1_1' => 'iron', '2_2' => 'leather', '0_0' => 'wood', '0_2' => 'gold',),
    'E' => array( '1_1' => 'cloth', '0_0' => 'iron', '0_2' => 'leather', '3_1' => 'wood', '2_2' => 'gold',),
    'F' => array( '0_2' => 'cloth', '2_2' => 'iron', '0_0' => 'leather', '1_1' => 'wood', '3_1' => 'gold',),
);

$this->guilds = array(
    1 => array( 'name'=> "Craft Collective", 'startBonus' => array("clientState" => null , "action_buttons" => null ), ),
    2 => array( 'name'=> "Explorers League", 'startBonus' => array("clientState" => 'takeResources', "action_buttons" => array( array('cloth', 'cloth'), array('cloth', 'leather'), array('leather', 'leather') )  ), ),
    3 => array( 'name'=> "Greycastle Guard", 'startBonus' => array("clientState" => 'takeResources', "action_buttons" => array( array('iron', 'iron'), array('iron', 'wood'), array('wood', 'wood') )  ), ),
    4 => array( 'name'=> "Mercantile Trust", 'startBonus' => array("clientState" => null, "action_buttons" => null ), ),
    5 => array( 'name'=> "Starfall Syndicate", 'startBonus' =>  array("clientState" => 'takeTreasure', "action_buttons" =>  array('blue','red', 'yellow'),), ),
    6 => array( 'name'=> "The Holy Order", 'startBonus' => array("clientState" => 'shrineRoom', "action_buttons" => null ), )
);

$this->tokens_number = array(
    'Warehouse' => array(1=> 1, 2=> 1, 3=> 2, 4=> 3,5=> 3, 6=> 4),
    'Library' => array(1=> 1, 2=> 1, 3=> 2,  4=> 3,5=> 3, 6=> 4),
    'Gem Workshop' => array(1=> 1, 2=> 1, 3=> 2,4=> 2,5=> 3, 6=> 3),
    'Magic Arcaenum' => array(1=> 1, 2=> 1, 3=> 2,4=> 2,5=> 3, 6=> 3),
    'Bedchamber' => array(1=> 2, 2=> 2, 3=> 3,4=> 4,5=> 5, 6=> 6),
    'Kitchen' => array(1=> 2, 2=> 2, 3=> 3,4=> 4,5=> 5, 6=> 6),
    'Great Hall' => array(1=> 2, 2=> 2, 3=> 3,4=> 4,5=> 5, 6=> 6),
    'Barracks' => array(1=> 2, 2=> 2, 3=> 3,4=> 4,5=> 5, 6=> 6),

    'specialists' => array(1=> 5, 2=> 5, 3=> 7,4=> 8, 5=> 9, 6=> 10),
    'baseresource' => array(1=> 4, 2=> 5, 3=> 6,4=> 7, 5=> 8, 6=> 8),
    'advresource' => array(1=> 3, 2=> 3, 3=> 4,4=> 5, 5=> 6, 6=> 6),
    '1N' => array(1=> 7, 2=> 5, 3=> 7,4=> 8, 5=> 9, 6=> 10),
    '1S' => array(1=> 7, 2=> 5, 3=> 7,4=> 8, 5=> 9, 6=> 10),
    '2' => array(1=> 12, 2=> 8, 3=> 12,4=> 14, 5=> 17, 6=> 19),
    'startinggold' => array(1=> 3, 2=> 4, 3=> 5,4=> 5, 5=> 6, 6=> 6),
);

















    


