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
 * stats.inc.php
 *
 * kingsguild game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "table_turnsNumber" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),


        "table_roomsBuilt" => array(   "id"=> 11,
                                "name" => totranslate("Number of rooms built"), 
                                "type" => "int" ),
                                
        "table_specialistsHired" => array(   "id"=> 12,
                                "name" => totranslate("Number of specialists hired"), 
                                "type" => "int" ),


        "table_treasureCardsPlayed" => array(   "id"=> 13,
                                "name" => totranslate("Number of treasure cards played"), 
                                "type" => "int" ),
                                
        "table_treasureCardsSold" => array(   "id"=> 14,
                                "name" => totranslate("Number of treasure cards sold"), 
                                "type" => "int" ),

        "table_resourceGathered" => array(   "id"=> 15,
                                "name" => totranslate("Number of resources gathered"), 
                                "type" => "int" ),

        "table_itemsCrafted" => array(   "id"=> 16,
                                "name" => totranslate("Number of items crafted"), 
                                "type" => "int" ), 
                                
        "table_questsCompleted" => array(   "id"=> 17,
                                "name" => totranslate("Number of quests completed"), 
                                "type" => "int" ), 

        "table_kingsStatue" => array(   "id"=> 18,
                                "name" => totranslate("Gold needed to win King's Statue"), 
                                "type" => "int" ), 
 
    ),
    
    // Statistics existing for each player
    "player" => array(
        "player_goldGained" => array(   "id"=> 10,
                                "name" => totranslate("Amount of gold gained"), 
                                "type" => "int" ),

        "player_roomsBuilt" => array(   "id"=> 11,
                                "name" => totranslate("Number of rooms built"), 
                                "type" => "int" ),
                                
        "player_specialistsHired" => array(   "id"=> 12,
                                "name" => totranslate("Number of specialists hired"), 
                                "type" => "int" ),


        "player_treasureCardsPlayed" => array(   "id"=> 13,
                                "name" => totranslate("Number of treasure cards played"), 
                                "type" => "int" ),
                                
        "player_treasureCardsSold" => array(   "id"=> 14,
                                "name" => totranslate("Number of treasure cards sold"), 
                                "type" => "int" ),

        "player_resourceGathered" => array(   "id"=> 15,
                                "name" => totranslate("Number of resources gathered. (Via gather action only)"), 
                                "type" => "int" ),

        "player_itemsCrafted" => array(   "id"=> 16,
                                "name" => totranslate("Number of items crafted"), 
                                "type" => "int" ), 
                                
        "player_questsCompleted" => array(   "id"=> 17,
                                "name" => totranslate("Number of quests completed"), 
                                "type" => "int" ), 
                                
        "player_charmsSpoints" => array(   "id"=> 18,
                                "name" => totranslate("Points from Charms"), 
                                "type" => "int" ),


        "player_relicsPoints" => array(   "id"=> 19,
                                "name" => totranslate("Ponts from Relics"), 
                                "type" => "int" ),
                                
        "player_questPoints" => array(   "id"=> 20,
                                "name" => totranslate("Points from quests"), 
                                "type" => "int" ),

        "player_specialistsPoints" => array(   "id"=> 21,
                                "name" => totranslate("Points from specialists"), 
                                "type" => "int" ),

        "player_roomsPoints" => array(   "id"=> 22,
                                "name" => totranslate("Points from rooms"), 
                                "type" => "int" ), 
                                
        "player_offeringPoints" => array(   "id"=> 23,
                                "name" => totranslate("Points from Offering to the Council"), 
                                "type" => "int" ), 
    )

);
