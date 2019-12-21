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
  * kingsguild.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require_once('modules/kgActionMapper.php');


class kingsguild extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            "transition_from_state" => 10,
            "expandAction_roomPlayed" => 11,
            "expandAction_specialistPlayed" => 12,
            "bonus_res_replace" => 13,
            "placed_specialist_type" => 14,
            "player_gain_treasure" => 15,
            "craft_action_hero" => 16,   // 1 - warrior 2 - rogue 3 - mage 
            "specialist_craft_action_played" => 17,
            "played_treasure_card" => 18,
            "second_player_treasurePlay" => 19,  
            "alreadySelected_treasureCard" => 20,  
            "selected_treasure_card_discard" => 21,
            "appraiser_active" => 22,  
            "player_play_appraiser" => 23, 
            "warlock_active" =>  24,
            "newQuestCardPosition" => 25,
            "activePlayerAfterBidding" => 26,
            "offeringActive" => 27,
            "playerPlayLast" => 28,
            "playerEndPhase" => 29,
            "startPhase" => 30,
            "soloExpandSecondPart" => 31,
            "soloKingsFuneral" => 32
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "kingsguild";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        shuffle($default_colors);
        $player_mats = array("A", "B","C", "D", "E", "F");
        shuffle($player_mats);

        // Create players and guild rooms
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_mat, player_guild) VALUES ";
        $sql_guild = "INSERT INTO room (room_type, room_location, room_location_arg, room_side) VALUES ";
        $values = array();
        $values_guild = array();

        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $guild = array_search ($color, $gameinfos['player_colors'])+1;
            $mat = array_shift( $player_mats );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."','$mat', '$guild')";

            $type = $this->getKeyByValueMultidim($this->rooms,"name",$this->guilds[$guild]['name']);
            $values_guild[] = "('$type', '".$player_id."','1_0','1')";
        
        }
        $sql .= implode( $values, ',' );
        $sql_guild .= implode( $values_guild, ',' );
        self::DbQuery( $sql );
        self::DbQuery( $sql_guild );

        // Create basic rooms
        $players_nbr = count($players);
        $basic_rooms = array_filter($this->rooms, function($elem) { return $elem['cathegory'] == 'basic';});

        $values = array();
        $sql = "INSERT INTO room (room_type, room_location, room_location_arg, room_side) VALUES ";

        foreach( $basic_rooms as $type => $room ) {
            for($i=0;$i<$this->tokens_number[$room['name']][$players_nbr];$i++ ) {
                if ($room['two_sided'] != false) {
                    $side = $type == $room['two_sided'][0] ? 1:0;
                } else {
                    $side = "NULL";
                }
                $values[] = "('$type', 'board','".addslashes( $room['position'] )."', '$side')";
            }

        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        // Create master rooms
        $master_rooms = array_filter($this->rooms, function($elem) { return $elem['cathegory'] == 'master';});
        $master_keys = array_keys ( $master_rooms );
        shuffle($master_keys);

        $values = array();
        $sql = "INSERT INTO room (room_type, room_location, room_location_arg) VALUES ";
        for($i=0;$i<($players_nbr+3);$i++ ) {
            $values[] = "('$master_keys[$i]', 'board','$i')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );

        // King statue 
        $sql = "INSERT INTO room (room_type, room_location, room_location_arg) VALUES ('24', '0', '0') "; 
        self::dbQuery($sql);

        // Shrine room 
        $sql = "INSERT INTO room (room_type, room_location, room_location_arg, room_side) VALUES ('23', '0', '0', '1') "; 
        self::dbQuery($sql);

        // Create specialists
        $types = array('A','B','C');
        for($i=0;$i<count($types);$i++ ) {
            $values = array();
            $sql = "INSERT INTO specialistandquest (specialistandquest_type, specialistandquest_type_arg, specialistandquest_location, specialistandquest_location_arg) VALUES ";

            $specialists = array_filter($this->specialist, function($elem) use($types,$i) { return $elem['cathegory'] == $types[$i];});
            $keys = array_keys ( $specialists );
            shuffle($keys);

            if ($players_nbr == 1 ) {       // for solo game remove Thug, Recruiter, Smuggler
                if (($key = array_search(9, $keys)) !== false) {
                    unset($keys[$key]);
                }

                if (($key = array_search(11, $keys)) !== false) {
                    unset($keys[$key]);
                }

                if (($key = array_search(34, $keys)) !== false) {
                    unset($keys[$key]);
                }

                $keys = array_values( $keys );
            }


            for($j=0;$j<($this->tokens_number['specialists'][$players_nbr]);$j++ ) {
                $values[] = "('specialist', '$keys[$j]', 'board','5')";
            }
            $sql .= implode( $values, ',' );
            self::DbQuery( $sql );
        }

        // add baggage
        $sql = "INSERT INTO specialistandquest (specialistandquest_type, specialistandquest_type_arg, specialistandquest_location, specialistandquest_location_arg) VALUES ";
        $sql .= "('specialist', '37', 'notplaced', '0')";
        self::DbQuery( $sql );

        // Create quests
        $types = array('1N','1S','Funeral','2','Council');
        $age1cards = array();

        if ($players_nbr == 1) {
            $quest_start_location = 6;
        } else {
            $quest_start_location = $players_nbr > 4 ? 6:5;                 // location !!!!!!!!!!!!!
        }
        for($i=0;$i<count($types);$i++ ) {
            $values = array();
            $sql = "INSERT INTO specialistandquest (specialistandquest_type, specialistandquest_type_arg, specialistandquest_location, specialistandquest_location_arg) VALUES ";

            $quests = array_filter($this->quest, function($elem) use($types,$i) { return $elem['cathegory'] == $types[$i];});
            $keys = array_keys ( $quests );
            shuffle($keys);

            if( count($keys) > 1 ) {
                for($j=0;$j<($this->tokens_number[$types[$i]][$players_nbr]);$j++ ) {
                    $values[] = "('quest', '$keys[$j]', 'board','$quest_start_location')";                                                                 
                }
            } else {
                $values[] = "('quest', '$keys[0]', 'board','$quest_start_location')";                                                          
            }

            if ($types[$i] == '1N') {
                $age1cards = $values;
            } elseif ( $types[$i] == '1S') {         // combine two types of age I cards together
                $age1cards = array_merge($age1cards, $values);
                shuffle($age1cards);
                $sql .= implode( $age1cards, ',' );
                self::DbQuery( $sql );
            } else {
                $sql .= implode( $values, ',' );
                self::DbQuery( $sql );
            }
        }

        // Create treasures
        $colors = array('blue','red','yellow');

        for($i=0;$i<count($colors);$i++ ) {
            $values = array();
            $sql = "INSERT INTO treasure (treasure_type, treasure_color, treasure_location, treasure_location_arg) VALUES ";

            $treasures = array_filter($this->treasures, function($elem) use($colors,$i) { return $elem['color'] == $colors[$i];});
            $keys = array_keys ( $treasures );
            $deck = array();

            foreach($keys as $key) {
                if ($players_nbr != 1) {
                    $addition = array_fill(0, $this->treasures[$key]['count'], $key);
                    $deck = array_merge($deck, $addition);
                } else {
                    if ($key != 2) {            // ommit spoiling potions for solo
                        $addition = array_fill(0, $this->treasures[$key]['count'], $key);
                        $deck = array_merge($deck, $addition); 
                    }
                }
            }

            shuffle($deck);

            for($j=0;$j<(count($deck));$j++ ) {
                // $values[] = "('$deck[$j]', '$colors[$i]', 'board','$i')";                       //!!!!!!!!!!!!!!!!
                $values[] = "('$deck[$j]', '$colors[$i]', 'board','$j')";
            }

            $sql .= implode( $values, ',' );
            self::DbQuery( $sql );
        }

        // Create other tokens (resource, sigils, thug)
        $types = array( array('iron', 'baseresource'),array('wood', 'baseresource'),array('leather', 'baseresource'),array('cloth', 'baseresource'),array('gem', 'advresource'), array('magic', 'advresource'));
        foreach($types as $key => $value) {
            $values = array();
            $sql = "INSERT INTO tokens (token_type, token_type_arg, token_location, token_location_arg) VALUES ";

            for($j=0;$j<($this->tokens_number[$value[1]][$players_nbr]);$j++ ) {
                $values[] = "('$value[1]', '$value[0]','board', '$key')";
            }

            $sql .= implode( $values, ',' );
            self::DbQuery( $sql );
        }

        foreach( $players as $player_id => $player ) {
            $values = array();
            $sql = "INSERT INTO tokens (token_type, token_type_arg, token_location, token_location_arg) VALUES ";
            for($i=0;$i<2;$i++ ){
                $values[] = "('sigil', '$player_id','free', '$i')";
            }
            $sql .= implode( $values, ',' );
            self::DbQuery( $sql );
        }

        $sql = "INSERT INTO tokens (token_type, token_type_arg, token_location, token_location_arg) VALUES ('thug', '', 'none', 'none')";
        self::DbQuery( $sql );


        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( 'expandAction_roomPlayed', 0 );
        self::setGameStateInitialValue( 'expandAction_specialistPlayed', 0 );
        self::setGameStateInitialValue( 'transition_from_state', 0 );
        self::setGameStateInitialValue( 'bonus_res_replace', 6 );
        self::setGameStateInitialValue( 'placed_specialist_type', 0 );
        self::setGameStateInitialValue( 'player_gain_treasure', -1);
        self::setGameStateInitialValue( 'craft_action_hero', 0);
        self::setGameStateInitialValue( 'specialist_craft_action_played', 0);
        self::setGameStateInitialValue( 'played_treasure_card', 0);
        self::setGameStateInitialValue( 'second_player_treasurePlay', -1);
        self::setGameStateInitialValue( 'alreadySelected_treasureCard', -1);
        self::setGameStateInitialValue('selected_treasure_card_discard', 0);
        self::setGameStateInitialValue( 'appraiser_active', 0);
        self::setGameStateInitialValue( 'player_play_appraiser', -1);
        self::setGameStateInitialValue( 'warlock_active', 0);
        self::setGameStateInitialValue( 'newQuestCardPosition', -1);
        self::setGameStateInitialValue('activePlayerAfterBidding',-1 );
        self::setGameStateInitialValue( 'offeringActive', 0);
        self::setGameStateInitialValue('playerPlayLast',-1 );
        self::setGameStateInitialValue('playerEndPhase', 0);
        self::setGameStateInitialValue('startPhase', 0);
        self::setGameStateInitialValue('soloExpandSecondPart', 0);
        self::setGameStateInitialValue('soloKingsFuneral', 0);

        // Init game statistics
        self::initStat( 'table', 'table_turnsNumber', 0 );    
        self::initStat( 'table', 'table_roomsBuilt', 0 );    
        self::initStat( 'table', 'table_specialistsHired', 0 );    
        self::initStat( 'table', 'table_treasureCardsPlayed', 0 );    
        self::initStat( 'table', 'table_treasureCardsSold', 0 );    
        self::initStat( 'table', 'table_resourceGathered', 0 );    
        self::initStat( 'table', 'table_itemsCrafted', 0 );    
        self::initStat( 'table', 'table_questsCompleted', 0 );   
        self::initStat( 'table', 'table_kingsStatue', 0 );    

        self::initStat( 'player', 'player_goldGained', 0 );  
        self::initStat( 'player', 'player_roomsBuilt', 0 );  
        self::initStat( 'player', 'player_specialistsHired', 0 );  
        self::initStat( 'player', 'player_treasureCardsPlayed', 0 );  
        self::initStat( 'player', 'player_treasureCardsSold', 0 );  
        self::initStat( 'player', 'player_resourceGathered', 0 );  
        self::initStat( 'player', 'player_itemsCrafted', 0 );  
        self::initStat( 'player', 'player_questsCompleted', 0 );  
        self::initStat( 'player', 'player_charmsSpoints', 0 );  
        self::initStat( 'player', 'player_relicsPoints', 0 );  

        self::initStat( 'player', 'player_questPoints', 0 );  
        self::initStat( 'player', 'player_specialistsPoints', 0 );  
        self::initStat( 'player', 'player_roomsPoints', 0 );  
        self::initStat( 'player', 'player_offeringPoints', 0 ); 

        // initial draw of specialists
        for($i=4;$i>-1;$i--) {
            $this->drawNewCard('specialist',null, $i);
        }
        // initial draw of quests
        if ($players_nbr == 1) {
            $quest_nbr = 6;
        } else {
            $quest_nbr = self::getPlayersNumber() > 4 ? 6:5 ;
        }
        
        for($i=0;$i<$quest_nbr;$i++) {
            $this->drawNewCard('quest', null, $i);
        }

        $players = self::loadPlayersBasicInfos();
        // distribute starting gold
        foreach( $players as $player_id => $player ) {
            $guild = $this->getPLayerGuild($player_id);

            // add gold according player order
            $this->updatePlayerGold($player_id, $this->tokens_number['startinggold'][$player['player_no']] );

            if($guild == 4) {  // add 4 gold
                $this->updatePlayerGold($player_id, 4);
            }
        }

        self::activeNextPlayer();
        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        $spectator = $this->isSpectator($current_player_id);

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score, player_mat mat, player_gold gold, player_hand_size handsize, player_guild guild FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
        $result['players'] = $this->getAdditionInfoFromType('guilds', $result['players'], array('name'));

        // rooms
        $sql = "SELECT room_id id, room_type type, room_location location, room_location_arg location_arg, room_side side FROM room ";
        $result['rooms'] = self::getCollectionFromDb( $sql );
        $additionalInfo = array('name', 'cathegory', 'doubleroom','two_sided','ability', 'nameTr', 'text');
        $result['rooms'] = $this->getAdditionInfoFromType('rooms', $result['rooms'], $additionalInfo);

        // specialists
        $sql = "SELECT specialistandquest_id id, specialistandquest_type_arg type, specialistandquest_location location, specialistandquest_location_arg location_arg, specialistandquest_visible visible, specialistandquest_discount discount FROM specialistandquest WHERE specialistandquest_type = 'specialist' ";
        $result['specialist'] = self::getCollectionFromDb( $sql );
        $result['specialist'] = $this->getAdditionInfoFromType('specialist', $result['specialist'], array('cathegory'));
        $additionalInfo = array('name', 'ability', 'nameTr', 'text');
        $result['specialist'] = $this->getAdditionInfoFromType('specialist', $result['specialist'], $additionalInfo, array('visible', 1) );

        // quests
        $sql = "SELECT specialistandquest_id id, specialistandquest_type_arg type, specialistandquest_location location, specialistandquest_location_arg location_arg, specialistandquest_visible visible FROM specialistandquest WHERE specialistandquest_type = 'quest' ";
        $result['quest'] = self::getCollectionFromDb( $sql );
        $result['quest'] = $this->getAdditionInfoFromType('quest', $result['quest'], array('cathegory'));
        $additionalInfo = array('name', 'items', 'cost', 'gold', 'reward', 'hero', 'nameTr', 'text');
        $result['quest'] = $this->getAdditionInfoFromType('quest', $result['quest'], $additionalInfo, array('visible', 1) );

        // trasures
        $sql = "SELECT treasure_id id, treasure_type type, treasure_color color, treasure_location location, treasure_location_arg location_arg, treasure_visible visible FROM treasure";
        $result['treasure'] = self::getCollectionFromDb( $sql );
        $additionalInfo = array('name', 'cathegory', 'effect', 'sellcost', 'text', 'nameTr');
        $result['treasure'] = $this->getAdditionInfoFromType('treasures', $result['treasure'], $additionalInfo, array('visible', 1) );
        $result['treasure'] = $this->filterTreasure( $result['treasure'], $current_player_id, array('type','name', 'cathegory', 'effect', 'sellcost', 'text', 'nameTr') );

        // rest tokens
        $sql = "SELECT token_id id, token_type type, token_type_arg type_arg, token_location location, token_location_arg location_arg FROM tokens ";
        $result['tokens'] = self::getCollectionFromDb( $sql );

        // player mat and mat bonuses
        if (!$spectator) {
            $result['mat'] = $this->playermats[$result['players'][$current_player_id]['mat']];
        }

        //for solo game
        $result['soloExpandSecondPart'] = self::getGameStateValue('soloExpandSecondPart');
        $result['soloKingsFuneral'] = self::getGameStateValue('soloKingsFuneral');

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression() {     // calculates number of quest cards left in the game
        $players_nbr = self::getPlayersNumber();
        $sql = "SELECT count(specialistandquest_id) FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_location = 'board' AND specialistandquest_visible = '0' ";
        $questLeft = self::getUniqueValueFromDB( $sql );
        $deduct = $players_nbr > 4 ? 6:5; // deduct initial played cards
        $allQuests = $this->tokens_number['1N'][$players_nbr]+ $this->tokens_number['1S'][$players_nbr]+ $this->tokens_number['2'][$players_nbr]+1-$deduct; // offering is excluded, when reached = 100%
        
        $result = (($allQuests-$questLeft)/$allQuests)*100;
        if ($result < 0) {
            return 0;
        } else {
            return $result;
        }
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function test() {
    }

    function isSpectator($current_player_id) {
        $players = self::getObjectListFromDB( "SELECT player_id id FROM player", true );;

        if (! in_array($current_player_id, $players)) {
            return true;
        }

        return false;
    }

    function getKeyByValueMultidim($array, $field, $value) {  //returns key by specific value in specific filed in multidimension (assoc) array
        foreach($array as $key => $element)
        {
            if ( $element[$field] === $value )
                return $key;
        }
        return false;
    }

    function getAdditionInfoFromType($set_name, &$set, $infos, $condition = null) {
        foreach($set as $key => $value) {
            if ($condition == null || $condition[1] == $set[$key][$condition[0]]) {
                foreach($infos as $info) {
                    if ($set_name != 'guilds') {
                        if (array_key_exists($info, $this->{$set_name}[$value['type']] ) ) {
                            $set[$key][$info] = $this->{$set_name}[$value['type']][$info];
                        } else {
                            $set[$key][$info] = null;
                        }
                    } else {
                        $set[$key]['guildname'] = $this->{$set_name}[$value['guild']][$info];
                    }
                }
            } else {
                $set[$key]['type'] = null;
            }
        }

        return $set;
    }

    function expandHandsize($player_id, $number) {
        self::notifyAllPlayers("expandHand", '', array(
            'player_id' => $player_id,
            'number' => $number,
        ) );
    }

    function filterTreasure(&$set, $player, $infos) {
        foreach($set as $key => $value) {
            if ($set[$key]['visible'] == 1 && $set[$key]['location'] != $player && $set[$key]['location'] != 'board' ) {
                if ( $set[$key]['location'] != 'discard') {
                    foreach($infos as $info) {
                        $set[$key][$info] = null;
                    }
                }
            }
        }

        return $set;
    }

    function getResTileOrNumId($res_type, $numId = false) {
        switch ($res_type) {
            case 'iron':
                return $numId ? 0 : array('baseresource', 0);
            break;
            case 'wood':
                return $numId ? 1 :  array('baseresource', 1);
            break;
            case 'leather':
                return $numId ? 2 :  array('baseresource', 2);
            break;
            case 'cloth':
                return $numId ? 3 :  array('baseresource', 3);
            break;
            case 'gem':
                return $numId ? 4 :  array('advresource', 0);
            break;
            case 'magic':
                return $numId ? 5 :  array('advresource', 1);
            break;
        }
    }

    function getResNameByNumId($numId) {
        switch ($numId) {
            case 0:
                return 'iron';
            break;
            case 1:
                return 'wood';
            break;
            case 2:
                return 'leather';
            break;
            case 3:
                return 'cloth';
            break;
            case 4:
                return 'gem';
            break;
            case 5:
                return 'magic';
            break;
        }
    }

    function getNumericCardColor($color) {
        switch($color) {
            case 'blue': 
                return 0;
            break;

            case 'red': 
                return 1;
            break;

            case 'yellow': 
                return 2;
            break;
        }
    }

    function getStringCardColor($num) {
        switch($num) {
            case 0: 
                return 'blue';
            break;

            case 1: 
                return 'red';
            break;

            case 2: 
                return 'yellow';
            break;
        }
    }

    function getResTypeById($res_id) {
        $sql = "SELECT token_type_arg t FROM tokens WHERE token_id = '$res_id' ";
        return self::getUniqueValueFromDB( $sql );
    }

    function getItemTypeById($type, $id) {
        if ($type != 'treasure') {
            $db_name = $type == 'room' ? 'room' : 'specialistandquest';
            $arg = $type == 'room' ? 'room_type' : 'specialistandquest_type_arg';
            $id_name = $type == 'room' ? 'room_id' : 'specialistandquest_id';

            $sql = "SELECT $arg t FROM $db_name WHERE $id_name = '$id' ";
            return self::getUniqueValueFromDB( $sql );
        } else {
            $sql = "SELECT treasure_type t FROM treasure WHERE treasure_id = '$id' ";
            return self::getUniqueValueFromDB( $sql );
        }
    }

    function getPlayerMat($player_id) {
        $sql = "SELECT player_mat mat FROM player WHERE player_id = '$player_id' ";
        return self::getUniqueValueFromDB( $sql );
    }

    function getPLayerGuild($player_id, $css = false) {
        $sql = "SELECT player_guild guild FROM player WHERE player_id = '$player_id' ";
        if ($css) {
            $n = $this->guilds[self::getUniqueValueFromDB($sql)]['name'];

            return 'bg-GuildBack'.str_replace(' ','',$n); 
        } else {
            return self::getUniqueValueFromDB($sql);
        }
    }

    function getPlayerName($player_id) {
        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        return self::getUniqueValueFromDB( $sql );
    }

    function getPlayerInfo($player_id) {
        $sql = "SELECT * FROM player WHERE player_id = '$player_id' ";
        return self::getObjectFromDB( $sql );
    }

    function getPlayerItems($item, $player_id) {
        $sql = "SELECT * FROM $item WHERE $item.'_location' = '$player_id' ";
        return self::getObjectFromDB( $sql );
    }

    static function getDbOutside($sql, $query) {
        return self::{$query}( $sql );
    }

    function combos($arr, $k) {
        if ($k == 0) {
          return array(array());
        }
     
        if (count($arr) == 0) {
          return array();
        }
     
        $head = $arr[0];
     
        $combos = array();
        $subcombos = $this->combos($arr, $k-1);
        foreach ($subcombos as $subcombo) {
          array_unshift($subcombo, $head);
          $combos[] = $subcombo;
        }
        array_shift($arr);
        $combos = array_merge($combos, $this->combos($arr, $k));
        return $combos;
    }

    function drawNewCard($cardtypename, $color, $location, $player_id = null)  {                  
        $tablename = $cardtypename == 'treasure' ? 'treasure' : 'specialistandquest';
        $tablenametype = $tablename."_type";
        $visible = $tablename."_visible";
        $loc = $tablename."_location";
        $locarg = $tablename."_location_arg";
        $typeid = $tablename."_id";
        $spectype = $tablename."_type_arg";
        if ($color == null) { 
            $sql = "SELECT min($typeid) id, $spectype spectype, $locarg locarg FROM $tablename WHERE $visible = 0 AND $tablenametype = '$cardtypename' AND $loc = 'board' ";
        } else { // treasure
            // $sql = "SELECT min($typeid) id, $tablenametype spectype FROM $tablename WHERE $visible = 0 AND treasure_color = '$color' ";                         //!!!!!!!!!!!! 
            $sql = "SELECT  $typeid id, $tablenametype spectype  FROM $tablename WHERE $visible = 0 AND treasure_color = '$color' AND treasure_location = 'board' ORDER BY treasure_location_arg ASC LIMIT 1";
        }
        $card = self::getObjectFromDB( $sql );

        // handle no more cards
        if ($cardtypename == 'specialist' && $card['id'] === null ) {        //no more specialists
            return null;
        }
        if ( $card === null && $cardtypename == 'treasure') {      // reshuffle treasure cards of given color, exclude scrolls                 /// no more cards to be reshuffled???
            $this->reshuffleDeck($color);
            $card = self::getObjectFromDB( $sql );
        }

        $id = $card['id'];
        if ($color == null) {
            $sql = "UPDATE $tablename SET $locarg = '$location', $visible = 1 WHERE $typeid = '$id' ";
        } else {
            $sql = "UPDATE $tablename SET treasure_location = '$player_id', $locarg = '$location', $visible = 1 WHERE $typeid = '$id' ";
        }
        self::dbQuery($sql);

        if ($cardtypename == 'specialist') {
            $log_msg = clienttranslate( 'New specialist: ${name}' );
        }

        if ($cardtypename == 'quest') {
            $log_msg = clienttranslate( 'New quest: ${name}' );
        }

        if ($tablename != 'treasure') {  
            $card_info =  $this->{$cardtypename}[$this->getItemTypeById($cardtypename, $id)]; 
            $card_info['type'] = $this->getItemTypeById($cardtypename, $id);    
            $card_info['visible'] = "1";    
            $card_info['location_arg'] = $location;                                                                  
            self::notifyAllPlayers( "drawCard", $log_msg, array(
                'card_type' => $cardtypename,
                'card_id' => $card['id'],
                'card_name' => $this->{$cardtypename}[$card['spectype']]['name'],
                'card_back' => $this->{$cardtypename}[$card['spectype']]['cathegory'],
                'location_from' => $card['locarg'],                                                           
                'location_to' => $location,
                'card_info' => $card_info,
                'name' => $this->{$cardtypename}[$card['spectype']]['nameTr'],
                'i18n' => array( 'name' ),
            ) );
        } else {
            $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
            $player_name  = self::getUniqueValueFromDB( $sql );

            self::notifyAllPlayers( "drawCard", clienttranslate( '${player_name} draws treasure card ${cardback}' ), array(
                'player_id' => $player_id,
                'player_name' => $player_name,
                'card_type' => $cardtypename,
                'card_id' => $card['id'],
                'color' => $color,
                'cardback' => 'treasure_'.$color,
            ) );

            $card_info =  $this->{$cardtypename.'s'}[$this->getItemTypeById($cardtypename, $id)]; 
            $card_info['type'] = $this->getItemTypeById($cardtypename, $id);    
            $card_info['visible'] = "1"; 
            $card_info['location'] = $player_id; 
            self::notifyPlayer($player_id, "drawCard", '', array(
                'player_id' => $player_id,
                'card_type' => $cardtypename,
                'card_id' => $card['id'],
                'card_name' => $this->{$cardtypename.'s'}[$card['spectype']]['name'],
                'card_back' => $color,
                'location_from' => $this->getNumericCardColor($color),
                'color' => $color,
                'location_to' => $location, 
                'card_info' => $card_info,                                                      
            ) );
        }

        if ($cardtypename == 'quest' &&  $this->{$cardtypename}[$card['spectype']]['name'] == "The King's Funeral" ) {
            return 'kingsFuneral';
        }
        if ($cardtypename == 'quest' &&  $this->{$cardtypename}[$card['spectype']]['name'] == "Offering to the Council" ) {
            return 'offering';
        }
        return null;
    }

    function reshuffleDeck($color) {
        $sql = "SELECT  treasure_id id, treasure_type spectype  FROM treasure WHERE treasure_color = '$color' AND treasure_location = 'discard' ";
        $discards = self::getCollectionFromDB( $sql );
        $cardsToshuffle = array();
        $typesToshuffle = array();
        // filter scrolls
        foreach ($discards as $card) {
            if ( !($this->treasures[$card['spectype']]['cathegory'] == 'Scroll')) {
                $cardsToshuffle[] = $card['id'];
                $typesToshuffle[] = $card['spectype'];
            }
        }

        if ( !empty($cardsToshuffle)) {
            shuffle($typesToshuffle);

            for($i=0;$i<count($cardsToshuffle);$i++) {
                $sql = "UPDATE treasure SET treasure_location = 'board', treasure_location_arg = '$i', treasure_visible = 0, treasure_type = '$typesToshuffle[$i]' WHERE treasure_id = '$cardsToshuffle[$i]' ";
                self::DbQuery( $sql );
            }

            $sql = "SELECT treasure_id id, treasure_type type, treasure_color color, treasure_location location, treasure_location_arg location_arg, treasure_visible visible FROM treasure WHERE treasure_color = '$color' AND treasure_location = 'board' ";
            $result['treasure'] = self::getCollectionFromDb( $sql );
            $additionalInfo = array('name', 'cathegory', 'effect', 'sellcost', 'text', 'nameTr');
            $result['treasure'] = $this->getAdditionInfoFromType('treasures', $result['treasure'], $additionalInfo, array('visible', 1) );
            $result['treasure'] = $this->filterTreasure( $result['treasure'], 0 , array('type','name', 'cathegory', 'effect', 'sellcost', 'text') );

            //notify
            self::notifyAllPlayers( "reshuffleDeck", clienttranslate( '${cardback} treasure deck reshuffled' ), array(
                'cardback' => 'treasure_'.$color,
                'cards' => $result['treasure'],
            ) );
        }
    }

    function placeRoomToPlayerGuild($room_id, $destination, $player_id, $mapper) { 
        $sql = "UPDATE room SET room_location = '$player_id', room_side = '1', room_location_arg = '$destination' WHERE room_id = '$room_id' ";
        self::dbQuery($sql);

        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        $player_name  = self::getUniqueValueFromDB( $sql );

        $sql = "SELECT room_type t FROM room WHERE room_id = '$room_id' ";
        $room_type  = self::getUniqueValueFromDB( $sql );

        if($this->rooms[$room_type]['two_sided'] != false ){
            $dualtype = array_values( array_diff($this->rooms[$room_type]['two_sided'],[intval($room_type)]) )[0];
            $sql = "SELECT max(room_id) id FROM room WHERE room_type = '$dualtype' AND room_location = 'board' ";
            $dualid  = self::getUniqueValueFromDB( $sql );

            $sql = "UPDATE room SET room_location = '$player_id', room_side = '0', room_location_arg = '$destination' WHERE room_id = '$dualid' ";
            self::dbQuery($sql);
        } else { $dualid = null; }

        $destination = $this->rooms[$room_type]['doubleroom'] ?  'doubletile_room_'.$destination.'_'.$player_id :  'tile_room_'.$destination.'_'.$player_id;

        self::notifyAllPlayers( "placeRoom", clienttranslate( '${player_name} places ${room_name} into guild' ), array(
            'player_id' => $player_id,
            'player_name' => $player_name,
            'room_id' => $room_id,
            'dual_id' => $dualid,
            'room_type' => $room_type,
            'destination' => $destination,
            'room_name' => $this->rooms[$room_type]['nameTr'],
            'i18n' => array('room_name'),
        ) );

        //check for Library ->expand hand
        if ( $this->rooms[$room_type]['ability'] != null && key( $this->rooms[$room_type]['ability'] ) == "handsize" ) {
            $add = $this->rooms[$room_type]['ability']['handsize'][1];
            $sql = "UPDATE player SET player_hand_size = player_hand_size + '$add' WHERE player_id = '$player_id' ";
            self::dbQuery($sql);
            $this->expandHandsize($player_id, $add);
        }

        // take gold from player
        $this->updatePlayerGold($player_id,-$this->rooms[$room_type]['value']);

        // update Score
        $score = $mapper->calculateScore();
        $scoreN = $score['specialists']+$score['rooms']+$score['quests'];
        self::DbQuery( "UPDATE player SET player_score='$scoreN' WHERE player_id= '$player_id'  " );
        self::notifyAllPlayers( "updateScore",'', array(
            'player_id' => $player_id,
            'value' => $scoreN,
            'inc' => false,
        ) );

        self::incStat( 1, 'player_roomsBuilt', $player_id );
        self::incStat( 1, 'table_roomsBuilt' );
    }

    function placeSpecialistToPlayerGuild($specialist_id, $destination, $player_id, $discount, $mapper) {
        $sql = "UPDATE specialistandquest SET specialistandquest_location = '$player_id', specialistandquest_location_arg = '$destination', specialistandquest_visible = 1 WHERE specialistandquest_id = '$specialist_id' ";
        self::dbQuery($sql);

        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        $player_name  = self::getUniqueValueFromDB( $sql );
        
        $specialist_type  = $this->getItemTypeById('specialist', $specialist_id);

        $destination =  'tile_specialist_'.$destination.'_'.$player_id;

        // Inventor -> update handsize
        if ( $this->specialist[$specialist_type]['name'] == 'Inventor' ) {
            $sql = "UPDATE player SET player_hand_size = player_hand_size + 2 WHERE player_id = '$player_id' ";
            self::dbQuery($sql);

            $this->expandHandsize($player_id, 2);
        }

        self::notifyAllPlayers( "placeSpecialist", clienttranslate( '${player_name} places ${specialist_name} into guild' ), array(
            'player_id' => $player_id,
            'player_name' => $player_name,
            'specialist_id' => $specialist_id,
            'destination' => $destination,
            'specialist_name' => $this->specialist[$specialist_type]['nameTr'],
            'i18n' => array('specialist_name'),
        ) );

        //if thug update thug icon and place on board
        if ($specialist_type == 11) {
            $sql = "UPDATE tokens SET token_type_arg = '$player_id', token_location = 'specialist', token_location_arg = '$specialist_id' WHERE token_type = 'thug' ";   // !!!!!!!!!!!
            self::dbQuery($sql);

            self::notifyAllPlayers( "moveThug",'', array(
                'player_id' => $player_id,
                'move_back' => true,
            ) );
        }

        // take gold from player
        $this->updatePlayerGold($player_id,-$this->specialist[$specialist_type]['value']+$discount);

        // update Score
        $score = $mapper->calculateScore();
        $scoreN = $score['specialists']+$score['rooms']+$score['quests'];
        self::DbQuery( "UPDATE player SET player_score='$scoreN' WHERE player_id= '$player_id'  " );
        self::notifyAllPlayers( "updateScore",'', array(
            'player_id' => $player_id,
            'value' => $scoreN,
            'inc' => false,
        ) );

        self::incStat( 1, 'player_specialistsHired', $player_id );
        self::incStat( 1, 'table_specialistsHired' );
    }

    function updateSpecialistsOnBoard($free_spaces, $discount, $nbr) { 
        $new_spaces = is_array($free_spaces) ? $free_spaces: [$free_spaces];
        $free_space = array_pop($new_spaces);

        $sql = "SELECT specialistandquest_id id, specialistandquest_location_arg loc, specialistandquest_discount discount, specialistandquest_type_arg t FROM specialistandquest WHERE specialistandquest_type = 'specialist' AND specialistandquest_location = 'board' AND specialistandquest_visible = '1' ";
        $specialists = self::getObjectListFromDB( $sql);  

        foreach($specialists as $specialist) {
            $id = $specialist['id'];
            if (intval($specialist['loc']) < intval($free_space) ) {
                $new_loc = intval($specialist['loc'])+1;
                $sql = "UPDATE specialistandquest SET specialistandquest_location_arg = '$new_loc' WHERE specialistandquest_id = '$id' ";
                self::dbQuery( $sql ); 
                $destination = $new_loc; 
                
                self::notifyAllPlayers( "moveSpecialist",  '', array(
                    'specialist_id' => $id,
                    'destination' => $destination,
                    'destroy' => false,
                ) );
            } else {
                // add gold to that specialist if full prize 
                if ($discount == 0) {
                    if ( $specialist['discount']+1 == $this->specialist[$specialist['t']]['value'] ) {
                        // remove specialist from game
                        $sql = "UPDATE specialistandquest SET specialistandquest_location= 'removed' WHERE specialistandquest_id = '$id' ";
                        self::dbQuery( $sql ); 

                        self::notifyAllPlayers( "moveSpecialist",  '', array(
                            'specialist_id' => $id,
                            'destination' => 'main_board',
                            'destroy' => true,
                        ) );
                        $new_spaces[] = intval($specialist['loc']);
                    } else {
                        //give extra discount
                        $sql = "UPDATE specialistandquest SET specialistandquest_discount= specialistandquest_discount+1 WHERE specialistandquest_id = '$id' ";
                        self::dbQuery( $sql ); 

                        self::notifyAllPlayers( "updateSpecDiscount",  '', array(
                            'specialist_id' => $id,
                        ) );
                    }
                }
            }
        }

        rsort($new_spaces);

        if ( empty($new_spaces) ) {
            return $nbr;
        } else {
            return $this->updateSpecialistsOnBoard($new_spaces,1, $nbr+1);
        }
    }

    function soloGameExpandActionEnd() {
        $sql = "SELECT specialistandquest_id id, specialistandquest_location_arg loc FROM specialistandquest WHERE specialistandquest_type = 'specialist' AND specialistandquest_location = 'board' AND specialistandquest_visible = '1' ";
        $specialists = self::getObjectListFromDB( $sql); 
        $specialistsToMove = array();
        $occupiedLocations = array();
        $allLocations = array(0,1,2,3,4);

        foreach($specialists as $specialist) {
            $spec_id = $specialist['id'];
            if (intval($specialist['loc']) == 4 ) { // discard rightmost specialist
                $sql = "UPDATE specialistandquest SET specialistandquest_location= 'removed' WHERE specialistandquest_id = '$spec_id' ";
                self::dbQuery( $sql ); 
    
                self::notifyAllPlayers( "moveSpecialist",  '', array(
                    'specialist_id' => $spec_id,
                    'destination' => 'main_board',
                    'destroy' => true,
                ) );
            } else {
                $occupiedLocations[] = intval($specialist['loc']);
                $specialistsToMove[] = $specialist;
            }
        }

        $freelocations = array_values(array_diff($allLocations, $occupiedLocations));

        foreach($specialistsToMove as $specialist) {
            $tilesToMove = 0;
            $id = $specialist['id'];
            foreach ($freelocations as $value) {
                if ($value > intval($specialist['loc']) ) {
                    $tilesToMove++;
                }
            }
            $destination = $tilesToMove+intval($specialist['loc']); 
            $sql = "UPDATE specialistandquest SET specialistandquest_location_arg = '$destination' WHERE specialistandquest_id = '$id' ";
            self::dbQuery( $sql );      

            self::notifyAllPlayers( "moveSpecialist",  '', array(
                    'specialist_id' => $id,
                    'destination' => $destination,
                    'destroy' => false,
            ) );
        }

        for ($i=count($freelocations)-1;$i>-1;$i--) {
            $this->drawNewCard('specialist', null, $i);
        }
    }

    function takeResourceByPlayer($resource_type, $player_id, $takeAsBonus, $resource_id = null) {  
        if ($resource_id === null) {
            $id = array();
        } else {$id = $resource_id; }

        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        $player_name  = self::getUniqueValueFromDB( $sql );
        $mapper = new kgActionMapper($player_id, $this);
        $free_positions = $mapper->getPositionForResource(); 
        $parameters = array();
        $destinations = array();
        $resourceList = array();

        if ($takeAsBonus == false) {
            $msg = clienttranslate('${player_name} takes ${resourceList}');
        } else {
            $msg = clienttranslate('${player_name} takes bonus ${resourceList}');
        }

        for($i=0;$i<count($resource_type);$i++) {
            if ($resource_id === null) {
                $res_type = $this->getResTileOrNumId($resource_type[$i]);
                $sql = "SELECT MIN(token_id) FROM tokens WHERE token_type = '$res_type[0]' AND token_type_arg = '$resource_type[$i]' AND token_location = 'board' ";
                $id[] = self::getUniqueValueFromDB($sql);
            }

            $sql = "UPDATE tokens SET token_location = '$player_id', token_location_arg = '$free_positions[$i]' WHERE token_id = '$id[$i]' ";
            self::dbQuery($sql);
            $resourceList[] =  'resource_'.$resource_type[$i];
            $destinations[] = 'tile_storage_'.$free_positions[$i].'_'.$player_id;
        }

        $parameters['player_id'] = $player_id;  $parameters['player_name'] = $player_name; $parameters['destination'] = $destinations;
        $parameters['resource_type'] = $resource_type; $parameters['resource_id'] = $id;
        $parameters['resourceList'] = $resourceList;
        self::notifyAllPlayers( "takeResource", $msg, $parameters );
    }

    function returnResourceByPlayer($resource_type, $player_id, $resource_id = null, $form) {
        if ($resource_id === null) {
            $id = array();
        } else {$id = $resource_id; }

        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        $player_name  = self::getUniqueValueFromDB( $sql );
        $parameters = array();
        $destinations = array();
        $resourceList = array();

        if ($form == 'pay') {
            $msg = clienttranslate('${player_name} uses ${resourceList}');
        }
        if ($form == 'return') {
            $msg = clienttranslate('${player_name} returns ${resourceList}');
        }
        if ($form == 'loss') {
            $msg = clienttranslate('${player_name} loses ${resourceList}');
        }

        for($i=0;$i<count($resource_type);$i++) {
            $res_type =$this->getResTileOrNumId($resource_type[$i]);
            if ($resource_id === null) {
                $res_type = $this->getResTileOrNumId($resource_type[$i]);
                $sql = "SELECT MIN(token_id) FROM tokens WHERE token_type = '$res_type[0]' AND token_type_arg = '$resource_type[$i]' AND token_location = '$player_id' ";
                $id[] = self::getUniqueValueFromDB($sql);
            }

            $position = 0;
            $sql = "UPDATE tokens SET token_location = 'board', token_location_arg = '$position' WHERE token_id = '$id[$i]' ";
            self::dbQuery($sql);
            $resourceList[] =  'resource_'.$resource_type[$i];
            $destinations[] = 'tile_'.$res_type[0].'_'.$res_type[1];
        }

        $parameters['player_id'] = $player_id;  $parameters['player_name'] = $player_name; $parameters['destination'] = $destinations;
        $parameters['resource_type'] = $resource_type; $parameters['resource_id'] = $id;
        $parameters['resourceList'] = $resourceList;
        self::notifyAllPlayers( "returnResource", $msg, $parameters );
    }

    function updatePlayerGold($player_id,$value, $notify = false) {
        if ($value == 0) { return;}

        $sql = "UPDATE player SET player_gold = player_gold + $value WHERE player_id = '$player_id' ";
        self::dbQuery($sql);

        if ($notify) {
            if ($value > 0) {
                $msg = clienttranslate( '${player_name_id} gets ${gold}' );
                self::incStat( $value, 'player_goldGained', $player_id );
            } else {
                $msg = clienttranslate( '${player_name_id} pays ${gold}' );
            }
        } else {
            $msg = '';
        }

        self::notifyAllPlayers( "updateGold", $msg, array(
            'player_id' => $player_id,
            'player_name_id' => $player_id,
            'value' => $value,
            'gold' => 'gold_'.abs($value),
        ) );
    }

    function updateReplaceRes($player_id ) {
        $sql = "SELECT player_replace_res res FROM player WHERE player_id = '$player_id' ";
        $res_string = self::getUniqueValueFromDB($sql);
        if( substr( $res_string, -1 ) == '_' ) {
            $res_string = substr( $res_string, 0, -1 );
          }
        $res_array = explode("_", $res_string);
        array_pop($res_array);

        $res_string = implode("_", $res_array);

        if ($res_string > 0) {
            $sql = "UPDATE player SET player_replace_res = $res_string WHERE player_id = '$player_id' ";
            $res_string = self::dbQuery($sql);
        } else {
            $sql = "UPDATE player SET player_replace_res = '__' WHERE player_id = '$player_id' ";
            $res_string = self::dbQuery($sql);
        }
    }

    function playerCraftItem($quest_id, $item_id, $quest_type, $player_id, $craftBoth, $mapper, $thug) {
        $items_nbr = count($this->quest[$quest_type]['items']);
        $second_id = $item_id == 0 ? 1:0;
        $player_alsoCrafted = null;

        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        $player_name  = self::getUniqueValueFromDB( $sql );

        if ($items_nbr == 1) {          // only one item on quest -> complete quest
            //update quest card in db
            $sql = "UPDATE specialistandquest SET specialistandquest_location = '$player_id' WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$quest_id' ";
            self::dbQuery( $sql); 
            
            self::incStat( 1, 'player_questsCompleted', $player_id );
            self::incStat( 1, 'table_questsCompleted' ); 

            $completed = true;
        } else {  // two items on quest -> finish or partly complete
            // find if sigil is present
            $sigil_loc = 'quest_'.$quest_id;
            $sql = "SELECT token_type_arg player, token_location_arg item_id, token_id sigil_id FROM tokens WHERE token_type = 'sigil' AND token_location = '$sigil_loc' ";
            $sigil = self::getObjectFromDB( $sql); 

            if ($sigil != null) {              // one of two items already crafted
                if ( $sigil['item_id'] == $item_id || ( $sigil['item_id'] == $second_id && $craftBoth) ) {
                    throw new BgaUserException( self::_("Item already crafted, hit  F5 to update") );
                }
                $sigil_id = $sigil['sigil_id'];
                $player_alsoCrafted = $sigil['player'];
                $completed = true;

                //update quest card in db
                $sql = "UPDATE specialistandquest SET specialistandquest_location = '$player_alsoCrafted' WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$quest_id' ";
                self::dbQuery( $sql);
                
                self::incStat( 1, 'player_questsCompleted', $player_id );
                self::incStat( 1, 'table_questsCompleted' ); 

                $sql = "SELECT token_id id FROM tokens WHERE token_type = 'sigil' AND token_type_arg = '$player_id' AND token_location = 'free'  ";
                $loc_arg =   empty(self::getCollectionFromDB( $sql)) ? 0:1; 

                //update sigil
                $sql = "UPDATE tokens SET token_location = 'free', token_location_arg = '$loc_arg' WHERE token_id = '$sigil_id' ";                       
                self::dbQuery( $sql); 
            } else {                    // not yet crafted anything
                    if($craftBoth) {
                        $sql = "UPDATE specialistandquest SET specialistandquest_location = '$player_id' WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$quest_id' ";
                        self::dbQuery( $sql); 

                        $completed = true;
                    } else {        //check if sigil available 
                        $sql = "SELECT token_id id FROM tokens WHERE token_type = 'sigil' AND token_type_arg = '$player_id' AND token_location = 'free'  ";
                        $sigils = self::getCollectionFromDB( $sql); 
                        if ( empty($sigils) ) {
                            throw new BgaUserException( self::_("You don't have free sigil to craft this item") );
                        }

                        $sigil_id = array_keys($sigils)[0];
                        //update sigil
                        $sigil_loc = 'quest_'.$quest_id;
                        $sql = "UPDATE tokens SET token_location = '$sigil_loc', token_location_arg = '$item_id' WHERE token_id = '$sigil_id' ";
                        self::dbQuery( $sql); 

                        $completed = false;
                    }
            }
        }

        $id_array = $craftBoth ? array($item_id+1, $second_id+1) : array($item_id+1);

        // check for fortune potion
        $doublegold = false;
        $sql = "SELECT player_active_potions ap FROM player WHERE player_id = '$player_id' ";
        $potions = self::getUniqueValueFromDb($sql); 
        if ($potions != null) {
            $potions_array = explode("_", $potions);
            if (in_array(31, $potions_array)) {
                $doublegold = true;
            }
        }

        // thug resolve 
        if ($thug != null) {
            if ($player_id != $thug) {
                $this->updatePlayerGold($player_id, -2);
                $this->updatePlayerGold($thug, 2);
                self::incStat( 2, 'player_goldGained', $thug );
                self::notifyAllPlayers( "logInfo",clienttranslate( '${player_name_id} pays ${gold} to ${player2_name_id} (Thug)' ), array(
                    'player_name_id' => $player_id,
                    'player2_name_id' => $thug,
                    'gold' => 'gold_2',
                ) );
            }

            if ($completed) {
                $thugId = self::getUniqueValueFromDb("SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'specialist' AND specialistandquest_type_arg = '11' " ); 
                $sql = "UPDATE tokens SET token_location = 'specialist', token_location_arg = '$thugId' WHERE token_type = 'thug' ";   
                self::dbQuery($sql);
                // return thug item
                self::notifyAllPlayers( "moveThug",'', array(
                    'move_back' => true,
                ) );
            }
        }

        for ($i=0;$i< count($id_array);$i++ ) {                                                                             //!!!!!!!!!!!!!!!!!!!!!!!!!
            $item_cost = $this->quest[$quest_type]['cost'][$id_array[$i]];
            $item_gold = $this->quest[$quest_type]['gold'][$id_array[$i]];

            $discount = $mapper->getCraftDiscount();
            $reduce = array();
            if ( $discount[ $this->quest[$quest_type]['items'][$id_array[$i]][1] ] != null ) {
                $reduce = $discount[ $this->quest[$quest_type]['items'][$id_array[$i]][1] ];
            } 
            // pay resources
            $resToPay = array();
            foreach($item_cost as $resource => $number) {
                // adjust specialist discount
                $res_count = in_array($resource, $reduce) ? $number-1: $number;
                for($j=0;$j<$res_count;$j++) {
                    // $this->returnResourceByPlayer($resource, $player_id);  
                    $resToPay[] = $resource;  
                }
            }
            $this->returnResourceByPlayer($resToPay, $player_id, null, 'pay'); 

            // notify players
            $notif_completed = $i+1 == count($id_array) ? $completed : null;
            $notif_sigil_return = $i+1 == count($id_array) && $player_alsoCrafted != null  ? "sigil_".$player_alsoCrafted."_".$sigil_id : null;
            $notif_sigil_add = ($items_nbr>1 && !$completed) ? "sigil_".$player_id."_".$sigil_id : null;

            if ($doublegold) {      
                $msg = clienttranslate( '${player_name} craft ${item} and gets ${gold} (Fortune Potion applies)' );
                // reward gold
                $this->updatePlayerGold($player_id, $item_gold*2);
                self::incStat( $item_gold*2, 'player_goldGained', $player_id );
                $goldstring = 'gold_'.($item_gold*2);
                $doublegold = false;
                // update active potion string in db
                if (($key = array_search(31, $potions_array)) !== false) {
                    unset($potions_array[$key]);
                    $potions_array = array_values($potions_array);

                    $potion_string = implode("_", $potions_array);
                    $sql = "UPDATE player SET player_active_potions = '$potion_string ' WHERE player_id = '$player_id' ";
                    self::dbQuery( $sql); 
                }

            } else {
                $msg = clienttranslate( '${player_name} craft ${item} and gets ${gold}' );
                // reward gold
                $this->updatePlayerGold($player_id, $item_gold);
                self::incStat( $item_gold, 'player_goldGained', $player_id );
                $goldstring = 'gold_'.$item_gold;
            }

            self::notifyAllPlayers( "craftItem", $msg, array(
                'player_id' => $player_id,
                'player_name' => $player_name,
                'item' => $this->quest[$quest_type]['items'][$id_array[$i]][0], 
                'i18n' => array('item'),
                'item_id' => $id_array[$i]-1,                           
                'gold' => $goldstring,
                'quest_id' => $quest_id,
                'quest_completed' => $notif_completed,
                'sigilForReturn_player' => $notif_sigil_return,
                'sigilToAdd' => $notif_sigil_add,
            ) );

            self::incStat( 1, 'player_itemsCrafted', $player_id );
            self::incStat( 1, 'table_itemsCrafted' ); 
        }

        return array('quest_completed' => $completed , 'player_alsoCrafted' => $player_alsoCrafted);
    }

    function preparePlayerForSelling($player_id, $number_to_sell) {
        self::notifyPlayer( $player_id, "sellTreasureMenu", '', array(
            'player_id' => $player_id,
            'item_number' => $number_to_sell,
        ) );
    }

    function playerSellTreasure($treasure_id, $player_id, $treasure_info = null, $sellOnly = true, $discard = false) {
        $mapper = new kgActionMapper($player_id, $this);
        
        // get location of card in hand
        if ($treasure_info === null ) {
            $sql = "SELECT treasure_location_arg locarg, treasure_type spectype FROM treasure WHERE  treasure_id = '$treasure_id' AND treasure_location = '$player_id' ";
            $treasure_info = self::getObjectFromDB( $sql );
        }

        // update new location
        $sql = "UPDATE treasure SET treasure_location = 'discard', treasure_location_arg = 0 WHERE  treasure_id = '$treasure_id' ";
        self::dbQuery( $sql );

        if ($sellOnly) {
            $auctioneerSell = false;
            // Auctioneer check
            if ( $mapper->isSpecificSpecialistPresent('Auctioneer') ) {
                $treasure_type = $this->treasures[$treasure_info['spectype']]['cathegory'];
                if ($treasure_type == 'Scroll' || $treasure_type ==  'Relic' || $treasure_type ==  'Charm' ) {
                    $auctioneerSell = true;
                }
            }

            //give player gold
            if ($auctioneerSell) {
                $this->updatePlayerGold($player_id, 4);         // Auctioneersell
                self::incStat( 4, 'player_goldGained', $player_id );
            } else {
                $this->updatePlayerGold($player_id, $this->treasures[$treasure_info['spectype']]['sellcost']);
                self::incStat( $this->treasures[$treasure_info['spectype']]['sellcost'], 'player_goldGained', $player_id );
            }
        }

        //notify
        $additionalInfo = array('name', 'cathegory', 'effect', 'sellcost', 'text', 'color', 'nameTr');
        $treasure['id'] = $treasure_id;
        $treasure['type'] = $treasure_info['spectype'];
        $treasure['location'] = 'discard';
        $treasure['visible'] =1;
        $info = array($treasure);

        if ($sellOnly && !$discard) {
            if ($auctioneerSell) {
                $msg = clienttranslate( '${player_name_id} sells ${cardback} (${cardname}) and gets ${gold} (Auctioneer)' );
                $gold = 'gold_4';      
            } else {
                $msg = clienttranslate( '${player_name_id} sells ${cardback} (${cardname}) and gets ${gold}' );
                $gold = 'gold_'.$this->treasures[$treasure_info['spectype']]['sellcost']; 
            }

            self::incStat( 1, 'player_treasureCardsSold', $player_id );
            self::incStat( 1, 'table_treasureCardsSold' );  
        } elseif ($discard) {
            $msg = clienttranslate( '${player_name_id} discards ${cardback} (${cardname})' );
            $gold = '';
        } else {
            $msg = clienttranslate( '${player_name_id} plays effect of ${cardback} (${cardname})' );
            $gold = '';
            self::incStat( 1, 'player_treasureCardsPlayed', $player_id );
            self::incStat( 1, 'table_treasureCardsPlayed' );  
        }

        self::notifyAllPlayers( "sellTreasure", $msg, array(
            'player_id' => $player_id,
            'player_name_id' => $player_id,
            'cardback' => 'treasure_'.$this->treasures[$treasure_info['spectype']]['color'],                      
            'gold' => $gold,
            'treasure_id' => $treasure_id,
            'treasure_info' => $this->getAdditionInfoFromType('treasures', $info, $additionalInfo)[0],
            'cardname' => $this->treasures[$treasure_info['spectype']]['nameTr'],
            'i18n' => array('cardname'),
        ) );

        //reorganize cards in hand
        if ($treasure_info['locarg'] < 20) {
            $this->updateHand($player_id, $treasure_info['locarg']);
        }
    }

    function updateHand($player_id, $free_space, $specific_treasure_id = null) {
        $sql = "SELECT player_hand_size hs  FROM player WHERE player_id = '$player_id' ";
        $hs = self::getUniqueValueFromDB($sql);

        if ($specific_treasure_id == null ) {
            for($i=$free_space+1;$i<$hs;$i++) {
                $sql = "SELECT treasure_id id FROM treasure  WHERE treasure_location_arg = '$i' AND treasure_location = '$player_id' ";
                $treasure_id = self::getUniqueValueFromDB( $sql );
                if ($treasure_id != null) {
                    $sql = "UPDATE treasure SET treasure_location_arg = treasure_location_arg -1 WHERE  treasure_id = '$treasure_id'";
                    self::dbQuery( $sql );
                    //notify
                    self::notifyPlayer( $player_id, "updateHand", '', array(
                        'new_location' => 'tile_card_'.($i-1),
                        'treasure_id' => $treasure_id,
                    ) );
                }
            }
        } else {
            $sql = "UPDATE treasure SET treasure_location_arg = '$free_space' WHERE  treasure_id = '$specific_treasure_id'";
            self::dbQuery( $sql );

            self::notifyPlayer( $player_id, "updateHand", '', array(
                'new_location' => 'tile_card_'.$free_space,
                'treasure_id' => $specific_treasure_id,
            ) );
        }
    }

    function playerPlayTreasureEffect($treasure_id, $player_id, $treasure_info, $warlock = false) {
        $effect = $this->treasures[$treasure_info['spectype']]['effect'];
        switch ( array_keys($effect)[0] ) {
            case "gain":
                $mapper = new kgActionMapper($player_id, $this);
                if ( $effect["gain"][0] == 'gold' ) {               //warlock
                    if ($warlock) {
                        $this->playerSellTreasure($treasure_id, $player_id, $treasure_info, false, true);           // destroy completely !!
                        $this->updatePlayerGold($player_id, $effect["gain"][1], true);
                        $this->updatePlayerGold($player_id, $effect["gain"][2], true);

                    } else {
                        if (self::getPlayersNumber() == 1) {
                            $this->playerSellTreasure($treasure_id, $player_id, $treasure_info, false);
                            $this->updatePlayerGold($player_id, $effect["gain"][1], true);
                        } else {
                            $this->playerSellTreasure($treasure_id, $player_id, $treasure_info, false);
                            $this->updatePlayerGold($player_id, $effect["gain"][1], true);
                            $this->updatePlayerGold(self::getPlayerAfter( $player_id ), $effect["gain"][2], true);
                        }
                    }

                    self::giveExtraTime( $player_id);
                    $this->gamestate->nextState( 'playTreasureNoAction' );
                } else {
                    $res_to_gain = $effect["gain"];

                    $res_by_type = array();
                    for ($i=0;$i<count($effect["gain"]);$i++) {         // check for availability of resources
                        $res = $effect["gain"][$i];
                        $res_by_type[$res] =  isset($res_by_type[$res]) ?  $res_by_type[$res]+1 : 1;
                    }

                    $res_unavailable = array();
                    foreach($res_by_type as $res => $number) {
                        $sql = "SELECT count(token_id) c FROM tokens WHERE token_type_arg = '$res' AND token_location = 'board' ";
                        $result = self::getUniqueValueFromDB( $sql );
                        if ($result < $number  ) {
                            for ($i=0;$i<($number-$result);$i++) {       // if not available, reduce amount to gain 
                                if (($key = array_search($res, $res_to_gain)) !== false) {
                                    unset($res_to_gain[$key]);
                                }
                                $res_unavailable[$res] = $result;
                            }
                        }
                    }

                    $res_to_gain = array_values($res_to_gain);

                    if (count($res_to_gain) < 1) {
                        // if not available inform
                        self::notifyAllPlayers("logInfo", clienttranslate( 'No available resources, treasure card has no effect' ), array(
                        ) );
                    } else {
                        if ( $mapper->checkGather($res_to_gain, true)['triggerReplace'] > 0 ) { // res will trigger replace action
                            // store played treasure to db 
                            self::setGameStateValue( 'played_treasure_card', $treasure_id);
                            $string = implode("_", $res_to_gain);
                            $sql = "UPDATE player SET player_replace_res = '$string' WHERE player_id = '$player_id' ";
                            self::dbQuery($sql);
                            // switch to replace state!!!!!!
                            $this->gamestate->nextState( 'playTreasure' );

                        } else {  
                            // update treasure card and notify
                            $this->playerSellTreasure($treasure_id, $player_id, $treasure_info, false);

                            // for ($i=0;$i<count($res_to_gain);$i++) {
                            //     $this->takeResourceByPlayer($res_to_gain[$i], $player_id, false);
                            // }
                            $this->takeResourceByPlayer($res_to_gain, $player_id, false);
                            self::giveExtraTime( $player_id);
                            $this->gamestate->nextState( 'playTreasureNoAction' );
                        }
                    }
                }
            break;

            case "gain2resource":                   //warlock
                // store played treasure to db 
                self::setGameStateValue( 'played_treasure_card', $treasure_id);
                if ($warlock) {
                    self::setGameStateValue( 'second_player_treasurePlay', $player_id );
                    self::setGameStateValue( 'warlock_active', 1);
                } else {
                    if (self::getPlayersNumber() != 1) {
                        self::setGameStateValue( 'second_player_treasurePlay', self::getPlayerAfter( $player_id ) );
                    }
                }

                $this->gamestate->nextState( 'playTreasure' );
            break;

            case "gainAndDraw":                     //warlock
                // store played treasure to db 
                self::setGameStateValue( 'played_treasure_card', $treasure_id);
                if ($warlock) {
                    self::setGameStateValue( 'second_player_treasurePlay', $player_id );
                    self::setGameStateValue( 'warlock_active', 1);
                } else {
                    if (self::getPlayersNumber() != 1) {
                        self::setGameStateValue( 'second_player_treasurePlay', self::getPlayerAfter( $player_id ) );
                    }
                }
                $this->gamestate->nextState( 'playTreasure' );
            break;

            case "drop":
                // store played treasure to db 
                self::setGameStateValue( 'played_treasure_card', $treasure_id);
                $this->gamestate->nextState( 'playTreasure' );
            break;

            case "luckyPotion":
                // store active potion to db
                $string = $treasure_info['spectype']."_";
                $sql = "UPDATE player SET player_active_potions = CONCAT(IFNULL(player_active_potions,''), '$string')  WHERE player_id = '$player_id' ";
                self::dBQuery($sql);
                $this->playerSellTreasure($treasure_id, $player_id, $treasure_info, false);
                $this->gamestate->nextState( 'playTreasureNoAction' );
            break;

            case "fortunePotion":
                // store active potion to db
                $string = $treasure_info['spectype']."_";
                $sql = "UPDATE player SET player_active_potions = CONCAT(IFNULL(player_active_potions,''), '$string')  WHERE player_id = '$player_id' ";
                self::dBQuery($sql);
                $this->playerSellTreasure($treasure_id, $player_id, $treasure_info, false);
                $this->gamestate->nextState( 'playTreasureNoAction' );
            break;

            case "hireSpecialist":   
                $mapper = new kgActionMapper($player_id, $this);
                
                // check space for specialist hiring
                $positions = $mapper->getPossiblePositionsForSpecialist();
                if ( empty($positions) ) {
                    throw new BgaUserException( self::_("You need at least 1 free specialist place to play Contract") );
                }

                // check if hire is possible (enough money for at least one specialist)
                $canbuild = false;
                $sql = "SELECT specialistandquest_id id FROM specialistandquest  WHERE specialistandquest_type = 'specialist' AND specialistandquest_location = 'board' AND specialistandquest_visible = '1' ";
                $specialistOnBoard= self::getObjectListFromDB( $sql, true );

                for($i=0;$i<count($specialistOnBoard);$i++) {
                    if( $mapper->canBuildItem('specialist', $specialistOnBoard[$i]) ) {
                        $canbuild = true;
                        break;
                    }
                }

                if (!$canbuild) {
                    throw new BgaUserException( self::_("You can't hire any specialist") );
                }

                // store active potion to db
                self::setGameStateValue( 'played_treasure_card', $treasure_id);
                $this->gamestate->nextState( 'playTreasure' );
            break;

            case "discardTreasure":                         
                // store active potion to db
                self::setGameStateValue( 'played_treasure_card', $treasure_id);
                $this->gamestate->nextState( 'playTreasure' );
            break;

        }
    }

    function resolveOneTimeBonus($player_id, $bonusarray, $mapper) {
        $draw_result = $mapper->getPositionForTreasureCards(count($bonusarray));
        $result = true;

        if ($draw_result['needToSell'] > 0) {
            $this->preparePlayerForSelling($player_id, count($bonusarray));
            $result = false;
        }

        for ($i=0;$i<count($bonusarray);$i++) {
            $this->drawNewCard('treasure', $bonusarray[$i], $draw_result['positions'][$i], $player_id);
        }
        self::setGameStateValue('placed_specialist_type',0);
        return $result;
    }

    function updateAfterCraftActions($player_id) {
        $sql = "SELECT player_specialist_craftaction speccraft FROM player WHERE player_id = '$player_id' ";
        $specialist_craftaction = array_slice(explode("_", self::getUniqueValueFromDB($sql)), 1);
        $specialist_craftaction = implode("_", $specialist_craftaction);
        $sql = "UPDATE player SET player_specialist_craftaction = '$specialist_craftaction' WHERE player_id = '$player_id' ";
        self::dbQuery($sql);
    }

    function createScoreTable($score, $players) {
        $table = array();
        $firstRow = array( array( 'str' => '${emp}', 'args' => array( 'emp' => ' ' ), 'type' => 'header') );
        foreach( $players as $player_id => $player ) {
            $firstRow[] = array( 'str' => '${player_name}',
                                 'args' => array( 'player_name' => $player['name'] ),
                                 'type' => 'header'
                               );
        }
        $table[] = $firstRow;

        $rowNames = array(
            clienttranslate('Points for Charms'),
            clienttranslate('Points for Relics'),
            clienttranslate('Points for number of quests'),
            clienttranslate('Points for Specialists'),
            clienttranslate('Points for Rooms'),
            clienttranslate('Offering to the Council points'),
            clienttranslate('Total points'),
        );
        $indexes = array('charms', 'relics', 'quests', 'specialists', 'rooms', 'offering', 'total');
        for($i=0;$i<7;$i++) {
            $row = array();
            $row[] = array( 'str' => '${name}',
                            'args' => array( 'name' => $rowNames[$i], 'i18n' => array('name') ),
                            'type' => 'header'
            );

            foreach( $players as $player_id => $player ) {
                $row[] = array( 'str' => '${value}',
                                'args' => array( 'value' => $score[$player_id]['score'][$indexes[$i]]),
                                'type' => 'header'
                );
            }

            $table[] = $row;
        }

        $this->notifyAllPlayers( "tableWindow", '', array(
            "id" => 'finalScoring',
            "title" => clienttranslate("End of the game scoring"),
            "table" => $table,
            "closing" => clienttranslate( "Close" )
        ) ); 


    }

    function getNextPlayerAtGuildBonusPhase($actualPlayer) {
        // players with guild bonuses at the beginning
        $guild_toActivate = array(2,3,5,6);
        if ( self::getGameStateValue('startPhase') == 0 ) {
            $nextPlayerId = $actualPlayer; 
        } else {
            $nextPlayerId = self::getPlayerAfter( $actualPlayer );
        }
        $nextPlayerGuild = $this->getPLayerGuild($nextPlayerId);
        $lastPlayer = self::getNextPlayerTable()[0];

        if ($nextPlayerId == $lastPlayer && self::getGameStateValue('startPhase') != 0 ) {
            $this->gamestate->changeActivePlayer( $nextPlayerId );
            $this->gamestate->nextState( 'endGuildPhase');
        } else {
            if (self::getGameStateValue('startPhase') == 0) {
                self::setGameStateValue('startPhase', 1);
            }
            if ( in_array($nextPlayerGuild, $guild_toActivate) ) {
                // player will play
                $this->gamestate->changeActivePlayer( $nextPlayerId );
                $this->gamestate->nextState( 'guildplay');
            } else {
                $this->getNextPlayerAtGuildBonusPhase($nextPlayerId);
            }
        }
    }

    function recalculateQuestPositionsSoloGame() {
        $lookup = array( 0 => 'discard', 1 => 0, 2 => 1, 5 => 2, 4 => 5, 3 => 4, 6 => 3);
        $allPositions = array(0,1,2,5,4,3);

        $sql = "SELECT specialistandquest_location_arg loc, specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_location = 'board' AND specialistandquest_visible = 1 ";
        $quests = self::getCollectionFromDB( $sql, true); 

        if (count($quests) > 5) { // remove quest
            $sql = "SELECT token_location loc FROM tokens WHERE token_type = 'sigil' AND token_location != 'free'  ";
            $sigils = self::getObjectListFromDB( $sql, true); 
            $posititionToRemove = 0;

            if (count($sigils) > 0 ) {
                $ids =  array();
                $sigilPositions = array();
                for ($i=0; $i<count($sigils) ; $i++) { 
                    $ids[] = explode("_", $sigils[$i])[1];
                    $sigilPositions[] =  array_search ($ids[$i], $quests);
                }
                
                $posititionToRemove = in_array("0", $sigilPositions) ? ( in_array("1", $sigilPositions) ? 2 : 1 ) : 0;
            }
            
            $id = $quests[$posititionToRemove];

            $sql = "UPDATE specialistandquest SET specialistandquest_location = 'removed'  WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$id' ";
            self::dbQuery( $sql); 

            if( $this->quest[ $this->getItemTypeById('quest', $id) ]['name'] == "The King's Funeral"  ) {
                self::setGameStateValue('soloKingsFuneral', 0);
            }

            self::notifyAllPlayers( "moveQuest",  '', array(
                'quest_id' => $id,
                'destination' => 'destroy',
            ) );

            for ($i=$posititionToRemove; $i>-1; $i--) { 
                unset($quests[$i]);
            }
        } else {
            $occupied = array_keys($quests);

            $free = array_values(array_diff($allPositions, $occupied))[0];
            
            foreach ($quests as $position => $id) { 
                if ( array_search($position,$allPositions) <  array_search($free,$allPositions) ) {
                    unset($quests[$position]);
                }
            }
        }

        // foreach ($quests as $position => $id) {
        //     $new_position = $lookup[$position];
        //     $sql = "UPDATE specialistandquest SET specialistandquest_location_arg = '$new_position'  WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$id' ";
        //     self::dbQuery( $sql);  

        //     self::notifyAllPlayers( "moveQuest",  '', array(
        //         'quest_id' => $id,
        //         'destination' => $new_position,
        //     ) );
        // }

        foreach($allPositions as $position) {
            if (key_exists($position, $quests) ) {
                $new_position = $lookup[$position];
                $id = $quests[$position];
                $sql = "UPDATE specialistandquest SET specialistandquest_location_arg = '$new_position'  WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$id' ";
                self::dbQuery( $sql);  

                self::notifyAllPlayers( "moveQuest",  '', array(
                    'quest_id' => $id,
                    'destination' => $new_position,
                ) );
            }
        }

        // draw new one
        $result = $this->drawNewCard('quest', null, 3);

        if ( $result == 'kingsFuneral') {
            self::setGameStateValue('soloKingsFuneral', 1 );
            self::notifyAllPlayers( "soloKingsFuneral",  '', array(
            ) );

            $this->soloGameExpandActionEnd();
        }

        if ( $result == 'offering') {
            // mark game end
            self::setGameStateValue('offeringActive', 1);
        }
    }
    


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in kingsguild.action.php)
    */

    function selectAction($selected_action) {
        self::checkAction( 'selectAction' );
        $this->gamestate->nextState( $selected_action );
    }

    function cancelAction() {
        self::checkAction( 'cancel');
        $player_id = self::getActivePlayerId();
        if ($this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction') {
            if (self::getGameStateValue('placed_specialist_type') == 33 ) {
                self::setGameStateValue('placed_specialist_type', 0);
                $this->gamestate->nextState( 'cancel');
            } else {
                self::notifyPlayer($player_id, "cancelClientState", '', array(
                ) );
            }
            
        }
        else if ( self::getGameStateValue('soloExpandSecondPart') == 1 && self::getPLayersNumber() == 1 ) {
            $this->gamestate->nextState( 'cancelSolo');
        } else {
            $this->gamestate->nextState( 'cancel');
        }
    }

    function chooseResource($resource_list) {
        self::checkAction( 'chooseResource');
        $last_resource = end($resource_list);

        if ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect') {
            // drop selected res type from all players
            $players = self::getObjectListFromDB( "SELECT player_id id FROM player", true );
            $player_id = self::getActivePlayerId();
            $treasure_id = self::getGameStateValue('played_treasure_card');
            $this->playerSellTreasure($treasure_id, $player_id, null, false);
            foreach ($players as $player) {
                // $token_type = $this->getResTileOrNumId($last_resource)[0];
                $sql = "SELECT token_id id FROM tokens WHERE  token_type_arg = '$last_resource' AND token_location = '$player' ";
                $result = self::getObjectListFromDB($sql, true );

                if ( !empty($result)) {
                    // $this->returnResourceByPlayer($last_resource, $player);
                    $this->returnResourceByPlayer(array($last_resource), $player, null, 'loss');
                }
            }

            self::setGameStateValue('played_treasure_card', 0);
            $this->gamestate->nextState( "confirm" );

        } else {
            //check if res type is available
            $sql = "SELECT count(token_id) c FROM tokens WHERE  token_id = '$last_resource' AND token_location = 'board' ";
            $result = self::getUniqueValueFromDB( $sql );
            if ($result < 1 ) {
                throw new BgaUserException( self::_("This resource is not available, hit  F5 to update") );
            }

            $resource_list_named = array();
            foreach($resource_list as $res_id) {
                $resource_list_named[] =  $this->getResTypeById($res_id);
            }

            $player_id = self::getActivePlayerId();

            // res type and amount check
            $mapper = new kgActionMapper($player_id, $this);
            $gatherCheck = $mapper->checkGather($resource_list_named, false);

            if (!$gatherCheck) {
                if (end($resource_list_named) == 'magic' || end($resource_list_named) == 'gem') {
                    throw new BgaUserException( self::_("You cannot gather this type of resource") );
                } elseif ( $mapper->isSpecificSpecialistPresent('Laborer') )
                    throw new BgaUserException( self::_("You can only select 3 resources of any type") );
                else {
                    throw new BgaUserException( self::_("You can only select 3 resources of the same type or 2 resources of different types") );
                }
            } else {
                if ( $this->gamestate->state()['name'] != 'playerGather' &&  $this->gamestate->state()['name'] != 'playerSpecialistOneTimeAction' ) {
                    $this->gamestate->nextState( "gather" );
                }

                self::notifyPlayer($player_id, "chooseResource", '', array(
                    'player_id' => $player_id,
                    'resource' =>   end($resource_list),
                    'trigger_replace' =>  $gatherCheck['triggerReplace'],
                    'max_gather_reached' => $gatherCheck['maxGather'], 
                ) );
            }
        }
    }

    function takeResourcesAndReplace($resource_list_take, $resource_list_return) {         
        self::checkAction( 'takeResourcesAndReplace' );
        $player_id = self::getActivePlayerId();

        $mapper = new kgActionMapper($player_id, $this);
        $freeSpaces = count($mapper->getPositionForResource());
        $returnNumber = count($resource_list_return);
        $gainNumber = count($resource_list_take);

        if ($this->gamestate->state()['name'] == 'playerGuildTurn') {
            $this->takeResourceByPlayer($resource_list_take, $player_id, false);
            self::giveExtraTime( $player_id);
            $this->gamestate->nextState( "nextPlayer" );

        }   elseif ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect') {
                // treasure card played and replace res action
                // first update card and than resolve return/gain resources
                $treasure_id = self::getGameStateValue('played_treasure_card');
                $maxres = $mapper->checkGather($resource_list_take, true)['triggerReplace'];

                if ($player_id != self::getGameStateValue( 'second_player_treasurePlay') ||  self::getGameStateValue("warlock_active") == 1 ) {
                    $this->playerSellTreasure($treasure_id, $player_id, null, false);
                }

                if ( !empty($resource_list_return)) {
                    $res_t = array();
                    foreach($resource_list_return as $resource){
                        // $this->returnResourceByPlayer($this->getResTypeById($resource), $player_id, $resource );
                        $res_t[] = $this->getResTypeById($resource);
                        $maxres--;
                    }
                    $this->returnResourceByPlayer($res_t, $player_id, $resource_list_return, 'return' );
                }

                $takeCount = count($resource_list_take);
                for ($i=0;$i<$maxres;$i++) {
                    unset($resource_list_take[$takeCount-1-$i]);
                }

                if ( !empty($resource_list_take)) {
                    $this->takeResourceByPlayer($resource_list_take, $player_id, false );
                }

                //update game state values
                if (self::getGameStateValue( 'second_player_treasurePlay') == -1) {
                    self::setGameStateValue('played_treasure_card', 0);
                    $sql = "UPDATE player SET player_replace_res = NULL WHERE player_id = '$player_id' ";
                    self::DbQuery($sql);
                }

                if ($player_id == self::getGameStateValue( 'second_player_treasurePlay') && (self::getGameStateValue("warlock_active") == 2 || self::getGameStateValue("warlock_active") == 0) ) {
                    self::setGameStateValue('played_treasure_card', 0);
                }
                $this->gamestate->nextState( "confirm" );
        }   else {
            if ($this->gamestate->state()['name'] != 'playerReplaceBonusResource') {

                if ( ($freeSpaces - $gainNumber + $returnNumber) < 0) {
                    throw new BgaUserException( self::_("Incorrect number of gain/return resources, hit F5 to reload game situation") );
                }
            } else {
                if (  $returnNumber > $gainNumber) {
                    throw new BgaUserException( self::_("Incorrect number of gain/return resources, hit F5 to reload game situation") );
                }
            }

            if ( !empty($resource_list_return)) {
                $res_t = array();
                foreach($resource_list_return as $resource){
                    $res_t[] = $this->getResTypeById($resource);
                }
                $this->returnResourceByPlayer($res_t, $player_id, $resource_list_return, 'return' );
            }

            if ($this->gamestate->state()['name'] == 'playerReplaceBonusResource') {               
                $maxres = $mapper->checkGather($resource_list_take, true)['triggerReplace'];

                $highindex = count($resource_list_take)-1;
                $lowindex =  count($resource_list_take)-$maxres-1;
                for ($i=$highindex;$i>$lowindex;$i--) {
                    unset($resource_list_take[$i]);
                }

                if (  count($resource_list_take) > $returnNumber) {
                    throw new BgaUserException( self::_("Incorrect number of gain/return resources, hit F5 to reload game situation") );
                }

                if ( !empty($resource_list_take)) {
                    $this->takeResourceByPlayer($resource_list_take, $player_id, true );
                }

                $this->updateReplaceRes($player_id);
            } else {
                $res_types = array();
                foreach($resource_list_take as $resource){
                    // $this->takeResourceByPlayer($this->getResTypeById($resource), $player_id, false, $resource );
                    $res_types[] = $this->getResTypeById($resource);
                    self::incStat( 1, 'player_resourceGathered',  $player_id);  
                    self::incStat( 1, 'table_resourceGathered'); 
                }

                if ( !empty($resource_list_take)) {
                    $this->takeResourceByPlayer($res_types, $player_id, false, $resource_list_take );
                }
            }
    
            if ( $this->gamestate->state()['name'] != 'playerSpecialistOneTimeAction' &&  $this->gamestate->state()['name'] != 'playerReplaceBonusResource') {
                $state_id = $this->getKeyByValueMultidim( $this->gamestate->states,'name', $this->gamestate->state()['name']);
                self::setGameStateValue("transition_from_state", $state_id);
                $this->gamestate->nextState( "takeResources" );
                return;
            }

            if ($this->gamestate->state()['name'] == 'playerReplaceBonusResource') {
                $this->gamestate->nextState( "takeResources" );
                return;
            }

            if ($this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction' && self::getGameStateValue( 'placed_specialist_type' ) != 26 ) {
                self::setGameStateValue( 'placed_specialist_type',0 );
                //check bonus resources
                $bonus_res = $mapper->getGatherBonus();

                if (!empty($bonus_res) ) {
                    // bonus not available!!                    
                    foreach($bonus_res as $resource){
                        $sql = "SELECT count(token_id) c FROM tokens WHERE token_type_arg = '$resource' AND token_location = 'board' ";
                        $result = self::getUniqueValueFromDB( $sql );
                        if ($result < 1  ) {
                            // res not available, no bonus
                            self::notifyAllPlayers("logInfo", clienttranslate( 'No available ${resource} ${player_name_id} gets no bonus' ), array(
                                'player_name_id' => $player_id,
                                'resource' =>   'resource_'.$resource,
                            ) );
                            unset($bonus_res[  array_search ($resource, $bonus_res) ] );
                        }
                    }
                    $bonus_res = array_values($bonus_res);

                    if (!empty($bonus_res) ) {
                        $replace_number = $mapper->checkGather($bonus_res, true)['triggerReplace'];
    
                        if ($replace_number > 0) {
                            $db_string = '';
                            for($i=0;$i<count($bonus_res);$i++) {
                                if ($i< (count($bonus_res) - $replace_number) ) {
                                    // $this->takeResourceByPlayer($bonus_res[$i], $player_id, true );
                                    $this->takeResourceByPlayer(array($bonus_res[$i]), $player_id, true );                          // name of the specialist in the log???????
                                } else {
                                    $db_string = $db_string.$bonus_res[$i].'_';
                                }
                            }
                            // save res to be replace to DB
                            $sql = "UPDATE player SET player_replace_res = '$db_string' WHERE player_id = '$player_id' ";
                            self::dbQuery($sql);
                        } else {
                            $this->takeResourceByPlayer($bonus_res, $player_id, true ); 
                        }
                    }
                }
                $this->gamestate->nextState( 'takeResources' );
                return;
            }
        }
    }

    function selectExpandItem($type, $id) {
        self::checkAction( 'selectExpandItem' );
        $player_id = self::getActivePlayerId();
        $mapper = new kgActionMapper($player_id, $this);

        // check if expand is possible
        if( !$mapper->canBuildItem($type, $id) ) {
            throw new BgaUserException( self::_("You don't have enough gold") );
        }

        // Aristocrat check
        if( $type == 'specialist' && $this->getItemTypeById('specialist', $id) == 27 && !$mapper->canBuildAristocrat() ) {
            throw new BgaUserException( self::_("You don't have free space to hire Aristocrat") );
        }

        // Overseer check
        if( $type == 'specialist' && $this->getItemTypeById('specialist', $id) == 33 && count($mapper->getPossiblePositionsForSpecialist()) < 2 ) {
            throw new BgaUserException( self::_("You don't have free space to hire Overseer") );
        }

        if ($type == 'room') {
            $tiles = $mapper->getPossiblePositionsForRooms();
        } else {
            if ( $this->getItemTypeById('specialist', $id) == 27){  // cannot block baggage!!!!
                $tiles = $mapper->getPossiblePositionsForSpecialist('aristocrat');
            } else {
                $tiles = $mapper->getPossiblePositionsForSpecialist();
            }
        }

        if ($this->gamestate->state()['name'] == 'playerTurn') {                                         
            $this->gamestate->nextState( "expand" );
        }

        self::notifyPlayer($player_id, "itemToPlace", '', array(
            'player_id' => $player_id,
            'item_type' =>   $type,
            'item_id' =>  $id,
            'possible_tiles' => $tiles,
        ) );
    }

    function placeRoom($room_id, $destination) {
        self::checkAction( 'placeRoom' );
        if($destination == '0') {
            throw new BgaUserException( self::_("You must place room to your guild first") );
        }

        $player_id = self::getActivePlayerId();

        $mapper = new kgActionMapper($player_id, $this);

        // check if expand is possible
        if( !$mapper->canBuildItem('room', $room_id) ) {
            throw new BgaUserException( self::_("You don't have enough gold") );
        }

        $positions = $mapper->getPossiblePositionsForRooms();
        if( !in_array(explode("_",$destination),$positions['singletiles']) && !in_array(explode("_",$destination),$positions['doubletiles']) ) {
            throw new BgaUserException( self::_("This position is not possible, hit F5 to update your screen") );
        }

        if(  $mapper->isSpecificRoomTypePresent($room_id) ) {
            throw new BgaUserException( self::_("You cannot build same room twice") );
        }

        // check magic/gem restriction
        $room_type = $this->getItemTypeById('room', $room_id);
        if ( self::getPlayersNumber() == 2 ) {
            if(  ($room_type == 3 || $room_type == 4 ) && ( $mapper->isSpecificRoomTypePresent(null, 3) || $mapper->isSpecificRoomTypePresent(null, 4) ) ) {
                throw new BgaUserException( self::_("You cannot build both Magic Arcaenum and Gem Workshop in 2 player game") );
            }
        }

        //update room pos in db and notif
        $this->placeRoomToPlayerGuild($room_id, $destination, $player_id, $mapper);

        // check for bonus on player mat
        $bonus = $mapper->getRoomPlacementBonus($room_id, $destination);
        $statueTriggerReplace = false;
        if ($bonus != false) {
            $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
            $player_name  = self::getUniqueValueFromDB( $sql );

            // check if res is available                                                             
            if ($bonus[0] != 'gold') {
                $sql = "SELECT count(token_id) c FROM tokens WHERE token_type_arg = '$bonus[0]' AND token_location = 'board' ";
                $result = self::getUniqueValueFromDB( $sql );
                if ($result < 1  ) {
                    // res not available, no bonus
                    self::notifyAllPlayers("logInfo", clienttranslate( 'No available ${resource} ${player_name} gets no bonus' ), array(
                        'player_name' => $player_name,
                        'resource' =>   'resource_'.$bonus[0],
                    ) );
                } elseif ($result == 1 && count($bonus) == 2) {

                    self::notifyAllPlayers("logInfo", clienttranslate( 'No available ${resource} ${player_name} gets only  one bonus' ), array(
                        'player_name' => $player_name,
                        'resource' =>   'resource_'.$bonus[0],
                    ) );

                    if ( $mapper->checkGather([$bonus[0]], true)['triggerReplace'] > 0 ) { // bonus res will trigger replace action
                        // self::setGameStateValue("bonus_res_replace", $this->getResTileOrNumId($bonus, true) );
                        $sql = "UPDATE player SET player_replace_res = '$bonus[0]' WHERE player_id = '$player_id' ";
                        self::dbQuery($sql);
                        $statueTriggerReplace = true;
                    } else {
                        // $this->takeResourceByPlayer($bonus[0], $player_id, true );
                        $this->takeResourceByPlayer(array($bonus[0]), $player_id, true );
                    }
                } else {
                    if ( $mapper->checkGather($bonus, true)['triggerReplace'] == 1 ) { // bonus res will trigger replace action
                        $sql = "UPDATE player SET player_replace_res = '$bonus[0]' WHERE player_id = '$player_id' ";
                        self::dbQuery($sql);
                        $statueTriggerReplace = true;
                    } elseif ($mapper->checkGather($bonus, true)['triggerReplace'] == 2) {
                        $res = $bonus[0]."_".$bonus[1];
                        $sql = "UPDATE player SET player_replace_res = '$res' WHERE player_id = '$player_id' ";
                        self::dbQuery($sql);
                        $statueTriggerReplace = true;
                    }   else {
                        // foreach($bonus as $res) {
                        //     $this->takeResourceByPlayer($res, $player_id, true );
                        // }
                        $this->takeResourceByPlayer($bonus, $player_id, true );
                    }
                }
            } else {
                $gold = count($bonus)*2;
                $this->updatePlayerGold($player_id, $gold);
                self::incStat( $gold, 'player_goldGained', $player_id );
                self::notifyAllPlayers("logInfo", clienttranslate( '${player_name} gets ${gold} bonus' ), array(
                    'player_name' => $player_name,
                    'gold' => 'gold_'.$gold,
                ) );
            }
        }

        if ($this->gamestate->state()['name'] == 'playerGuildTurn') {
            self::giveExtraTime( $player_id);
            $this->gamestate->nextState( "nextPlayer" );
        } elseif ($this->gamestate->state()['name'] == 'playerPlaceKingStatue') {
            if (self::getPlayersNumber() == 1) {
                $state_id = $this->getKeyByValueMultidim( $this->gamestate->states,'name', $this->gamestate->state()['name']);
                self::setGameStateValue("transition_from_state", $state_id);
            } 
            if ($statueTriggerReplace) {
                $this->gamestate->nextState( "placeRoomAndReplace" );
            } else {
                $this->gamestate->nextState( "placeRoom" );
            }
        } else {
            $state_id = $this->getKeyByValueMultidim( $this->gamestate->states,'name', $this->gamestate->state()['name']);
            self::setGameStateValue("transition_from_state", $state_id);
            self::setGameStateValue("expandAction_roomPlayed", 1);
            $this->gamestate->nextState( "placeRoom" );   
        }
    }

    function placeSpecialist( $specialist_id, $destination ) {
        self::checkAction( 'placeSpecialist' );
        if($destination == '0') {
            throw new BgaUserException( self::_("You must place specialist to your guild first") );
        }

        $player_id = self::getActivePlayerId();
        $mapper = new kgActionMapper($player_id, $this);
        $specialist_type = $this->getItemTypeById('specialist', $specialist_id);
        if ( !in_array($destination, $mapper->getPossiblePositionsForSpecialist() ) ) {
            throw new BgaUserException( self::_("This is not valid location for specialist") );
        }

        //first Bard action check
        if( $mapper->isSpecificSpecialistPresent('Bard') && $mapper->isSpecificSpecialistPresent( $this->specialist[$specialist_type]['name'] ) ) {
            //update specialist pos in db
            $sql = "UPDATE specialistandquest SET specialistandquest_location_arg = '$destination' WHERE specialistandquest_id = '$specialist_id' ";
            self::dbQuery( $sql );
            // cancel client state
            self::notifyPlayer($player_id, "cancelClientState", '', array(
            ) );
            // notify rest players
            self::notifyAllPlayers( "moveSpecialist",  clienttranslate('${player_name_id} moves specialist (Bard action)'), array(
                'specialist_id' => $specialist_id,
                // 'destination' => 'tile_specialist_'.$destination.'_'.$player_id,
                'destination' => $destination.'_'.$player_id,
                'destroy' => false,
                'player_name_id' => $player_id,
            ) );
            // send new possible positions to player
            $mapper = new kgActionMapper($player_id, $this);
            $tiles = $mapper->getPossiblePositionsForSpecialist();
            self::notifyPlayer($player_id, "updateBard", '', array(
                'tiles' => $tiles,
            ) );
            return;
        }

        //check if specialist is present
        $sql = "SELECT specialistandquest_location l,specialistandquest_location_arg loc, specialistandquest_discount discount, specialistandquest_type_arg t FROM specialistandquest WHERE specialistandquest_id = '$specialist_id' ";
        $specialist_info = self::getObjectFromDB( $sql );
        if ($specialist_info['l'] != 'board') {
            if ($specialist_info['l'] != 'notplaced' && $specialist_info['t'] != 37 ) {
                throw new BgaUserException( self::_("Wrong specialist, hit F5 to update") );
            }
        }

        // check if expand is possible
        if (self::getGameStateValue("placed_specialist_type") != 33) {
            if( !$mapper->canBuildItem('specialist', $specialist_id)  ) {
                throw new BgaUserException( self::_("You don't have enough gold") );
            }
        }

        // Aristocrat check
        if($specialist_type == 27 && !$mapper->canBuildAristocrat() ) {
            throw new BgaUserException( self::_("You don't have free space to hire Aristocrat") );
        }

        // Overseer check
        if($specialist_type == 33 && count($mapper->getPossiblePositionsForSpecialist()) < 2 ) {
            throw new BgaUserException( self::_("You don't have free space to hire Overseer") );
        }

        if ($specialist_type == 27)  {
            $positions = $mapper->getPossiblePositionsForSpecialist('aristocrat');
        } elseif ($specialist_type == 37) {
            $positions = $mapper->getPossiblePositionsForSpecialist('baggage');
        } else {
            $positions = $mapper->getPossiblePositionsForSpecialist();
        }

        if( !in_array($destination, $positions) ) {
            throw new BgaUserException( self::_("This position is not possible, hit F5 to update your screen") );
        }

        // calculate discount
        if ( $mapper->isSpecificSpecialistPresent('Recruiter') ) {
                $discount =  2*$specialist_info['discount'];
                if ($this->specialist[$specialist_info['t']]['value'] < $discount) {
                    $discount = $this->specialist[$specialist_info['t']]['value'];
                }
        } else {
            $discount =  $specialist_info['discount']; 
        }

        if ( self::getGameStateValue("placed_specialist_type") == 33 ) {                // Overseer -> free
            $discount = $this->specialist[$specialist_type]['value'];
        }

        // if played treasure card -> play it
        if ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect') {
            $this->playerSellTreasure($treasure_id = self::getGameStateValue('played_treasure_card'), $player_id, null, false );
        }

        //update specialist in db and adjust gold
        $this->placeSpecialistToPlayerGuild($specialist_id, $destination, $player_id, $discount, $mapper);

        // update rest specialist on board
        if ( $specialist_type != 37 ) { // not in case of baggage
            if (self::getPlayersNumber() == 1 ) {                                                     // solo game
                
            } else {
                $number_to_draw = $this->updateSpecialistsOnBoard($specialist_info['loc'], $specialist_info['discount'], 1);
                // and draw new specialists
                for ($i=$number_to_draw-1;$i>-1;$i--) {
                    $this->drawNewCard('specialist', null, $i);
                }
            } 
        }

        // if Curator prepare menu and update discard cards
        $skip = false;
        if ($specialist_type == 29)  {
            $sql = "SELECT treasure_id id, treasure_type t FROM treasure WHERE treasure_location = 'discard' ";
            $discarded_treasures = self::getObjectListFromDB($sql);
            $relics = array();
            // search for relics
            $pos = 20;
            foreach ($discarded_treasures as $treasure) {
                if (  $this->treasures[$treasure['t']]['cathegory']  == 'Relic' ) {         // if relics found, store position in menu
                    $relics[] = $treasure['id'];
                    $id = $treasure['id'];
                    $sql = "UPDATE treasure SET treasure_location_arg = '$pos' WHERE treasure_id = '$id' ";
                    self::DbQuery($sql);
                    $pos++;
                }
            }

            if (count($relics) > 0) {
                $this->preparePlayerForSelling($player_id, count($relics));
                // notify relics reorder
                self::notifyPlayer($player_id , "reorderRelics", '', array(
                    'relics' => $relics,
                ) );
            } else {
                //notify about relics
                self::notifyAllPlayers("logInfo", clienttranslate( 'No relics in discard pile, Curator action skipped' ), array() );
                $skip = true;
            }
        }

        // if opponents have no resource skip smuggler
        if ($specialist_type == 34) {
            $sql = "SELECT token_id id FROM tokens WHERE ( token_type = 'baseresource' OR  token_type = 'advresource') AND token_location <> 'board' AND token_location <> '$player_id' ";
            $opponentsResource = self::getObjectListFromDB($sql, true);

            if (count($opponentsResource) < 1) {
                self::notifyAllPlayers("logInfo", clienttranslate( 'No resource in opponent guilds, Smuggler action skipped' ), array() );
                $skip = true;
            }
        }

        if ($this->gamestate->state()['name'] != 'playerSpecialistOneTimeAction' && $this->gamestate->state()['name'] != 'playerPlayTreasureEffect') {
            $state_id = $this->getKeyByValueMultidim( $this->gamestate->states,'name', $this->gamestate->state()['name']);
            self::setGameStateValue("transition_from_state", $state_id);
            self::setGameStateValue("expandAction_specialistPlayed", 1);
            if (!$skip) {
                self::setGameStateValue("placed_specialist_type", $specialist_info['t']);
            }
        } else {
            if (self::getGameStateValue("playerEndPhase")) {        //play treasure card on end with specialist action
                self::setGameStateValue("transition_from_state", 25);
                if(self::getPlayersNumber() == 1 ) {
                    $this->soloGameExpandActionEnd();
                }
            }
            if ( key($this->specialist[$specialist_type]['ability']) == 'onetimeaction' || key($this->specialist[$specialist_type]['ability']) == 'onetimebonus') {
                if (!$skip) {
                    self::setGameStateValue("placed_specialist_type", $specialist_type);                // chain of specialists actions
                }
            } else {
                    self::setGameStateValue("placed_specialist_type",0);
            }
        }

        $this->gamestate->nextState( "placeSpecialist" );  
    }

    function drawTreasureCard($card_color) {
        self::checkAction( 'drawTreasureCard' );
        
        // $player_id = self::getActivePlayerId();                                 
        if ($this->gamestate->state()['name'] == 'playerGuildTurn') {
            $player_id = self::getActivePlayerId();
            $mapper = new kgActionMapper($player_id, $this);
            $draw_result = $mapper->getPositionForTreasureCards(1);
            $this->drawNewCard('treasure', $card_color, $draw_result['positions'][0], $player_id);
            self::giveExtraTime( $player_id);
            $this->gamestate->nextState( "nextPlayer" );
        } elseif ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect') {
            $player_id = self::getActivePlayerId();
            $treasure_id = self::getGameStateValue("played_treasure_card");
            $treasure_discard = self::getGameStateValue("selected_treasure_card_discard");

            if ($player_id == self::getGameStateValue("second_player_treasurePlay") && (self::getGameStateValue("warlock_active") == 2 || self::getGameStateValue("warlock_active") == 0) ) {     // player pays one for card
                $this->updatePlayerGold($player_id, -1, true);
                self::setGameStateValue('played_treasure_card', 0);
            } elseif ($treasure_discard != 0) {                                             // player discard one card and get two
                $this->playerSellTreasure($treasure_id, $player_id, null, false);               
                $this->playerSellTreasure($treasure_discard, $player_id, null, false, true);
                self::setGameStateValue("selected_treasure_card_discard", 0);
            } else {
                $this->playerSellTreasure($treasure_id, $player_id, null, false);           // player gets 1 gold and card
                self::setGameStateValue( 'alreadySelected_treasureCard', $this->getNumericCardColor($card_color));
                $this->updatePlayerGold($player_id, 1, true);
            }
            
            $mapper = new kgActionMapper($player_id, $this);
            if ($treasure_discard  == 0) {
                $draw_result = $mapper->getPositionForTreasureCards(1);
            } else {
                $draw_result = $mapper->getPositionForTreasureCards(2);
            }

            if ($draw_result['needToSell'] > 0) {
                $this->preparePlayerForSelling($player_id, $draw_result['needToSell']);
            }

            if ($treasure_discard  == 0) {
                $this->drawNewCard('treasure', $card_color, $draw_result['positions'][0], $player_id);
            } else {
                // $colors = array('blue', 'red', 'yellow');
                // $result_colors = array();
                // foreach ($colors as $color) {
                //     if (strpos($card_color, $color) !== false) {
                //         $result_colors[] = $color;
                //         if (strpos(substr($card_color,strlen($color)), $color) !== false) {
                //             $result_colors[] = $color;
                //         }
                //     }
                // }
                $result_colors = explode("_",$card_color);
                $this->drawNewCard('treasure', $result_colors[0], $draw_result['positions'][0], $player_id);
                $this->drawNewCard('treasure', $result_colors[1], $draw_result['positions'][1], $player_id);
            }

            if ( $draw_result['needToSell'] ) {                         
                $this->gamestate->setPlayersMultiactive( array($player_id), "drawTreasureCardAndSell", true );
                $this->gamestate->nextState( 'drawTreasureCardAndSell' );
            } else {
                $this->gamestate->nextState( 'confirm' );
            }


        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction') {
            $player_id = self::getActivePlayerId();

            if (self::getGameStateValue("placed_specialist_type") == 32 ) {    // Minstrel
                $mapper = new kgActionMapper($player_id, $this);
                $draw_result = $mapper->getPositionForTreasureCards(1);
                if ($draw_result['needToSell']) {
                    $this->preparePlayerForSelling($player_id, 1);
                }
                $this->drawNewCard('treasure', $card_color, $draw_result['positions'][0], $player_id);
                self::setGameStateValue("placed_specialist_type", 0);  
                if ($draw_result['needToSell']) {
                    $this->gamestate->setPlayersMultiactive( array($player_id), "drawTreasureCardAndSell", true );
                    $this->gamestate->nextState( 'drawTreasureCardAndSell' );
                } else {
                    $this->gamestate->nextState( 'drawTreasureCard' );
                }
            } else {        // merchant
                $this->preparePlayerForSelling($player_id, 3);
                $result_colors = explode("_",$card_color);

                $this->drawNewCard('treasure', $result_colors[0], 20, $player_id);
                $this->drawNewCard('treasure', $result_colors[1], 21, $player_id);
                $this->drawNewCard('treasure', $result_colors[2], 22, $player_id);
                $this->gamestate->nextState( 'selectTreasureForDiscard' );
            }
        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistCraftAction' && self::getGameStateValue("appraiser_active") == 1 ) {
            $player_id = self::getActivePlayerId();
            $mapper = new kgActionMapper($player_id, $this);
            $draw_result = $mapper->getPositionForTreasureCards(1);
            if ($draw_result['needToSell']) {
                $this->preparePlayerForSelling($player_id, 1);
            }
            $this->drawNewCard('treasure', $card_color, $draw_result['positions'][0], $player_id);
            $this->gamestate->setPlayersMultiactive( array($player_id), "drawTreasureCardAndSell", true );
            $this->gamestate->nextState( 'drawTreasureCardAndSell' );
        } else {
            $player_id = self::getActivePlayerId();
            $mapper = new kgActionMapper($player_id, $this);
            $draw_result = $mapper->getPositionForTreasureCards(1);
            if ($draw_result['needToSell']) {
                $this->preparePlayerForSelling($player_id, 1);
            }
            $this->drawNewCard('treasure', $card_color, $draw_result['positions'][0], $player_id);
            if ( self::getGameStateValue("transition_from_state") == 7 || self::getGameStateValue("specialist_craft_action_played") == 1 ) {
                // update FRW bonus of given quest
                $quest_pos = self::getGameStateValue('newQuestCardPosition');
                $sql = "UPDATE specialistandquest SET specialistandquest_discount = 1 WHERE specialistandquest_type = 'quest' AND specialistandquest_location = 'board' AND specialistandquest_location_arg='$quest_pos' ";
                self::dbQuery( $sql );

                // update player gold for 
                $this->updatePlayerGold($player_id,-1);
                // update after craft actions
                $this->updateAfterCraftActions($player_id);
            } else {
                self::setGameStateValue("placed_specialist_type", 0);          
            }

            if ( $draw_result['needToSell'] ) {                             // !!!!! correct?
                $this->gamestate->setPlayersMultiactive( array($player_id), "drawTreasureCardAndSell", true );
                $this->gamestate->nextState( 'drawTreasureCardAndSell' );
            } else {
                $this->gamestate->nextState( 'drawTreasureCard' );
            }
        }
    }

    function stealResource($resource_id, $player_from, $return_id){
        self::checkAction( 'stealResource' );

        $sql = "SELECT token_location loc FROM tokens WHERE token_id = '$resource_id' ";
        $player = self::getUniqueValueFromDB( $sql );

        if ($player != $player_from) {
            throw new BgaUserException( self::_("This is not player's resource, hit F5 to update your screen") );
        }

        $player_id = self::getActivePLayerId();
        $mapper = new kgActionMapper($player_id, $this);
        $resource_type = $this->getResTypeById($resource_id);

        if ($mapper->checkGather([$resource_type], true)['triggerReplace'] > 0 && $return_id == null ) {
            throw new BgaUserException( self::_("You must also select one resource to return") );
        } 

        $sql = "SELECT player_name n FROM player WHERE player_id = '$player_id' ";
        $player_name  = self::getUniqueValueFromDB( $sql );

        if ($return_id != null) {
            // $this->returnResourceByPlayer($this->getResTypeById($return_id), $player_id, $return_id );
            $this->returnResourceByPlayer(array($this->getResTypeById($return_id)), $player_id, array($return_id), 'return' );
        }

        $position = $mapper->getPositionForResource();
        $sql = "UPDATE tokens SET token_location = '$player_id', token_location_arg = '$position[0]' WHERE token_id = '$resource_id' ";
        self::dbQuery($sql);

        self::notifyAllPlayers("stealResource", clienttranslate( '${player_name} steals ${resource} from ${player_name_id}' ), array(
            'player_name' => $player_name,
            'player_id' => $player_id,
            'resource' => 'resource_'.$resource_type,
            'resource_type' => $resource_type,
            'resource_id' => 'resource_'.$resource_id,
            'player_name_id' => $player_from,
            'player_id_from' => $player_from,
            'destination' => 'tile_storage_'.$position[0].'_'.$player_id,
        ) );

        self::setGameStateValue('placed_specialist_type', 0);
        $this->gamestate->nextState( "stealResource" );  
    }

    function selectCraftItem($quest_id, $item_id) {
        self::checkAction( 'craftItem' );

        // check
        $sql = "SELECT specialistandquest_location loc, specialistandquest_visible vis FROM specialistandquest WHERE specialistandquest_id = '$quest_id' ";
        $quest_info= self::getObjectFromDB( $sql );

        if ( $quest_info['loc'] != 'board' || $quest_info['vis'] != 1  ) {
            throw new BgaUserException( self::_("This quest is not available, hit F5 to update") );
        }

        $player_id = self::getActivePLayerId();
        $mapper = new kgActionMapper($player_id, $this);

        $quest_type = $this->getItemTypeById('quest', $quest_id);

        if ( !$mapper->canCraftItem($quest_type, $item_id+1)) {
            throw new BgaUserException( self::_("You don't have enough resources to craft this item") );
        }

        // Thug check
        $sql = "SELECT token_type_arg thugplayer, token_location_arg locarg FROM tokens WHERE token_type = 'thug' AND token_location = 'quest' ";
        $thug = self::getObjectFromDB( $sql );
        if ($thug['locarg'] == $quest_id && $thug['thugplayer'] != $player_id ) {
            //check if player has gold to spend
            if ( $mapper->getPlayerGold() < 2) {
                throw new BgaUserException( self::_("You don't have enough gold to pay Thug") );
            }
            $thug = array( 'player_id' => $thug['thugplayer'], 'value' => 2);
        } else {
            $thug = null;
        }

        $second = count($this->quest[$quest_type]['items']) >1 ? true : false;              // check if second item craft is possible
        if ($second) {
            $sigil_loc = 'quest_'.$quest_id;
            $sql = "SELECT token_type_arg player FROM tokens WHERE token_type = 'sigil' AND token_location = '$sigil_loc' ";
            if (self::getUniqueValueFromDB( $sql) !=null) {
                $second = false;
            } 
        }

        if ($this->gamestate->state()['name'] == 'playerTurn') {                                              // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $this->gamestate->nextState( "craft" );
        }

        self::notifyPlayer($player_id , "selectCraftItem", '', array(
            'quest_id' => $quest_id,
            'item_id' => $item_id,
            'second_item' => $second,
            'thug_present' => $thug,
        ) );
    }


    function craftItem( $quest_id, $item_id, $second) {
        self::checkAction( 'craftItem' );

        // check
        $sql = "SELECT specialistandquest_location loc, specialistandquest_location_arg pos, specialistandquest_visible vis, specialistandquest_discount frw FROM specialistandquest WHERE specialistandquest_id = '$quest_id' ";
        $quest_info= self::getObjectFromDB( $sql );

        if ( $quest_info['loc'] != 'board' || $quest_info['vis'] != 1  ) {
            throw new BgaUserException( self::_("This quest is not available, hit F5 to update") );
        }

        $player_id = self::getActivePLayerId();
        $mapper = new kgActionMapper($player_id, $this);

        $item_id_second = $item_id == 0 ? 1:0;
        $quest_type = $this->getItemTypeById('quest', $quest_id);
        if ($second) {
            if ( count($this->quest[$quest_type]['items']) < 2 ) {
                throw new BgaUserException( self::_("This quest doesn't have two items. Hit F5 to update") );
            }

            if ( !$mapper->canCraftItem($quest_type, $item_id+1) || !$mapper->canCraftItem($quest_type, $item_id_second+1)) {         
                throw new BgaUserException( self::_("You don't have enough resources to craft those items") );
            }

            if ( !$mapper->canCraftBothItems($quest_type, $item_id+1,  $item_id_second+1) ) {         
                throw new BgaUserException( self::_("You don't have enough resources to craft those items") );
            }
        } else {
            if ( !$mapper->canCraftItem($quest_type, $item_id+1)) {
                throw new BgaUserException( self::_("You don't have enough resources to craft this item") );
            }
        }

        // check thug
        $sql = "SELECT token_type_arg thugplayer, token_location_arg locarg FROM tokens WHERE token_type = 'thug' AND token_location = 'quest' ";
        $thug = self::getObjectFromDB( $sql );
        if ($thug['locarg'] == $quest_id) {
            $thug = $thug['thugplayer'];
        } else {
            $thug = null;
        }

        $craft_result = $this->playerCraftItem($quest_id, $item_id, $quest_type, $player_id, $second, $mapper, $thug);
        // reset potions flag on client side
        self::notifyPlayer($player_id , "resetPotions", '', array() );

        if ( $this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction' ) {
            self::setGameStateValue( 'placed_specialist_type', 0);
            self::setGameStateValue( 'specialist_craft_action_played', 1);
        } else {
            $state_id = $this->getKeyByValueMultidim( $this->gamestate->states,'name', $this->gamestate->state()['name']);
            self::setGameStateValue("transition_from_state", $state_id);
        }

        $hero = $this->quest[$quest_type]["hero"][0];
        if (  $craft_result['player_alsoCrafted'] != $player_id || $quest_info['frw'] == 0  ) {         // exclude playing the effect twice, discount field in DB marks already used bonues (1)
            switch ($hero) {
                case 'warrior':
                    self::setGameStateValue( 'craft_action_hero', 1);
                break;
                case 'rogue':
                    self::setGameStateValue( 'craft_action_hero', 2);
                break;
                case 'mage':
                    self::setGameStateValue( 'craft_action_hero', 3);
                break;
            } 
        } else {
            self::setGameStateValue( 'craft_action_hero', 0);  
        }

        if ($craft_result['quest_completed']) {                                                 
            // draw new card to same position
            // $this->drawNewCard('quest', null, $quest_info['pos']);
            self::setGameStateValue('newQuestCardPosition', $quest_info['pos']);
            $luckyPotion = false;
            $reward = $this->quest[$quest_type]["reward"];

            // lucky potion check
            $sql = "SELECT player_active_potions ap FROM player WHERE player_id = '$player_id' ";
            $potions = self::getUniqueValueFromDb($sql); 
            if ($potions != null) {
                $potions_array = explode("_", $potions);
                if (in_array(16, $potions_array)) {
                    $luckyPotion = true;
                    $reward = array_merge($reward, $reward);
                    // reset active potion in db
                    if (($key = array_search(16, $potions_array)) !== false) {
                        unset($potions_array[$key]);
                        $potions_array = array_values($potions_array);

                        $potion_string = implode("_", $potions_array);
                        $sql = "UPDATE player SET player_active_potions = '$potion_string ' WHERE player_id = '$player_id' ";
                        self::dbQuery( $sql); 
                    }
                }
            }

            if ( $craft_result['player_alsoCrafted'] == null ||  $craft_result['player_alsoCrafted'] == $player_id ) {        // only one player gets treasure card/cards

                $next = $mapper->getPositionForTreasureCards(count($reward));
                if ($next['needToSell'] > 0) {
                    $this->preparePlayerForSelling($player_id, count($reward));
                }
                for($i=0;$i<count($reward);$i++ ) {
                    $this->drawNewCard('treasure', $reward[$i], $next['positions'][$i], $player_id );
                }
                if ($next['needToSell'] > 0) {
                    $this->gamestate->setPlayersMultiactive( array($player_id), "craftItemAndSell", true );
                    $this->gamestate->nextState( "craftItemAndSell" ); 
                } else {
                    // check Appraiser !!!!!!!!!!!!!
                    if ($mapper->isSpecificSpecialistPresent('Appraiser') ) {
                        self::setGameStateValue('appraiser_active',1);
                        self::setGameStateValue('player_play_appraiser', $player_id );
                    }
                    $this->gamestate->nextState( "craftItem" ); 
                }
            }  else {  // each player get one card, first one to select!
                $this->preparePlayerForSelling($player_id, count($reward) );
                for($i=0;$i<count($reward);$i++ ) {
                    $this->drawNewCard('treasure', $reward[$i], 20+$i, $player_id );
                }
                self::setGameStateValue('player_gain_treasure', $craft_result['player_alsoCrafted']);
                $this->gamestate->nextState( "completeQuestShared" );
            }
        } else {            //craft withou completing quest -> next player
            $this->gamestate->nextState( "craftItem" ); 
        }
    }

    function selectTreasureCards($treasure_ids_selected) {
        self::checkAction( 'selectTreasureCards' );
        $curator = self::getGameStateValue('placed_specialist_type') == 29 ? true:false;

        if ($this->gamestate->state()['name'] == 'playerSellTreasure') {
            $player_id = self::getCurrentPLayerId();
            $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' ";
        } elseif ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect' || $this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction') {
            $player_id = self::getActivePLayerId();
            $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' ";
            if ($curator )  { // curator
                $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = 'discard' AND treasure_location_arg >= 20 ";
            }
        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistCraftAction') {
            $player_id = self::getActivePLayerId();
            $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' ";
        } else {
            $player_id_active = self::getActivePLayerId();
            $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id_active' AND treasure_location_arg >= 20 ";
        }

        //check count
        if ( $this->gamestate->state()['name'] == 'playerSellTreasure' || $this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction') {
            if ($curator) {
                // check if gold available
                $sqlG = "SELECT player_gold FROM player WHERE player_id = '$player_id' ";
                $playerGold = self::getUniqueValueFromDB($sqlG);
                if ( count($treasure_ids_selected)*3 > $playerGold ) {
                    throw new BgaUserException( self::_("You don't have enough gold for all relics") );
                }
                if ( count($treasure_ids_selected) < 1 ) {
                    throw new BgaUserException( self::_("You must choose at leas one relic") );
                }
            } else {
                $msg = self::_("You have to choose exactly %s treasure card(s)");
                $num = $this->gamestate->state()['name'] == 'playerSellTreasure' ? $this->gamestate->state()['args'][$player_id]['cards_needed_to_sell'] : $this->gamestate->state()['args']['parameters']['cards_needed_to_sell'];

                if (count($treasure_ids_selected) != $num ) {
                    throw new BgaUserException( sprintf( $msg, $num  ) );
                }
            }
        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistCraftAction') {
            if (count($treasure_ids_selected) != 1  ) {
                throw new BgaUserException(self::_("You have to choose exactly 1 scroll") );
            }

            if ( $this->treasures[$this->getItemTypeById('treasure', $treasure_ids_selected[0] )]["cathegory"] != 'Scroll' ) {
                throw new BgaUserException(self::_("You can select scrolls only") );
            }
        } else {
            if (count($treasure_ids_selected) != $this->gamestate->state()['args']['card_number']  ) {
                throw new BgaUserException( sprintf( self::_("You have to choose exactly %s treasure card(s)"), $this->gamestate->state()['args']['card_number'])  );
            }
        }

        // check availability 
        $treasure_ids_all = self::getObjectListFromDB($sql, true);
        foreach($treasure_ids_selected as $treasure) {
            if ( !in_array($treasure, $treasure_ids_all) ) {
                throw new BgaUserException( self::_("Treasures not available, hit F5 to update") );
            }
        }

        if ($this->gamestate->state()['name'] == 'playerSellTreasure') {
            $appraiserPlay = self::getGameStateValue('appraiser_active') == 1 ? true : false;

            //sell treasure/discard treasure
            foreach($treasure_ids_selected as $treasure) {
                if ($appraiserPlay) { 
                    $this->playerSellTreasure($treasure, $player_id, null, false, true);
                } else {
                    $this->playerSellTreasure($treasure, $player_id);
                }
            }

            $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' AND treasure_location_arg >= 20  ";
            $treasure_rest = self::getObjectListFromDB($sql, true);
            $mapper = new kgActionMapper($player_id, $this);
            foreach($treasure_rest as $treasure) {
                $position = $mapper->getPositionForTreasureCards(1)['positions'][0];
                $this->updateHand($player_id, $position,$treasure );
            }

            // update game state
            // check Appraiser !!!!!!!!!!!!!
            if ($mapper->isSpecificSpecialistPresent('Appraiser') &&  !$appraiserPlay ) {
                self::setGameStateValue('appraiser_active',1);
                self::setGameStateValue('player_play_appraiser', $player_id );
            }

            if ( $appraiserPlay ) {
                self::setGameStateValue('appraiser_active',0);
            }

            if (self::getGameStateValue("second_player_treasurePlay") == -1 ) {
                $this->gamestate->setPlayerNonMultiactive( $player_id, "confirm" );
            } else {
                $this->gamestate->setPlayerNonMultiactive( $player_id, "confirmCardPlay" );
            }

        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistCraftAction') {
            // destroy scroll effect
            $sql = "SELECT treasure_location_arg locarg, treasure_type spectype FROM treasure WHERE treasure_location = '$player_id' AND treasure_id = '$treasure_ids_selected[0]' ";
            $treasure_info = self::getObjectFromDB($sql);

            $this->updateAfterCraftActions($player_id);
            $this->playerPlayTreasureEffect($treasure_ids_selected[0], $player_id, $treasure_info, true);

        } elseif ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect') {
                self::setGameStateValue("selected_treasure_card_discard", $treasure_ids_selected[0] );
                $this->gamestate->nextState( "selectTreasureDiscard" );
        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistOneTimeAction') {

            if ($curator) {
                $cards_number = count($treasure_ids_selected);
                $mapper = new kgActionMapper($player_id, $this);
                $positions = $mapper->getPositionForTreasureCards($cards_number);
                if ($positions['needToSell'] > 0) {
                    $this->preparePlayerForSelling($player_id, count($treasure_ids_selected) );
                } 

                // adjust gold
                $this->updatePlayerGold($player_id, -$cards_number*3);
                //update new relics in hand
                for ($i=0;$i<count($treasure_ids_selected);$i++) {
                    $position = $positions["positions"][$i];
                    $sql = "UPDATE treasure SET treasure_location = '$player_id', treasure_location_arg = '$position' WHERE treasure_id = '$treasure_ids_selected[$i]' ";
                    self::dbQuery( $sql );
                }

                // update back rest discards
                $rest_relics = array_values( array_diff($treasure_ids_all, $treasure_ids_selected) );
                foreach($rest_relics as $treasure_id) {
                    $sql = "UPDATE treasure SET treasure_location_arg = 0 WHERE  treasure_id = '$treasure_id' ";
                    self::dbQuery( $sql );
                }

                //notify
                self::notifyAllPlayers("playerChooseRelics", clienttranslate( '${player_name_id} buys ${number} relic(s) from discard pile for ${gold}' ), array(
                    'player_name_id' => $player_id,
                    'player_id' => $player_id,
                    'number' => $cards_number,
                    'gold' => 'gold_'.($cards_number*3),
                    'relics_keep' => $treasure_ids_selected,
                ) );
                //card movement
                self::notifyPlayer($player_id, "thisPlayerChooseRelics", '', array(
                    'relics_keep' => $treasure_ids_selected,
                    'relics_positions' =>$positions["positions"],
                    'relics_return' => $rest_relics,
                ) );

                self::setGameStateValue("placed_specialist_type", 0);
                if ( $positions['needToSell'] > 0 ) {
                    $this->gamestate->setPlayersMultiactive( array($player_id), "drawTreasureCardAndSell" );
                    $this->gamestate->nextState("drawTreasureCardAndSell");
                } else {
                    $this->gamestate->nextState( "drawTreasureCard" );
                }

            } else {
                //discard treasures
                foreach($treasure_ids_selected as $treasure) {
                    $this->playerSellTreasure($treasure, $player_id, null, false, true);
                }
                $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' AND treasure_location_arg >= 20  ";
                $treasure_rest = self::getObjectListFromDB($sql, true);
                $mapper = new kgActionMapper($player_id, $this);
                foreach($treasure_rest as $treasure) {
                    $position = $mapper->getPositionForTreasureCards(1)['positions'][0];
                    $this->updateHand($player_id, $position,$treasure );
                }
                
                self::setGameStateValue("placed_specialist_type", 0);
                $this->gamestate->nextState( "discardTreasures" );
            }
        } else {                                                            // after shared completed quest
            $cards_number = count($treasure_ids_selected);
            $player_id_other = self::getGameStateValue('player_gain_treasure');
            $mapper_actual = new kgActionMapper($player_id_active, $this);
            $mapper_other = new kgActionMapper($player_id_other, $this);

            //appraiser check
            if ($mapper_actual->isSpecificSpecialistPresent('Appraiser') ) {
                self::setGameStateValue("appraiser_active", 1);
                self::setGameStateValue("player_play_appraiser", $player_id_active);
            }
            if ($mapper_other->isSpecificSpecialistPresent('Appraiser') ) {
                self::setGameStateValue("appraiser_active", 1);
                self::setGameStateValue("player_play_appraiser", $player_id_other);
            }

            $position_active = $mapper_actual->getPositionForTreasureCards($cards_number);
            $position_other = $mapper_other->getPositionForTreasureCards($cards_number);

            $playersToActivateNext = array();
            if ($position_active['needToSell'] > 0) {
                $this->preparePlayerForSelling($player_id_active, $position_active['needToSell']);
                $playersToActivateNext[] = $player_id_active;
            } 

            if ($position_other['needToSell'] > 0) {
                $this->preparePlayerForSelling($player_id_other, $position_other['needToSell']);
                $playersToActivateNext[] = $player_id_other;
            }

            $treasure_ids_other = array_values (array_diff($treasure_ids_all, $treasure_ids_selected) );

            for ($i=0;$i<count($treasure_ids_selected);$i++) {
                // update db player 1
                $position = $position_active["positions"][$i];
                $sql = "UPDATE treasure SET treasure_location = '$player_id_active', treasure_location_arg = '$position' WHERE treasure_id = '$treasure_ids_selected[$i]' ";
                self::dbQuery( $sql );
            
                // update db player 2
                $position = $position_other["positions"][$i];
                $sql = "UPDATE treasure SET treasure_location = '$player_id_other', treasure_location_arg = '$position' WHERE treasure_id = '$treasure_ids_other[$i]' ";
                self::dbQuery( $sql );
            

                $all_players = self::getObjectListFromDB( "SELECT player_id id FROM player", true);

                //notify
                foreach ($all_players as $player) {
                    if ($player == $player_id_active) {
                        $destroy = ($i == count($treasure_ids_selected)-1) ? true:false;
                        self::notifyPlayer($player, "thisPlayerChooseTreasure", clienttranslate('${You} choose to keep ${card1} and ${player_name_id} gets ${card2}'), array(
                            'treasure_id_keep' => $treasure_ids_selected[$i],
                            'treasure_id_give' => $treasure_ids_other[$i],
                            'card1' => 'treasure_'.$this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_selected[$i])]['color'],
                            'card2' => 'treasure_'.$this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_other[$i])]['color'],
                            'player_name_id' => $player_id_other,
                            'player_id_give' => $player_id_other,
                            'treasure_location' => 'tile_card_'.$position_active["positions"][$i],
                            'destroy_menu' => $destroy,
                            'You'=>'You'
                        ) );
                    } elseif ($player == $player_id_other) {
                        // $card_info =  $this->treasures[$this->getItemTypeById('treasure', $id)];
                        $card_info =  $this->treasures[$this->getItemTypeById('treasure', $treasure_ids_other[$i])];
                        $card_info['type'] = $this->getItemTypeById('treasure', $treasure_ids_other[$i]);    
                        $card_info['visible'] = "1";
                        self::notifyPlayer($player, "thisPlayerGetTreasureFromPlayer", clienttranslate('${player_name_id} chooses to keep ${card1} and ${You} get ${card2}'), array(
                            'treasure_id' => $treasure_ids_other[$i],
                            'treasure_name' => $this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_other[$i])]['name'],
                            'card1' => 'treasure_'.$this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_selected[$i])]['color'],
                            'card2' => 'treasure_'.$this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_other[$i])]['color'],
                            'player_name_id' => $player_id_active,
                            'player_id_from' => $player_id_active,
                            'treasure_id2' => $treasure_ids_selected[$i],
                            'treasure_location' => $position_other["positions"][$i],
                            'card_info' => $card_info,
                            'You'=>'You'
                        ) );
                    } else {
                        self::notifyPlayer($player, "treasureHandle", clienttranslate('${player_name_id} chooses to keep ${card1} and ${player2_name_id} gets ${card2}'), array(
                            'treasure_id1' => $treasure_ids_selected[$i],
                            'treasure_id2' => $treasure_ids_other[$i],
                            'card1' => 'treasure_'.$this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_selected[$i])]['color'],
                            'card2' => 'treasure_'.$this->treasures[$this->getItemTypeById('treasure',  $treasure_ids_other[$i])]['color'],
                            'player_name_id' => $player_id_active,
                            'player2_name_id' => $player_id_other,
                            'player_id' => $player_id_other,
                            'player_from' => $player_id_active,
                        ) );
                    }
                }
            }

            //state transition
            if (($position_active['needToSell'] > 0) || ($position_other['needToSell'] > 0) ) {
                $this->gamestate->setPlayersMultiactive( $playersToActivateNext, "confirmAndSell" );
                $this->gamestate->nextState("confirmAndSell");
            } else {
                $this->gamestate->nextState( "confirm" );
            }
        }
    }

    function playTreasureCard($treasure_id, $sell) {
        self::checkAction( 'playTreasureCard' );
        $player_id = self::getActivePlayerId();

        // check if card present
        $sql = "SELECT treasure_location_arg locarg, treasure_type spectype FROM treasure WHERE treasure_location = '$player_id' AND treasure_id = '$treasure_id' ";
        $treasure_info = self::getObjectFromDB($sql);

        if  ($treasure_info === null ) {
            throw new BgaUserException( self::_("This treasure card is not in your hand, hit F5 to update") );
        }

        if ($sell) {        // only get money and discard
            $this->playerSellTreasure($treasure_id, $player_id, $treasure_info);
            // cancel client state
            self::notifyPlayer($player_id, "cancelClientState", '', array(
            ) );

        } else {            // play effect
            $effect = $this->treasures[$treasure_info['spectype']]['effect'];
            if ($effect === null) {
                throw new BgaUserException( self::_("This card has no effect, you can only sell it") );
            }

            // check second card in case of treasure map
            if ( $this->treasures[$treasure_info['spectype']]["name"] == "Treasure Map"  ) {
                $sql = "SELECT COUNT(treasure_id) FROM treasure WHERE treasure_location = '$player_id' ";
                $treasure_count = self::getUniqueValueFromDB($sql);
                if ($treasure_count < 2) {
                    throw new BgaUserException( self::_("You need at least 2 cards to play Treasure Map") );
                }
            }
            $this->playerPlayTreasureEffect($treasure_id, $player_id, $treasure_info);
        }
    }

    function selectQuest($quest_id) {
        self::checkAction( 'selectQuest' );
        $player_id = self::getActivePlayerId();

        // check if present
        $sql = "SELECT specialistandquest_id id, specialistandquest_location_arg locarg FROM specialistandquest WHERE specialistandquest_id = '$quest_id' AND specialistandquest_location = 'board' AND specialistandquest_visible = 1 ";
        $quest = self::getObjectFromDB($sql);

        if  ($quest === null ) {
            throw new BgaUserException( self::_("This quest card is not available, hit F5 to update") );
        }

        if ($this->gamestate->state()['name'] == 'playerSpecialistCraftAction') {
            // check if player can place thug
            $mapper = new kgActionMapper($player_id, $this);
            if ( !$mapper->isSpecificSpecialistPresent('Thug') ) {
                throw new BgaUserException( self::_("You don't have Thug in your guild, hit F5 to update") );
            }
            // place thug on quest and inform other players
            $sql = "UPDATE tokens SET token_location = 'quest', token_location_arg = '$quest_id' WHERE token_type = 'thug' ";
            self::dbQuery( $sql); 
            self::notifyAllPlayers( "moveThug", clienttranslate('${player_name_id} places Thug on quest'), array(
                'player_id' => $player_id,
                'player_name_id' => $player_id,                         
                'quest_id' => $quest_id,
                'move_back' => false,
            ) );

            // update after craft actions
            $this->updateAfterCraftActions($player_id);

        } else { // Witch action
            $quest_type = $this->getItemTypeById('quest', $quest_id);

            // check if Thug is on questcard
            $sql = "SELECT token_type_arg thugplayer, token_location_arg locarg FROM tokens WHERE token_type = 'thug' AND token_location = 'quest' ";
            $thug = self::getObjectFromDB( $sql );
            if ($thug['locarg'] == $quest_id) {
                $thug = $thug['thugplayer'];
            } else {
                $thug = null;
            }

            if ($thug) {
                $thugId = self::getUniqueValueFromDb("SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'specialist' AND specialistandquest_type_arg = '11' " ); 
                $sql = "UPDATE tokens SET token_location = 'specialist', token_location_arg = '$thugId' WHERE token_type = 'thug' ";   
                self::dbQuery($sql);

                // return thug item
                self::notifyAllPlayers( "moveThug",'', array(
                    'move_back' => true,
                ) );
            }

            // give quest to player
            $sql = "UPDATE specialistandquest SET specialistandquest_location = '$player_id' WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$quest_id' ";
            self::dbQuery( $sql); 

            //notify
            self::notifyAllPlayers( "craftItem", clienttranslate('${player_name_id} takes ${questName}'), array(
                'player_id' => $player_id,
                'player_name_id' => $player_id,   
                'quest_completed' => true,                     
                'quest_id' => $quest_id,
                'questName' => $this->quest[$quest_type]['nameTr'],
                'i18n' => array('questName'),
                'sigilToAdd' => null,
            ) );

            self::setGameStateValue('newQuestCardPosition',  $quest['locarg']);

            //transition
            self::setGameStateValue( 'placed_specialist_type', 0);
        }
        
        $this->gamestate->nextState( "selectQuest" ); 
    }

    function oracleAction() {
        self::checkAction( 'oracleAction' );
        $player_id = self::getActivePlayerId();
        $mapper = new kgActionMapper($player_id, $this);

        if ( !$mapper->isSpecificSpecialistPresent('Oracle') ) {
            throw new BgaUserException( self::_("You don't have Oracle in your guild, hit F5 to update") );
        }

        $sql = "SELECT specialistandquest_id id, specialistandquest_type_arg spectype FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_location = 'board' AND specialistandquest_visible = '0' ORDER BY specialistandquest_id ASC LIMIT 2";
        $result = self::getObjectListFromDB( $sql);

        $card1 = null;
        $card2 = null;
        if (count($result) > 1 ) {
            $card1 = $this->quest[$result[0]['spectype']];
            $card2 = $this->quest[$result[1]['spectype']];
        } elseif (count($result) == 1 ) {
            $card1 = $this->quest[$result[0]['spectype']];
        }

        self::notifyPlayer( $player_id,"oracleShow", '' , array(
            'questCard1' => $card1,
            'questCard2' => $card2,                         
        ) );
    }

    function passAction() {
        self::checkAction( 'pass' );
        $player_id = self::getActivePlayerId();
        if ($this->gamestate->state()['name'] == 'playerPlayTreasureEffect') {
            self::setGameStateValue('played_treasure_card', 0);
        } elseif ($this->gamestate->state()['name'] == 'playerSpecialistCraftAction' && self::getGameStateValue('appraiser_active') == 1 ) {
            self::setGameStateValue('appraiser_active', 0);
        } elseif (self::getGameStateValue('transition_from_state') == 7 ||  self::getGameStateValue('specialist_craft_action_played') == 1 ) {
            // update after craft actions
            // $player_id = self::getActivePlayerId();
            $this->updateAfterCraftActions($player_id);
            // $player_id = self::getActivePlayerId();
            // $sql = "SELECT player_specialist_craftaction speccraft FROM player WHERE player_id = '$player_id' ";
            // $specialist_craftaction = array_slice(explode("_", self::getUniqueValueFromDB($sql)), 1);
            // $specialist_craftaction = implode("_", $specialist_craftaction);
            // $sql = "UPDATE player SET player_specialist_craftaction = '$specialist_craftaction' WHERE player_id = '$player_id' ";
            // self::dbQuery($sql);
        } elseif ($this->gamestate->state()['name'] == 'playerExpand' && self::getPlayersNumber() == 1) {
            self::setGameStateValue('soloExpandSecondPart', 0);
            self::setGameStateValue('playerEndPhase', 1);
            $this->soloGameExpandActionEnd();
        } else {
            self::setGameStateValue('placed_specialist_type', 0);
        }
        self::giveExtraTime( $player_id);

        if ($this->gamestate->state()['name'] == 'playerBuildRoomOnly' || $this->gamestate->state()['name'] == 'playerHireSpecialistOnly') {
            $player_id = self::getActivePlayerId();
            $mapper = new kgActionMapper($player_id, $this);

            if ($mapper->willPlayOnEnd()) {
                $this->gamestate->nextState( "passEnd" ); 
            } else {
                $this->gamestate->nextState( "pass" ); 
            }
        } else {
            $this->gamestate->nextState( "pass" );  
        }
    }

    function makeBid($bid) {
        self::checkAction( 'makeBid' );
        $player_id = self::getCurrentPlayerId();
        // save bid to db and deactivate player
        $sql = "UPDATE player SET player_funeralbid = '$bid' WHERE player_id = '$player_id' ";
        self::dbQuery($sql);

        $this->gamestate->setPlayerNonMultiactive( $player_id, 'makeBid' );
    }

    function makeOffering() {
        self::checkAction( 'makeOffering' );
        $player_id = self::getCurrentPlayerId();

        // check if already done
        $sql = "SELECT player_offering FROM player WHERE player_id = '$player_id' ";
        if ( self::getUniqueValueFromDB($sql) == 1 ) {
            throw new BgaUserException( self::_("You already made an offering to the Council") );
        }

        // check free sigil
        $sql = "SELECT token_id id FROM tokens WHERE token_type = 'sigil' AND token_type_arg = '$player_id' AND token_location = 'free'  ";
        $sigils = self::getCollectionFromDB( $sql); 
        if ( empty($sigils) ) {
            throw new BgaUserException( self::_("You don't have free sigil to make offering") );
        }
        $sigil_id = array_keys($sigils)[0];

        //store offering
        $sql = "UPDATE player SET player_offering = '1' WHERE player_id = '$player_id' ";
        self::dbQuery($sql);

        // get ID of offering card
        $sql = "SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_type_arg = '50' ";
        $quest_id = self::getUniqueValueFromDB( $sql); 
        $sigil_loc = 'quest_'.$quest_id;

        $sql = "UPDATE tokens SET token_location = '$sigil_loc', token_location_arg = '' WHERE token_id = '$sigil_id' ";
        self::dbQuery($sql);

        // notify
        self::notifyAllPlayers( "makeOffering", clienttranslate('${player_name_id} makes Offering to the Council'), array(
            'player_id' => $player_id,
            'player_name_id' => $player_id,                         
            'quest_id' => $quest_id,
            'sigilToAdd' => "sigil_".$player_id."_".$sigil_id,
        ) );


        $mapper = new kgActionMapper($player_id, $this);
        if ($mapper->isSpecificSpecialistPresent('Vizier')) {
                // do not end players turn if Vizier present
        } else {
            $this->gamestate->nextState( "makeOffering" ); 
        }
    }

    function endTurn() {
        self::checkAction( 'endTurn' );
        $player_id = self::getActivePlayerId();
        $this->gamestate->nextState( "endTurn" ); 
    }

    function soloFuneral() {
        self::checkAction( 'soloFuneral' );
        if (self::getGameStateValue('soloKingsFuneral') != 1) {
            throw new BgaUserException( self::_("Not possible move, hit F5 to update") );
        }

        self::setGameStateValue('soloKingsFuneral', 0);
        $this->gamestate->nextState( "soloFuneral" ); 
    }
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argPlayerGuildTurn() {
        $active_players = $this->gamestate->getActivePlayerList();

        foreach($active_players as $player_id) {
            $guild = $this->getPLayerGuild($player_id);

            $stateToSwitch = $this->guilds[$guild]['startBonus']['clientState'];
            $buttons = $this->guilds[$guild]['startBonus']['action_buttons'];

            if ($this->guilds[$guild]['name'] == "The Holy Order") {
                $obj = new kgActionMapper($player_id, $this);
                $result[$player_id]['stateArgs']['highligth'] = $obj->getPossiblePositionsForRooms();

                $sql = "SELECT room_id id FROM room WHERE room_type = '23' ";  // get ID of shrine room
                $result[$player_id]['stateArgs']['roomId']= self::getUniqueValueFromDB($sql);
            }

            $result[$player_id]['stateToSwitch'] = $stateToSwitch;
            $result[$player_id]['stateArgs']['actionButtons']= $buttons;
        }

        return $result;
    }

    function argPlayerTurn() {
        $player_id = self::getActivePlayerId();
        $mapper = new kgActionMapper($player_id, $this);
        $result = array();
        // Bard is present
        if ( $mapper->isSpecificSpecialistPresent('Bard') ) {
            $result['bardActionActive'] = true;
            $result['possibleTiles'] = $mapper->getPossiblePositionsForSpecialist();
        }
        //Oracle is present
        if ( $mapper->isSpecificSpecialistPresent('Oracle') ) {
            $result['oracleActionActive'] = true;
        }

        // last turn - make offering possible adn check for Vizier
        if ( self::getGameStateValue( 'offeringActive') == 1) {
            $result['offeringActive'] = true;
            if ( $mapper->isSpecificSpecialistPresent('Vizier') ) {
                $result['vizierActive'] = true;
            }
        }

        // get players action bonuses
        $result['bonuses'] = $mapper->getActionBonuses();

        // solo game kings funeral
        $result['soloKingsFuneral'] =  self::getGameStateValue('soloKingsFuneral');

        return $result;
    }

    function argPlayerGather() {
        $mapper = new kgActionMapper(self::getActivePlayerId(), $this);
        $result['gatherBonus'] = $mapper->getGatherBonus();
        return $result;
    }

    function argPlayerExpand() {
        if (self::getGameStateValue('soloExpandSecondPart') == 1 && self::getPlayersNumber() == 1 ) {
            self::notifyAllPlayers( "soloExpand",'', array(
            ) );
        }
    }

    function argPlayerSelectTreasureCard() {
        $player_id = self::getActivePlayerId();
        $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' AND treasure_location_arg >= 20";
        $result['possible_treasures'] = self::getObjectListFromDB($sql, true);
        $result['card_number'] = count($result['possible_treasures'])/2;
        return $result;
    }

    function argPlayerCraft() {
        
    }

    function argPlayerReplaceBonusResource() {
        $player_id = self::getActivePlayerId();
        $sql = "SELECT player_replace_res res FROM player WHERE player_id = '$player_id' ";
        $res_string = self::getUniqueValueFromDB($sql);
        if( substr( $res_string, -1 ) == '_' ) {
            $res_string = substr( $res_string, 0, -1 );
          }
        $res_array = explode("_", $res_string);

        // $result['bonus_resource'] = array_pop($res_array);    
        
        $result['bonus_resource'] = $res_array;  
        $result['number'] = count($res_array);  

        return $result;
    }

    function argPlayerSellTreasure() {
        $active_players = $this->gamestate->getActivePlayerList();
        // if (empty($active_players)) {
        //     $active_players[] = self::getCurrentPlayerId();
        // }
        
        foreach($active_players as $player_id) {
            if (self::getGameStateValue('appraiser_active') == 1 ) {
                $sql = "SELECT treasure_id id, treasure_location_arg loc FROM treasure WHERE treasure_location = '$player_id' ";
                $all_player_cards = self::getObjectListFromDB($sql);
                $ids = array_column($all_player_cards, 'id');
                $result[$player_id] = array( "cards_needed_to_sell" => 1, "possible_treasures" => $ids );
                $result['appraiser'] = true;
            } else {
                $mapper = new kgActionMapper($player_id, $this);
                
                $sql = "SELECT treasure_id id, treasure_location_arg loc FROM treasure WHERE treasure_location = '$player_id' ";
                $all_player_cards = self::getObjectListFromDB($sql);
            
                $new_cards = array_column(array_filter($all_player_cards, function($elem){ return $elem['loc'] >= 20; } ), 'id');
                $number = $mapper->getPositionForTreasureCards(count($new_cards))['needToSell'];
                $ids = array_column($all_player_cards, 'id');
                $result[$player_id] = array( "cards_needed_to_sell" => $number, "possible_treasures" => $ids );
            }
        }

        return  $result;
    }

    function argPlayerSpecialistOneTimeAction() {
        $player_id = self::getActivePlayerId();
        $placed_specialist = self::getGameStateValue('placed_specialist_type');
        $result['action_name'] = $this->specialist[$placed_specialist]['ability']["onetimeaction"][0];
        $mapper = new kgActionMapper($player_id, $this);

        if ( array_key_exists( 'action_buttons',  $this->specialist[$placed_specialist]['ability']["onetimeaction"])) {
            $result['parameters']['actionButtons'] = $this->specialist[$placed_specialist]['ability']["onetimeaction"]['action_buttons'];
        }

        if ( $result['action_name'] == 'gather') {
            $result['parameters']['replaceTrigger'] = false;
            $result['parameters']['maxReached'] = false;
            $result['parameters']['dealerPass'] = true;
        }

        if ( $result['action_name'] == 'traderesources') {
            // $sql = "SELECT * FROM tokens WHERE ( token_location = '$this->player_id' ) AND ( token_type = 'baseresource' OR token_type = 'advresource') ";
            // $result = self::getObjectListFromDB( $sql, 'getObjectListFromDB' );
            $result['parameters']['forSell'] = null;
            $result['parameters']['forBuy'] = null;
        }

        if ( $result['action_name'] == 'placeBaggage') {
            $result['parameters']['possibleTiles'] = $mapper->getPossiblePositionsForSpecialist('baggage');
            $sql = "SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type_arg = '37' AND specialistandquest_type = 'specialist'  ";
            $result['parameters']['item_id'] =   self::getUniqueValueFromDB($sql);
        }

        if ( $result['action_name'] == 'placeSpecialist') {
            $result['parameters']['possibleTiles'] = $mapper->getPossiblePositionsForSpecialist();
            $sql = "SELECT specialistandquest_id id, specialistandquest_type_arg arg FROM specialistandquest WHERE specialistandquest_type = 'specialist' AND specialistandquest_location = 'board' AND specialistandquest_location_arg = 4 ";
            $specialist = self::getObjectFromDB($sql);
            $result['parameters']['tile_from'] =  'tile_specialist_4';
            if ($specialist === null) {
                $sql = "SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'specialist' AND specialistandquest_location = 'board' AND specialistandquest_location_arg = 3 ";
                $specialist = self::getUniqueValueFromDB($sql);
                $result['parameters']['tile_from'] =  'tile_specialist_3';
            }
            
            if ($specialist === null || $specialist['arg'] == 27 ) {
                $result['parameters']['cancel'] = true;
            } else {
                $result['parameters']['cancel'] = false;
            }

            $result['parameters']['item_id'] =  $specialist['id'];
        }

        if ( $result['action_name'] == 'steal') {
            $result['parameters']['selected'] = null;
            if ($mapper->checkGather(['wood'], true)['triggerReplace'] > 0) {
                $result['parameters']['triggerReplace'] = 1;
            } else {
                $result['parameters']['triggerReplace'] = false;
            }
            $result['parameters']['selectedForReplace'] = null;
        }

        if ( $result['action_name'] == 'select_questcard') {
            $sql = "SELECT specialistandquest_id id, specialistandquest_type_arg t FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_location = 'board' AND specialistandquest_visible = 1 AND specialistandquest_type_arg <> 50";
            $questOnBoard = self::getCollectionFromDB($sql, true);
            $possibleQuests = array();
            foreach($questOnBoard as $id => $type) {
                if ( count($this->quest[$type]['items']) < 2 ) {
                    $possibleQuests[] = $id;
                }
            }

            $result['parameters']['possibleQuests'] = $possibleQuests;
        }

        if ( $result['action_name'] == 'take3give3') {
            $sql = "SELECT treasure_id id, treasure_location_arg locarg FROM treasure WHERE treasure_location = '$player_id' ";
            $player_treasures = self::getObjectListFromDB($sql);

            $notYetSelected = true;
            $possible_treasures = array();
            // search for already selected 3 cards
            foreach ($player_treasures as $treasure) {
                $possible_treasures[] = $treasure['id'];
                if ($treasure['locarg'] >= 20 ) {
                    $notYetSelected = false;
                }
            }
            
            if ($notYetSelected) {
                $buttons = array('blue','red', 'yellow');
                $result['parameters']['actionButtons'] = $this->combos($buttons, 3);
            } else {
                $result['action_name'] = 'return3cards';
                $result['parameters']['possible_treasures'] = $possible_treasures;
                $result['parameters']['cards_needed_to_sell'] = 3;
            }
        }

        if ( $result['action_name'] == 'buydiscardrelics' ) {
            $sql = "SELECT treasure_id id, treasure_type t FROM treasure WHERE treasure_location = 'discard' ";
            $pdiscarded_treasures = self::getObjectListFromDB($sql);
            $possible_treasures = array();
            // search for relics
            foreach ($pdiscarded_treasures as $treasure) {
                if (  $this->treasures[$treasure['t']]['cathegory']  == 'Relic' ) {
                    $possible_treasures[] = $treasure['id'];
                }
            }

            $result['parameters']['possible_treasures'] = $possible_treasures;
        }
        
        return $result;
    }

    function argPlayerSpecialistCraftAction() {
        $player_id = self::getActivePlayerId();

        if ( self::getGameStateValue('appraiser_active') == 1 ) {
            $specialist_name = $this->specialist[1]['name'];

            $result['action_name'] = $this->specialist[1]['ability']["aftertreasuregain"][0];
            $result['specialist'] = $specialist_name;
            $result['parameters']['specialist_name'] = $specialist_name;
            $result['parameters']['actionButtons'] = array('blue', 'red', 'yellow');

        } else {
            $sql = "SELECT player_specialist_craftaction speccraft FROM player WHERE player_id = '$player_id' ";
            $actual_specialist = explode("_", self::getUniqueValueFromDB($sql) )[0];
            $specialist_name = $this->specialist[$actual_specialist]['name'];

            $result['action_name'] = $this->specialist[$actual_specialist]['ability']["craftaction"][0];
            $result['parameters']['specialist'] = $specialist_name;
            
            if ( $result['action_name'] == 'goldfortreasure' ) {
                $result['parameters']['actionButtons'] = array($this->specialist[$actual_specialist]['ability']["craftaction"][2]);
            }

            if ( $result['action_name'] == 'destroyscroll' ) {
                $mapper = new kgActionMapper($player_id, $this);
                $result['parameters']['scrolls'] = $mapper->getScrollsIds() ;
            }

            if ( $result['action_name'] == 'thugaction' ) {
                $sql = "SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_location = 'board' AND specialistandquest_visible = 1 ";
                $questOnBoard = self::getObjectListFromDB($sql, true);

                $result['parameters']['possibleQuests'] = $questOnBoard;
            }

            $result['specialist'] = $specialist_name;
        }

        return $result;
    }

    function argPlayerPlayTreasureEffect() {
        $treasure_id = self::getGameStateValue( 'played_treasure_card');
        $sql = "SELECT treasure_location loc, treasure_location_arg locarg, treasure_type spectype FROM treasure WHERE treasure_id = '$treasure_id' ";
        $treasure_info = self::getObjectFromDB($sql);
        
        $player_id = self::getActivePLayerId();
        $second_player_id = self::getGameStateValue('second_player_treasurePlay');
        if (self::getGameStateValue('warlock_active') == 1) {
            $second_player_id = -1;
        }

        $effect = $this->treasures[$treasure_info['spectype']]['effect'];
        $result['selectedCard'] = $treasure_id ;

        if (key($effect) == 'gain') {  //replace res
            $mapper = new kgActionMapper($player_id, $this);
            $sql = "SELECT player_replace_res FROM player WHERE player_id = '$player_id' ";
            $res_string = self::getUniqueValueFromDB($sql);
            if( substr( $res_string, -1 ) == '_' ) {
                $res_string = substr( $res_string, 0, -1 );
              }
            $res_array = explode("_", $res_string);

            $result['switchToState'] = 'replaceRes';
            $result['parameters']['selectedResources'] = $res_array;
            $result['parameters']['alreadySelected'] = array();
            $result['parameters']['number'] = $mapper->checkGather($res_array, true)['triggerReplace'];
        }

        if (key($effect) == 'gain2resource') {  //choose 2 same resource / 2nd player choose one
            $mapper = new kgActionMapper($player_id, $this);
            // $mapper_second = new kgActionMapper($player_id_second, $this);

            $result['switchToState'] = 'chooseResources';
            $set = $player_id == $second_player_id ? 1:0;

            //check res availability
            $revisedButtons = $effect['gain2resource'][$set];             
            $result['parameters']['triggerReplace'] = array();
            for( $i=0;$i<count($effect['gain2resource'][$set]);$i++) {
                if ($set == 0){
                    $res = $revisedButtons[$i][1];
                } else {
                    $res = $revisedButtons[$i];
                }
                $sql = "SELECT count(token_id) c FROM tokens WHERE token_type_arg = '$res' AND token_location = 'board' ";
                $res_count = self::getUniqueValueFromDB( $sql );

                if ($set == 0) {
                    if ($mapper->checkGather(array($res), false) === false ) {           // exclude res that cannot be gathered by player
                        unset($revisedButtons[$i]);
                    } else {
                        if ($res_count == 0) {
                            unset($revisedButtons[$i]);
                        }
                        elseif ($res_count < count($revisedButtons[$i]) ) {
                            unset($revisedButtons[$i][count($revisedButtons[$i])-1] );
                            $result['parameters']['triggerReplace'][] = $mapper->checkGather($revisedButtons[$i], true)['triggerReplace'];
                        } else {
                            $result['parameters']['triggerReplace'][] = $mapper->checkGather($revisedButtons[$i], true)['triggerReplace'];
                        }
                    }
                } else {
                    if ($mapper->checkGather(array($res), false) === false ) { // exclude res that cannot be gathered by player
                        unset($revisedButtons[$i]);
                    } else {
                        if ($res_count == 0) {
                            unset($revisedButtons[$i]);
                        } else {
                            $revisedButtons[$i] = array($revisedButtons[$i]);
                            $result['parameters']['triggerReplace'][] = $mapper->checkGather($revisedButtons[$i], true)['triggerReplace'];
                        }
                    }
                }
            }
            $result['parameters']['actionButtons'] = array_values($revisedButtons);                           //!!!!!!!!!!
            // $result['parameters']['triggerReplace'] = $mapper->checkGather($effect['gain2resource'][0][0], true)['triggerReplace'];
        }

        if (key($effect) == 'gainAndDraw') {        // gain 1 and draw treasure, 2nd pl draw different treasure
            if ($player_id != $second_player_id) {
                $result['parameters']['actionButtons'] = $effect['gainAndDraw'];
                $result['switchToState'] = 'takeTreasureFromCard';
            } else {                        // exclude already chosen color
                $result['switchToState'] = 'takeTreasureFromCardPay';
                $result['parameters']['actionButtons'] = $effect['gainAndDraw'];
                $exclude = $this->getStringCardColor(self::getGameStateValue( 'alreadySelected_treasureCard'));
                if (($key = array_search($exclude, $result['parameters']['actionButtons'])) !== false) {
                    unset( $result['parameters']['actionButtons'][$key]);
                    $result['parameters']['actionButtons'] = array_values($result['parameters']['actionButtons']);
                }
            }
        }

        if (key($effect) == 'drop') {        // choose 1 res to lose
                $result['switchToState'] = 'dropRes';
                $result['parameters']['actionButtons'] = $effect['drop'];
        }

        if (key($effect) == 'hireSpecialist') {      
            $result['switchToState'] = 'hireSpecialist';
            // $result['parameters']['actionButtons'] = $effect['drop'];
        }

        if (key($effect) == 'discardTreasure') {  
            if ( self::getGameStateValue("selected_treasure_card_discard") == 0 ) {
                $sql = "SELECT treasure_id id FROM treasure WHERE treasure_location = '$player_id' ";
                $all_player_cards = self::getObjectListFromDB($sql, true);
                $filtered_cards = array_values(array_diff($all_player_cards, array($treasure_id)));

                $result['switchToState'] = 'discardTreasure';
                $result['parameters']['possible_treasures'] = $filtered_cards;
                $result['parameters']['selected'] = 'treasure_'.$treasure_id; 
                $result['card_number'] = 1;
            } else {
                $result['switchToState'] = 'take2treasures';
                $result['parameters']['actionButtons'] = $effect["discardTreasure"];
                $result['parameters']['selected'] = array('treasure_'.$treasure_id, 'treasure_'.self::getGameStateValue("selected_treasure_card_discard") ); 
            }
        }


        return $result;
    }

    function argKingsFuneralBidding() {
        // $active_players = $this->gamestate->getActivePlayerList();

        $active_players = self::getObjectListFromDB( "SELECT player_id id FROM player", true );
        // if (empty($active_players)) {
        //     $active_players[] = self::getCurrentPlayerId();
        // }
        
        foreach($active_players as $player_id) {
            $mapper = new kgActionMapper($player_id, $this);
            $result[$player_id] = $mapper->getPlayerGold();
        }
        return $result;
    }

    function argPlayerPlaceKingStatue() {
        $player_id = self::getActivePLayerId();
        $mapper = new kgActionMapper($player_id, $this);
        $result = array();

        $result['action_name'] = 'placeStatue';
        $result['parameters']['possibleTiles'] = $mapper->getPossiblePositionsForRooms();
        $sql = "SELECT room_id FROM room WHERE room_type = '24' "; 
        $result['parameters']['item_id'] =   self::getUniqueValueFromDB($sql);

        return $result;
    }

    function argPlayerEndTurn() {
        $player_id = self::getActivePLayerId();
        $mapper = new kgActionMapper($player_id, $this);
        $result = array();
        if ( $mapper->isSpecificSpecialistPresent('Bard') ) {
            $result['bardActionActive'] = true;
            $result['possibleTiles'] = $mapper->getPossiblePositionsForSpecialist();
        }

        if ( $mapper->isSpecificSpecialistPresent('Oracle') ) {
            $result['oracleActionActive'] = true;
        }

        if ( self::getGameStateValue( 'offeringActive') == 1) {
            if ( $mapper->isSpecificSpecialistPresent('Vizier') ) {
                $result['vizierActionActive'] = true;
            }
        }

        return $result;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

    function stGameStart() {
        $this->getNextPlayerAtGuildBonusPhase( self::getActivePLayerId() );
    }

    function stgameActionSelection() {      // flow: 1 check switch after Appraiser 2 check Appraiser 3 Check draw new quest 4 rest
        if ( self::getGameStateValue('appraiser_active') == 0 && self::getGameStateValue('player_play_appraiser') != -1) {     // switch back after Appraiser change of active players
            $this->gamestate->changeActivePlayer( self::getGameStateValue('player_play_appraiser') );
            self::setGameStateValue('player_play_appraiser', -1); 
        }

        if ( self::getGameStateValue('activePlayerAfterBidding' ) != -1 &&  self::getGameStateValue('activePlayerAfterBidding' ) != self::getActivePlayerId() ) { // switch back after funeral bidding
            $this->gamestate->changeActivePlayer( self::getGameStateValue('activePlayerAfterBidding') );
            self::setGameStateValue('activePlayerAfterBidding', -1);
        }

        $player_id = self::getActivePlayerId();
        self::giveExtraTime( $player_id);

        //check Appraiser action
        if ( self::getGameStateValue('appraiser_active') == 1) {
            $playerToPlay = self::getGameStateValue('player_play_appraiser');
            if ( $playerToPlay == $player_id) {
                $this->gamestate->nextState( 'appraiser' ); 
            } else {
                self::setGameStateValue('player_play_appraiser', $player_id); // shared completed quest and 2nd player has Appraiser -> switch players
                $this->gamestate->changeActivePlayer( $playerToPlay );
                $this->gamestate->nextState( 'appraiser' );
            }
            return;
        }

        if (self::getPlayersNumber() > 1) {                     // solo game
            // check for quest card draw  
            if (self::getGameStateValue('offeringActive') != 1) {
                if (self::getGameStateValue( 'newQuestCardPosition') > -1 ) {
                    $questResult = $this->drawNewCard('quest', null, self::getGameStateValue( 'newQuestCardPosition') );
                    if ($questResult === null) {
                        self::setGameStateValue( 'newQuestCardPosition', -1);
                    } else {        // transition to Funeral/Offering
                        if ( $questResult == 'kingsFuneral') {
                            self::setGameStateValue('activePlayerAfterBidding', $player_id);
                            // $this->gamestate->setAllPlayersMultiactive(  );
                            $this->gamestate->nextState( 'funeral' );
                            return;
                        }
    
                        if ( $questResult == 'offering') {
                            // mark game end
                            self::setGameStateValue('offeringActive', 1);
                        }
                    }
                }
            }
        }

        $transition_from = self::getGameStateValue('transition_from_state');
        $placed_specialist = self::getGameStateValue('placed_specialist_type');
        $specialist_craft_action_played = self::getGameStateValue('specialist_craft_action_played');

        $sql = "SELECT player_specialist_craftaction speccraft, player_replace_res res  FROM player WHERE player_id = '$player_id' ";
        $playerState = self::getObjectListFromDB($sql)[0];
        $replace = $playerState['res'];
        $specialist_craftaction = $playerState['speccraft'];

        $mapper = new kgActionMapper($player_id, $this);
        $transitionToPlayerEnd = $mapper->willPlayOnEnd(); 

        if ($transition_from == 5 ) {                   // gather action
            // check for bonus resources, check for res replacement
            $bonus_res = $mapper->getGatherBonus();

            if (empty($bonus_res) || $replace == '__' ) {
                self::setGameStateValue('playerEndPhase', 1);
                if ($transitionToPlayerEnd) {
                    $this->gamestate->nextState( 'playerEndTurn' );
                } else {
                    $this->gamestate->nextState( 'nextPlayer' );
                }
            } 
            if (!empty($bonus_res) && $replace == null) {
                // bonus not available!!                    
                foreach($bonus_res as $resource){
                    $sql = "SELECT count(token_id) c FROM tokens WHERE token_type_arg = '$resource' AND token_location = 'board' ";
                    $result = self::getUniqueValueFromDB( $sql );
                    if ($result < 1  ) {
                        // res not available, no bonus
                        self::notifyAllPlayers("logInfo", clienttranslate( 'No available ${resource} ${player_name_id} gets no bonus' ), array(
                            'player_name_id' => $player_id,
                            'resource' =>   'resource_'.$resource,
                        ) );
                        unset($bonus_res[  array_search ($resource, $bonus_res) ] );
                    }
                }
                $bonus_res = array_values($bonus_res);

                if (!empty($bonus_res) ) {
                    $replace_number = $mapper->checkGather($bonus_res, true)['triggerReplace'];

                    if ($replace_number > 0) {
                        $db_string = '';
                        for($i=0;$i<count($bonus_res);$i++) {
                            if ($i< (count($bonus_res) - $replace_number) ) {
                                // $this->takeResourceByPlayer($bonus_res[$i], $player_id, true );
                                $this->takeResourceByPlayer(array($bonus_res[$i]), $player_id, true );                          // name of the specialist in the log???????
                            } else {
                                $db_string = $db_string.$bonus_res[$i].'_';
                            }
                        }
                        // save res to be replace to DB
                        $sql = "UPDATE player SET player_replace_res = '$db_string' WHERE player_id = '$player_id' ";
                        self::dbQuery($sql);

                        $this->gamestate->nextState( 'replaceBonusRes' );
                    } else {
                        // foreach($bonus_res as $resource){
                        //     $this->takeResourceByPlayer($resource, $player_id, true );                          // name of the specialist in the log???????
                        // }
                        $this->takeResourceByPlayer($bonus_res, $player_id, true ); 
                        self::setGameStateValue('playerEndPhase', 1);  
                        if ($transitionToPlayerEnd) {
                            $this->gamestate->nextState( 'playerEndTurn' );
                        } else {
                            $this->gamestate->nextState( 'nextPlayer' );
                        }
                    }
                } else {
                    self::setGameStateValue('playerEndPhase', 1);
                    if ($transitionToPlayerEnd) {
                        $this->gamestate->nextState( 'playerEndTurn' );
                    } else {
                        $this->gamestate->nextState( 'nextPlayer' );
                    }
                }
            }
            if (!empty($bonus_res) && $replace != null && $replace != '__') {
                $this->gamestate->nextState( 'replaceBonusRes' );
            }
            return;
        }

        if ($transition_from == 6 && $specialist_craft_action_played == 0) {                    // expand action
            if (self::getGameStateValue('expandAction_roomPlayed') == 1 ) {
                if ( $replace != null && $replace != '__') {
                    $this->gamestate->nextState( 'replaceBonusRes' );
                } else {
                    if (self::getPlayersNumber() > 1) {         // solo game
                        $this->gamestate->nextState( 'specialistonly' );
                    } else {
                        if (self::getGameStateValue('soloExpandSecondPart') == 0 ) {
                            self::setGameStateValue('expandAction_roomPlayed', 0);
                            self::setGameStateValue('expandAction_specialistPlayed', 0);
                            self::setGameStateValue('soloExpandSecondPart', 1);
                            $this->gamestate->nextState( 'soloPlayExpand');
                        } else {
                            self::setGameStateValue('soloExpandSecondPart', 0);
                            self::setGameStateValue('playerEndPhase', 1); 
                            $this->soloGameExpandActionEnd();
                            $this->gamestate->nextState( 'playerEndTurn');
                        }
                    }
                }
            }

            if (self::getGameStateValue('expandAction_specialistPlayed') == 1 ) {
                // check specialist action and one time bonuses                                          
                if ( $placed_specialist  != 0) {
                    if ( key($this->specialist[$placed_specialist]['ability']) == 'onetimebonus' ) { // adventurer check
                        $resolveresult = $this->resolveOneTimeBonus($player_id, $this->specialist[$placed_specialist]['ability']['onetimebonus'], $mapper );
                        if ($resolveresult) {
                            if (self::getPlayersNumber() > 1) {         // solo game
                                $this->gamestate->nextState( 'buildOnly' );
                            } else {
                                if (self::getGameStateValue('soloExpandSecondPart') == 0 ) {
                                    self::setGameStateValue('expandAction_roomPlayed', 0);
                                    self::setGameStateValue('expandAction_specialistPlayed', 0);
                                    self::setGameStateValue('soloExpandSecondPart', 1);
                                    $this->gamestate->nextState( 'soloPlayExpand');
                                } else {
                                    self::setGameStateValue('soloExpandSecondPart', 0);
                                    self::setGameStateValue('playerEndPhase', 1); 
                                    $this->soloGameExpandActionEnd();
                                    $this->gamestate->nextState( 'playerEndTurn');
                                }
                                
                            }
                        } else {
                            $this->gamestate->setPlayersMultiactive( array($player_id), "sellTreasures", true );
                            $this->gamestate->nextState( 'sellTreasures' );
                        }
                    } elseif ( key($this->specialist[$placed_specialist]['ability']) == 'onetimeaction' ) {
                        $this->gamestate->nextState( 'specialAction' );
                    } else {
                        if (self::getPlayersNumber() > 1) {         // solo game
                            $this->gamestate->nextState( 'buildOnly' );
                        } else {
                            if (self::getGameStateValue('soloExpandSecondPart') == 0 ) {
                                self::setGameStateValue('expandAction_roomPlayed', 0);
                                self::setGameStateValue('expandAction_specialistPlayed', 0);
                                self::setGameStateValue('soloExpandSecondPart', 1);
                                $this->gamestate->nextState( 'soloPlayExpand');
                            } else {
                                self::setGameStateValue('soloExpandSecondPart', 0);
                                self::setGameStateValue('playerEndPhase', 1); 
                                $this->soloGameExpandActionEnd();
                                $this->gamestate->nextState( 'playerEndTurn');
                            }
                            
                        }
                    }
                } else {
                    if ( $replace != null && $replace != '__') {
                        $this->gamestate->nextState( 'replaceBonusRes' );
                    } else {
                        if (self::getPlayersNumber() > 1) {         // solo game
                            $this->gamestate->nextState( 'buildOnly' );
                        } else {
                            if (self::getGameStateValue('soloExpandSecondPart') == 0 ) {
                                self::setGameStateValue('expandAction_roomPlayed', 0);
                                self::setGameStateValue('expandAction_specialistPlayed', 0);
                                self::setGameStateValue('soloExpandSecondPart', 1);
                                $this->gamestate->nextState( 'soloPlayExpand');
                            } else {
                                self::setGameStateValue('soloExpandSecondPart', 0);
                                self::setGameStateValue('playerEndPhase', 1); 
                                $this->soloGameExpandActionEnd();
                                $this->gamestate->nextState( 'playerEndTurn');
                            }
                            
                        }
                    }
                }  
                
            }
            return;
        }


        if ($transition_from == 8) {                    // build only action (2nd phase of expand)
            if ( $replace != null && $replace != '__') {
                $this->gamestate->nextState( 'replaceBonusRes' );
            } else {
                self::setGameStateValue('playerEndPhase', 1);
                if ($transitionToPlayerEnd) {
                    $this->gamestate->nextState( 'playerEndTurn' );
                } else {
                    $this->gamestate->nextState( 'nextPlayer' );
                }
            }
            return;
        }

        if ($transition_from == 9  && $specialist_craft_action_played == 0) {                    // specialist only action (2nd phase of expand)
            // check specialist action and one time bonuses                                            
            if ( $placed_specialist  != 0) {
                if ( key($this->specialist[$placed_specialist]['ability']) == 'onetimebonus' ) {    // adventurer check
                    $resolveresult = $this->resolveOneTimeBonus($player_id, $this->specialist[$placed_specialist]['ability']['onetimebonus'], $mapper );
                    if ($resolveresult) {
                        self::setGameStateValue('playerEndPhase', 1);
                        if ($transitionToPlayerEnd) {
                            $this->gamestate->nextState( 'playerEndTurn' );
                        } else {
                            $this->gamestate->nextState( 'nextPlayer' );
                        }
                    } else {
                        $this->gamestate->setPlayersMultiactive( array($player_id), "sellTreasures", true );
                        $this->gamestate->nextState( 'sellTreasures' );
                    }
                } elseif ( key($this->specialist[$placed_specialist]['ability']) == 'onetimeaction' ) {
                    $this->gamestate->nextState( 'specialAction' );
                } else {
                    self::setGameStateValue('playerEndPhase', 1);
                    if ($transitionToPlayerEnd) {
                        $this->gamestate->nextState( 'playerEndTurn' );
                    } else {
                        $this->gamestate->nextState( 'nextPlayer' );
                    }
                }

            } else {
                if ( $replace != null && $replace != '__') {
                    $this->gamestate->nextState( 'replaceBonusRes' );
                } else {
                    self::setGameStateValue('playerEndPhase', 1);
                    if ($transitionToPlayerEnd) {
                        $this->gamestate->nextState( 'playerEndTurn' );
                    } else {
                        $this->gamestate->nextState( 'nextPlayer' );
                    }
                }
            }
            return;            
        }

        if ( $transition_from == 0 || $transition_from == 25) {               // played treasure card Contract -> hire specialist -> one time action
            if ( $placed_specialist  != 0) {
                if ( key($this->specialist[$placed_specialist]['ability']) == 'onetimebonus' ) {  // adventurer check
                    $resolveresult = $this->resolveOneTimeBonus($player_id, $this->specialist[$placed_specialist]['ability']['onetimebonus'], $mapper );
                    if ($resolveresult) {
                        $this->gamestate->nextState( 'finishTreasureCardPlay' );
                    } else {
                        $this->gamestate->setPlayersMultiactive( array($player_id), "sellTreasures", true );
                        $this->gamestate->nextState( 'sellTreasures' );
                    }
                } elseif ( key($this->specialist[$placed_specialist]['ability']) == 'onetimeaction' ) {
                    $this->gamestate->nextState( 'specialAction' );
                } else {
                    $this->gamestate->nextState( 'finishTreasureCardPlay' );
                }
            } else {
                if ( $replace != null && $replace != '__') {
                    $this->gamestate->nextState( 'replaceBonusRes' );
                } else {
                    $this->gamestate->nextState( 'finishTreasureCardPlay' );
                }
            }
            return;
        }
        
        if ($transition_from == 7 || $specialist_craft_action_played == 1) {                    // craft action
            if ($specialist_craftaction === null ) {
                // look for specialist craftactions
                $specialists_with_action = $mapper->getCraftSpecialistAtions(self::getGameStateValue( 'craft_action_hero') );
                if (empty($specialists_with_action)) {
                    // no action after craft, normal transition
                    if ($specialist_craft_action_played == 1) {
                        self::setGameStateValue('specialist_craft_action_played', 0);
                        $this->gamestate->nextState( 'runAgain' ); 
                    } else {
                        self::setGameStateValue('playerEndPhase', 1);
                        if ($transitionToPlayerEnd) {
                            $this->gamestate->nextState( 'playerEndTurn' );
                        } else {
                            $this->gamestate->nextState( 'nextPlayer' );
                        }
                    }
                } else {
                    // actions found -> save them to db and transition to special action
                    $db_actions = implode("_", $specialists_with_action);
                    $sql = "UPDATE player SET player_specialist_craftaction = '$db_actions' WHERE player_id = '$player_id' ";
                    self::dbQuery($sql);
                    $this->gamestate->nextState( 'specialCraftAction' ); 
                }
            } elseif ( $specialist_craftaction == '' ) { // all actions done
                $sql = "UPDATE player SET player_specialist_craftaction = NULL WHERE player_id = '$player_id' ";
                self::dbQuery($sql);
                if ($specialist_craft_action_played == 1) {
                    self::setGameStateValue('specialist_craft_action_played', 0);
                    $this->gamestate->nextState( 'runAgain' ); 
                } else {
                    self::setGameStateValue('playerEndPhase', 1);
                    if ($transitionToPlayerEnd) {
                        $this->gamestate->nextState( 'playerEndTurn' );
                    } else {
                        $this->gamestate->nextState( 'nextPlayer' );
                    }
                }
            } else {
                $this->gamestate->nextState( 'specialCraftAction' ); 
            }
            return;
        }

        if ($transition_from == 20) {                    // solo play King statue placement
            $sql = "UPDATE specialistandquest SET specialistandquest_location = 'discard', specialistandquest_location_arg = '' WHERE specialistandquest_type = 'quest' AND specialistandquest_type_arg = '49'  ";
            self::dbQuery( $sql); 

            $sql = "SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_type_arg = '49'  ";
            $id = self::getUniqueValueFromDB( $sql);

            self::notifyAllPlayers( "moveQuest",  '', array(
                'quest_id' => $id,
                'destination' => 'destroy',
            ) );

            $this->gamestate->nextState( 'nextPlayer' );
            return;
        }
    }

    function stKingsFuneralBidding() {
        $this->gamestate->setAllPlayersMultiactive(  );
    }

    function stNextPlayerPlayTreasure() {               //warlock
        $second_player = self::getGameStateValue('second_player_treasurePlay');
        $warlock = self::getGameStateValue( 'warlock_active');
        $player_id = self::getActivePlayerId();
        self::giveExtraTime( $player_id);
        if ($second_player == -1) {
            self::setGameStateValue( 'played_treasure_card', 0);
            if ( self::getGameStateValue('playerEndPhase') == 1 ) {
                $this->gamestate->nextState( "backToNormalActionsEnd" );  
            } elseif (self::getGameStateValue('expandAction_roomPlayed') || self::getGameStateValue('expandAction_specialistPlayed')) { 
                $this->gamestate->nextState( "backToNormalActionsBetween" ); 
            } else {
                $this->gamestate->nextState( "backToNormalActions" ); 
            }
        } else {
            if (self::getGameStateValue('played_treasure_card') == 0 ){
                self::setGameStateValue('second_player_treasurePlay', -1);
                self::setGameStateValue( 'alreadySelected_treasureCard', -1);
                if ($warlock == 0) {
                    $this->activePrevPlayer();
                    if ( self::getGameStateValue('playerEndPhase') == 1 ) {
                        $this->gamestate->nextState( "backToNormalActionsEnd" ); 
                    } elseif (self::getGameStateValue('expandAction_roomPlayed') || self::getGameStateValue('expandAction_specialistPlayed')) { 
                        $this->gamestate->nextState( "backToNormalActionsBetween" ); 
                    } else {
                        $this->gamestate->nextState( "backToNormalActions" ); 
                    }
                } else {
                    self::setGameStateValue( 'warlock_active', 0);
                    $this->gamestate->nextState( "backToNormalActionsBetween" ); 
                }
            } else {
                if ($warlock == 0) {
                    $this->activeNextPlayer();
                } else {
                    self::setGameStateValue( 'warlock_active', 2);
                }
                $this->gamestate->nextState( "nextPlayer" ); 
            }
        }
    }

    function stResolveFuneralBidding() {
        $sql = "SELECT player_id id, player_funeralbid bid, player_name names FROM player ";
        $playerbids = self::getCollectionFromDB($sql);

        $playerbidvalues = array_column($playerbids, 'bid');
        $playernames = array_column($playerbids, 'names');
        $playerids = array_column($playerbids, 'id');
        $max = max($playerbidvalues);
        $player_id = null;

        // check if only 1p has max
        if (array_count_values($playerbidvalues)[$max] == 1) {
            $index =  array_search ($max, $playerbidvalues);
            $player_id = $playerids[$index];
            self::notifyAllPlayers( "logInfo",clienttranslate( '${player_name_id} wins the bidding: ${gold}' ), array(
                'player_name_id' => $player_id,
                'gold' => 'gold_'.$max,
                ) );
            $this->updatePlayerGold($player_id, -$max, true);

            self::setStat( $max, 'table_kingsStatue' ); 
        } else {
            // no winner of the bidding
            self::notifyAllPlayers( "logInfo",clienttranslate( 'No one wins the bidding' ), array(
            ) );
            self::setStat( $max+1, 'table_kingsStatue' ); 
        }

        $firstRow = array( '' );
        foreach( $playernames as $name ) {
            $firstRow[] = array( 'str' => '${player_name}',
                                 'args' => array( 'player_name' => $name ),
                                 'type' => 'header'
                               );
        }
        
        $table = array();
        $table[] = $firstRow;
        $table[] = array_merge( array( clienttranslate("Gold")), $playerbidvalues);

        $this->notifyAllPlayers( "tableWindow", '', array(
            "id" => 'kingStatueBidding',
            "title" => clienttranslate("The King's Funeral bidding result"),
            "table" => $table, 
            "closing" => clienttranslate( "Close" )
        ) ); 

        //update quest card and notify to destroy
        $sql = "UPDATE specialistandquest SET specialistandquest_location = 'discard', specialistandquest_location_arg = '' WHERE specialistandquest_type = 'quest' AND specialistandquest_type_arg = '49'  ";
        self::dbQuery( $sql); 

        $sql = "SELECT specialistandquest_id id FROM specialistandquest WHERE specialistandquest_type = 'quest' AND specialistandquest_type_arg = '49'  ";
        self::notifyAllPlayers( "discardFuneral", '' , array(
            'funeral_id' => self::getUniqueValueFromDB( $sql),
        ) );

        if ($player_id === null) {
            $this->gamestate->nextState( "nowinner" ); 
        } else {
            $this->gamestate->changeActivePlayer( $player_id );
            $this->gamestate->nextState( "winner" ); 
        }
    }

    function stNextPlayer() {
        $player_id = self::getActivePlayerId();
        self::setGameStateValue("transition_from_state", 0);
        self::setGameStateValue('expandAction_roomPlayed',0);
        self::setGameStateValue('expandAction_specialistPlayed',0);
        self::setGameStateValue( 'bonus_res_replace', 6 );
        self::setGameStateValue( 'placed_specialist_type', 0 );
        self::setGameStateValue( 'craft_action_hero', 0);
        self::setGameStateValue( 'specialist_craft_action_played', 0);
        self::setGameStateValue( 'played_treasure_card', 0);
        self::setGameStateValue( 'second_player_treasurePlay', -1);
        self::setGameStateValue( 'alreadySelected_treasureCard', -1);
        self::setGameStateValue('player_gain_treasure', -1);
        self::setGameStateValue("selected_treasure_card_discard", 0 );
        self::setGameStateValue( 'appraiser_active', 0);
        self::setGameStateValue( 'player_play_appraiser', -1);
        self::setGameStateValue( 'warlock_active', 0);
        self::setGameStateValue( 'newQuestCardPosition', -1);
        self::setGameStateValue('activePlayerAfterBidding',-1 );
        self::setGameStateValue('playerEndPhase', 0);
        self::incStat( 1, "table_turnsNumber");

        $sql = "UPDATE player SET player_replace_res = null, player_active_potions = null WHERE player_id = '$player_id' ";
        self::dbQuery( $sql); 

        // score adjust 
        $mapper = new kgActionMapper($player_id, $this);
        $score = $mapper->calculateScore();
        $scoreN = $score['specialists']+$score['rooms']+$score['quests'];
        self::DbQuery( "UPDATE player SET player_score='$scoreN' WHERE player_id= '$player_id'  " );
        self::notifyAllPlayers( "updateScore",'', array(
            'player_id' => $player_id,
            'value' => $scoreN,
            'inc' => false,
        ) );

        // end game check
        if ( self::getGameStateValue( 'offeringActive') == 1 && self::getGameStateValue( 'playerPlayLast')  != -1 ) {
            if (self::getGameStateValue( 'playerPlayLast') == $player_id) {
                $this->gamestate->nextState("endGame" );
                return;
            }
        }

        if (self::getGameStateValue( 'offeringActive') == 1 && self::getPlayersNumber() == 1) {
            self::setGameStateValue( 'offeringActive', 2);
        }

        if (self::getGameStateValue( 'offeringActive') == 2 && self::getPlayersNumber() == 1) {
            $this->gamestate->nextState("endGame" );
            return;
        }


        // last quest card played - mark last player
        if ( self::getGameStateValue( 'offeringActive') == 1 && self::getGameStateValue( 'playerPlayLast')  == -1 ) {
            self::setGameStateInitialValue('playerPlayLast', $player_id );
        }


        if (self::getPlayersNumber() == 1 ) {
            $this->recalculateQuestPositionsSoloGame();
        }

        $this->activeNextPlayer();
        $this->gamestate->nextState("nextPlayer" );
    }

    function stEndCalculations() {
        $players = self::getCollectionFromDB( "SELECT player_id id, player_name name, player_score score, player_offering offering FROM player" );

        $sql = "SELECT token_id id, token_location loc, token_location_arg locarg, token_type_arg player_id FROM tokens WHERE token_type = 'sigil' AND token_location != 'free'  ";
        $sigils = self::getCollectionFromDB( $sql); 

        //distribute quests with sigils
        foreach($sigils as $sigil) {
            if ($sigil['locarg'] != '' ) {  // sigil is not on offering card
                $quest_id = explode("_", $sigil['loc'])[1];
                $sigil_id = $sigil['id'];
                $player_id = $sigil['player_id'];

                // update sigil
                $sql = "SELECT count(token_id) FROM tokens WHERE token_type = 'sigil' AND token_location = 'free' AND token_type_arg = '$player_id'  ";
                $loc_arg = self::getUniqueValueFromDB( $sql) == 1 ? 1:0; 

                $sql = "UPDATE tokens SET token_location = 'free', token_location_arg = '$loc_arg' WHERE token_id = '$sigil_id' ";
                self::dbQuery($sql);

                //update quest
                $sql = "UPDATE specialistandquest SET specialistandquest_location = '$player_id' WHERE specialistandquest_type = 'quest' AND specialistandquest_id = '$quest_id' ";
                self::dbQuery( $sql); 

                //notify
                self::notifyAllPlayers( "questAndSigilMovement", '', array(
                    'player_id' => $player_id,
                    'quest_id' => $quest_id,
                    'sigil_id' => "sigil_".$player_id."_".$sigil['id'],
                ) );
            }
        }

        //calculate score
        $final_score = array();
        $maxCharm = 0;
        $charmBonus = array();
        foreach($players as $player) {
            $player_id = $player['id'];
            $mapper = new kgActionMapper($player_id, $this);
            $final_score[$player['id']] = $mapper->finalScoring( $player['offering']);

            $player_score = array_sum( $final_score[$player_id]['score'] );
            $final_score[$player['id']]['score']['total'] = $player_score;
            $player_aux_score = $final_score[$player_id]['gold'];

            //update in db
            self::DbQuery( "UPDATE player SET player_score='$player_score', player_score_aux = '$player_aux_score' WHERE player_id= '$player_id'  " );

            self::notifyAllPlayers( "updateScore",'', array(
                'player_id' => $player_id,
                'value' => $player_score,
                'inc' => false,
            ) );

            // charm bonus update
            if (self::getPlayersNumber() != 1) {
                if (  $final_score[$player_id]['charmNumber'] == $maxCharm ) {
                    $charmBonus[] = $player_id;
                }
                if (  $final_score[$player_id]['charmNumber'] > $maxCharm ) {
                    $maxCharm =  $final_score[$player_id]['charmNumber'];
                    $charmBonus = array($player_id);
                }
            }

            // points stats
            self::setStat( $final_score[$player_id]['score']['charms'], "player_charmsSpoints", $player_id);
            self::setStat( $final_score[$player_id]['score']['relics'], "player_relicsPoints", $player_id);
            self::setStat( $final_score[$player_id]['score']['quests'], "player_questPoints", $player_id);
            self::setStat( $final_score[$player_id]['score']['specialists'], "player_specialistsPoints", $player_id);
            self::setStat( $final_score[$player_id]['score']['rooms'], "player_roomsPoints", $player_id);
            self::setStat( $final_score[$player_id]['score']['offering'], "player_offeringPoints", $player_id);
        }

        foreach($charmBonus as $player_id) {
            $final_score[$player_id]['score']['charms'] += 3;
            $final_score[$player_id]['score']['total'] += 3;
            self::incStat( 3, "player_charmsSpoints", $player_id);

            self::DbQuery( "UPDATE player SET player_score= player_score + 3 WHERE player_id= '$player_id'  " );

            self::notifyAllPlayers( "updateScore",'', array(
                'player_id' => $player_id,
                'value' => 3,
                'inc' => true,
            ) );
        }

        $this->createScoreTable($final_score, $players);

        // end game
        $this->gamestate->nextState("");
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
