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
 * states.inc.php
 *
 * kingsguild game states description
 *
 */
$machinestates = array(
    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),

    2 => array(
        "name" => "gameStart",
        "description" => clienttranslate('preparing game components'),
        "type" => "game",
        "action" => "stGameStart",
        "updateGameProgression" => false,   
        "transitions" => array( "guildplay" => 3, "endGuildPhase" => 4 )
    ),

    3 => array(                 // choose starting bonuses
        "name" => "playerGuildTurn",
        "description" => clienttranslate('${actplayer} must choose guild bonus'),
        "descriptionmyturn" => clienttranslate('${You} must choose guild bonus'),
        "type" => "activeplayer",
        "args" => "argPlayerGuildTurn",
        "possibleactions" => array("takeResourcesAndReplace", "placeRoom", "drawTreasureCard"),
        "transitions" => array( "nextPlayer" => 2, "placeRoom"=> 2, "zombiePass" => 2 )
    ), 
//------------------------------------------------------------------------------------
    // 4 => array(                     // entry point for player action
    //     "name" => "playerTurn",
    //     "description" => clienttranslate('${actplayer} must choose one action'),
    //     "descriptionmyturn" => clienttranslate('${you} must choose one action or play treasure card'),
    //     "type" => "activeplayer",
    //     "args" => "argPlayerTurn",
    //     "possibleactions" => array( "selectAction", "specialAction", "playTreasureCard", "bardAction", "oracleAction", "placeSpecialist", "makeOffering" ),
    //     "transitions" => array( "gather" => 5, "expand" => 6, "craft" => 7, "specialAction" => 10, "playTreasureNoAction" => 4 ,  "playTreasure" => 16, "makeOffering" => 30, "zombiePass" => 30)
    // ), 

    4 => array(                     // entry point for player action
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} can gather, craft, expand or play a treasure card'),
        "descriptionmyturn" => clienttranslate('${you} can gather, craft, expand or play a treasure card'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => array( "specialAction", "playTreasureCard", "bardAction", "oracleAction", "placeSpecialist", "makeOffering", "chooseResource","selectExpandItem", "craftItem", "soloFuneral", "cancel" ),
        "transitions" => array( "gather" => 5, "expand" => 6, "craft" => 7, "specialAction" => 10, "playTreasureNoAction" => 4 ,  "playTreasure" => 16, "makeOffering" => 30,  "cancel" => 4, "zombiePass" => 30, "soloFuneral" => 20 )
    ), 
//------------------------------------------------------------------------------------
    5 => array(                     // base actions
        "name" => "playerGather",
        "description" => clienttranslate('${actplayer} must select resources to gather'),
        "descriptionmyturn" => clienttranslate('${you} must select resources to gather'),
        "type" => "activeplayer",
        "args" => "argPlayerGather",
        "possibleactions" => array( "chooseResource", "takeResources", "takeResourcesAndReplace", "cancel"),
        "transitions" => array( "takeResources" => 10, "cancel" => 4,  "zombiePass" => 30)
    ), 
    6 => array(
        "name" => "playerExpand",
        "description" => clienttranslate('${actplayer} must select room or specialist'),
        "descriptionmyturn" => clienttranslate('${you} must select room or specialist'),
        "type" => "activeplayer",
        "args" => "argPlayerExpand",
        "possibleactions" => array( "selectExpandItem", "placeRoom",  "placeSpecialist", "cancel", "pass"),
        //"transitions" => array( "placeRoom" => 10, "placeSpecialist" => 10, "cancel" => 4,  "zombiePass" => 30)
        "transitions" => array( "placeRoom" => 10, "placeSpecialist" => 10, "playTreasureNoAction" => 6 , "playTreasure" => 16, "pass" => 25,  "cancel" => 4, "cancelSolo" => 6, "zombiePass" => 30)
    ), 
    7 => array(
        "name" => "playerCraft",
        "description" => clienttranslate('${actplayer} must choose item to craft'),
        "descriptionmyturn" => clienttranslate('${you} must choose item to craft'),
        "type" => "activeplayer",
        "args" => "argPlayerCraft",
        "possibleactions" => array( "craftItem", "cancel"),
        "transitions" => array( "craftItem" => 10, "craftItemAndSell" => 15, "completeQuestShared" => 14, "cancel" => 4,  "zombiePass" => 30)
    ), 
//------------------------------------------------------------------------------------
    8 => array(                     // second part of expand action
        "name" => "playerBuildRoomOnly",
        "description" => clienttranslate('${actplayer} may build room or pass'),
        "descriptionmyturn" => clienttranslate('${you} may build room or pass'),
        "type" => "activeplayer",
        "args" => "argPlayerExpand",
        "possibleactions" => array( "selectExpandItem", "placeRoom",  "playTreasureCard", "pass",  "cancel"),
        "transitions" => array( "placeRoom" => 10, "playTreasureNoAction" => 8 , "playTreasure" => 16, "pass" => 30,  "cancel" => 8, "zombiePass" => 30)
    ), 
    9 => array(
        "name" => "playerHireSpecialistOnly",
        "description" => clienttranslate('${actplayer} may hire specialist or pass'),
        "descriptionmyturn" => clienttranslate('${you} may hire specialist or pass'),
        "type" => "activeplayer",
        "args" => "argPlayerExpand",
        "possibleactions" => array( "selectExpandItem", "placeSpecialist",  "playTreasureCard", "pass",  "cancel"),
        "transitions" => array( "placeSpecialist" => 10, "playTreasureNoAction" => 9 , "playTreasure" => 16, "pass" => 30,  "cancel" => 9, "zombiePass" => 30)
    ), 
//------------------------------------------------------------------------------------
    10 => array(                // check for special action and select right transition
        "name" => "gameActionSelection",
        "type" => "game",
        "action" => "stgameActionSelection",
        "updateGameProgression" => false,  
        "transitions" => array(  "playerEndTurn" => 25, "nextPlayer" => 30, "buildOnly" => 8, "specialistonly" => 9, "replaceBonusRes" => 11, "specialAction" => 12, "specialCraftAction" => 13,
                                 "runAgain" => 10, "finishTreasureCardPlay" => 17, "appraiser" =>13, "sellTreasures" =>15, "funeral"=>18,
                                 "zombiePass" => 30,  "soloPlayExpand" => 6)
    ), 
//------------------------------------------------------------------------------------
    11 => array(                // everytime player needs to return resources because of storage limit
        "name" => "playerReplaceBonusResource",
        "description" => clienttranslate('${actplayer} can take bonus resource or pass'),
        "descriptionmyturn" => clienttranslate('${you} can replace one of your resources by bonus resource or pass'),
        "type" => "activeplayer",
        "args" => "argPlayerReplaceBonusResource",
        "possibleactions" => array( "takeResources", "takeResourcesAndReplace"),
        "transitions" => array( "takeResources" => 10, "zombiePass" => 30)
    ), 

    12 => array(                // specialist one-time action (after placed to the guild)
        "name" => "playerSpecialistOneTimeAction",
        "description" => clienttranslate('${actplayer} must use specialist ability'),
        "descriptionmyturn" => clienttranslate('${you} must use specialist ability'),
        "type" => "activeplayer",
        "args" => "argPlayerSpecialistOneTimeAction",
        "possibleactions" => array(  "chooseResource", "takeResources", "takeResourcesAndReplace", "drawTreasureCard","placeSpecialist", 
                                    "stealResource", "craftItem", "selectQuest", "selectTreasureCards", "pass", "cancel"),
        "transitions" => array( "pass" => 10, "takeResources" => 10, "drawTreasureCard" => 10, "drawTreasureCardAndSell" => 15, "placeSpecialist" => 10, 
                                "stealResource" => 10, "craftItem"=>10, "craftItemAndSell"=>15, "completeQuestShared" => 14, "selectQuest" => 10, 
                                "selectTreasureForDiscard"=> 12,"discardTreasures"=> 10, "zombiePass" => 30, "cancel" => 10)
    ), 

    13 => array(                // specialist action (after crafting item)
        "name" => "playerSpecialistCraftAction",
        "description" => clienttranslate('${actplayer} can use ${specialist}\'s ability'),
        "descriptionmyturn" => '',
        "type" => "activeplayer",
        "args" => "argPlayerSpecialistCraftAction",
        "possibleactions" => array(  "drawTreasureCard", "selectTreasureCards", "selectQuest", "pass"),
        "transitions" => array( "pass" => 10, "drawTreasureCardAndSell" => 15, "drawTreasureCard" => 10, "placeSpecialist" => 10, "stealResource" => 10, 
                                "playTreasureNoAction" => 10, "playTreasure" => 16, "selectQuest"=> 10, "zombiePass" => 30)
    ), 
//------------------------------------------------------------------------------------
    14 => array(            // select treasure cards after shared quest completed
        "name" => "playerSelectTreasureCard",
        "description" => clienttranslate('${actplayer} must choose ${card_number} treasure card(s)'),
        "descriptionmyturn" => clienttranslate('${you} must choose ${card_number} card(s) to keep'),
        "type" => "activeplayer",
        "args" => "argPlayerSelectTreasureCard",
        "possibleactions" => array( "selectTreasureCards", "confirm"),
        "transitions" => array( "confirm" => 10, "confirmAndSell" => 15, "zombiePass" => 30)
    ),

    15 => array(        // everytime player has more treasures than limit
        "name" => "playerSellTreasure",
        "description" => clienttranslate('${actplayer} must sell or discard treasure card(s)'),
        "descriptionmyturn" => '',
        "type" => "multipleactiveplayer",
        "args" => "argPlayerSellTreasure",
        "possibleactions" => array( "selectTreasureCards", "confirm"),
        "transitions" => array( "confirm" => 10, "confirmCardPlay" => 17, "zombiePass" => 30)
    ),

    16 => array(                 // treasure cards effects
        "name" => "playerPlayTreasureEffect",
        "description" => clienttranslate('${actplayer} must resolve treasure card effect'),
        "descriptionmyturn" => '',
        "type" => "activeplayer",
        "args" => "argPlayerPlayTreasureEffect",
        "possibleactions" => array( "confirm", "takeResourcesAndReplace", "drawTreasureCard", "chooseResource", "selectExpandItem", "placeSpecialist", "selectTreasureCards", "pass", "cancel"),
        "transitions" => array( "confirm" => 17, "drawTreasureCardAndSell" => 15, "cancelBetween" => 10, "pass"=>17, "placeSpecialist"=> 10, "selectTreasureDiscard" =>16,  "zombiePass" => 30, "cancel" => 16)
    ),

    17 => array(                // next player resolve treasure effect
        "name" => "nextPlayerPlayTreasure",
        "type" => "game",
        "action" => "stNextPlayerPlayTreasure",
        "updateGameProgression" => false,   
        "transitions" => array( "nextPlayer" => 16, "backToNormalActions" => 4, "backToNormalActionsBetween" => 10, "backToNormalActionsEnd" => 25  )
    ),
//------------------------------------------------------------------------------------
    18 => array(                                // KINGS FUNERAL
        "name" => "kingsFuneralBidding",
        "description" => clienttranslate('Other players must bid on the King\'s Statue'),
        "descriptionmyturn" => clienttranslate('King\'s Funeral: ${you} must bid on the King\'s Statue'),
        "type" => "multipleactiveplayer",
        "args" => "argKingsFuneralBidding",
        "action" => "stKingsFuneralBidding",
        "possibleactions" => array( "makeBid"),
        "transitions" => array( "makeBid" => 19, "zombiePass" => 19)
    ),

    19 => array(       
        "name" => "resolveFuneralBidding",
        "type" => "game",
        "action" => "stResolveFuneralBidding",
        "updateGameProgression" => false,   
        "transitions" => array( "nowinner" => 10, "winner" => 20 )
    ),

    20 => array(       
        "name" => "playerPlaceKingStatue",
        "description" => clienttranslate('${actplayer} must place the King\'s Statue to the guild'),
        "descriptionmyturn" => clienttranslate('${you} must place the King\'s Statue to your guild'),
        "type" => "activeplayer",
        "args" => "argPlayerPlaceKingStatue",
        "possibleactions" => array( "placeRoom"),
        "transitions" => array( "placeRoom" => 10, "placeRoomAndReplace" => 11, "zombiePass" => 30)
    ),
//------------------------------------------------------------------------------------
    25 => array(            // player can only play treasure card or end turn
        "name" => "playerEndTurn",
        "description" => clienttranslate('${actplayer} may play treasure card(s) or end turn'),
        "descriptionmyturn" => clienttranslate('${you} may play treasure card(s) or end turn'),
        "type" => "activeplayer",
        "args" => "argPlayerEndTurn",
        "possibleactions" => array( "endTurn", "playTreasureCard", "bardAction", "oracleAction", 'cancel', 'placeSpecialist'),
        "transitions" => array("playTreasureNoAction" => 25 ,  "playTreasure" => 16, "endTurn" => 30, "zombiePass" => 30, 'cancel' => 25)
    ),

    30 => array(                // transition to next player
        "name" => "nextPlayer",
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "nextPlayer" => 4, "endGame" => 40, "zobiePass" => 4 )
    ),

    40 => array( 
        "name" => "endCalculations",
        "type" => "game",
        "action" => "stEndCalculations",
        "updateGameProgression" => false,   
        "transitions" => array( "" => 99 )
    ),
//------------------------------------------------------------------------------------ 
    // Final state.
    // Please do not modify.
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )
);