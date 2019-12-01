<?php
/**
 *------
 * kingsguild implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * kgActionMapper.php
 *
 * functions to resolve possible actions and move on the given player
 * 
 *
 */


class kgActionMapper {

    private $player_id = null;
    private $game = null;

    function __construct($player, $game) {
        $this->player_id = $player;
        $this->game = $game;
    }

    private function getRooms() {
        $sql = "SELECT * FROM room WHERE room_location = '$this->player_id' AND room_side = '1' ";
        $result = $this->game::getDbOutside( $sql, 'getObjectListFromDB' );
        return $result;
    }

    private function getSpecialists() {
        $sql = "SELECT * FROM specialistandquest WHERE specialistandquest_location = '$this->player_id' AND specialistandquest_type = 'specialist' ";
        $result = $this->game::getDbOutside( $sql, 'getObjectListFromDB' );
        return $result;
    }

    private function getResources() {
        $sql = "SELECT * FROM tokens WHERE ( token_location = '$this->player_id' ) AND ( token_type = 'baseresource' OR token_type = 'advresource') ";
        $result = $this->game::getDbOutside( $sql, 'getObjectListFromDB' );
        return $result;
    }

    private function getTreasureCards() {
        $sql = "SELECT * FROM treasure WHERE treasure_location = '$this->player_id'";
        $result = $this->game::getDbOutside( $sql, 'getObjectListFromDB' );
        return $result;
    }

    private function getPlayerMat() {
        $sql = "SELECT player_mat mat FROM player WHERE player_id = '$this->player_id' ";
        $result = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        return $result;
    }

    private function getQuests() {
        $sql = "SELECT * FROM specialistandquest WHERE specialistandquest_location = '$this->player_id' AND specialistandquest_type = 'quest' ";
        $result = $this->game::getDbOutside( $sql, 'getObjectListFromDB' );
        return $result;
    }

    public function isSpecificSpecialistPresent($name) {
        $result = $this->getSpecialists();

        foreach($result as $specialist) {
            $specialist_info = $this->game->specialist[$specialist['specialistandquest_type_arg']];

            if ($specialist_info['name'] == $name) {
                return true;
            }
        }

        return false;
    }

    public function isSpecificRoomTypePresent($id, $type = null) {

        if ($type == null) {
            $sql = "SELECT room_type t FROM room WHERE room_id = '$id' ";
            $room_type = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        } else {
            $room_type = $type;
        }
        $rooms = $this->getRooms();
        
        foreach($rooms as $room) {
            if ($room['room_type'] == $room_type) {
                return true;
            }
        }

        return false;
    }

    public function getMaxResources() {
        $possibleRes = 0;
        $rooms = $this->getRooms();

        foreach($rooms as $room) {
            $room_info = $this->game->rooms[$room['room_type']];
            if($room_info['ability'] != null) {
                if( key($room_info['ability']) == 'tile' && $room_info['ability']['tile'][0] == 'storage') {
                    $possibleRes +=  $room_info['ability']['tile'][1];
                }
            }
        }

        $specialists = $this->getSpecialists();
        foreach($specialists as $specialist) {
            $specialist_info = $this->game->specialist[$specialist['specialistandquest_type_arg']];
            if($specialist_info['ability'] != null) {
                if( key($specialist_info['ability']) == 'tile' && $specialist_info['ability']['tile'][0] == 'storage') {
                    $possibleRes +=  $specialist_info['ability']['tile'][1];
                }
            }
        }

        return $possibleRes;
    }

    public function getResourceCount($type) {

        if ($type == 'all') {
            $sql = "SELECT count(token_id) c FROM tokens WHERE (token_type = 'baseresource' OR token_type = 'advresource') AND token_location = '$this->player_id' ";
            $result = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        } else {
            if($type == 'gem' || $type == 'magic' ) {
                $t = 'advresource';
            } else {
                $t = 'baseresource';
            }

            $sql = "SELECT count(token_id) c FROM tokens WHERE token_type = '$t' AND token_type_arg = '$type' AND token_location = '$this->player_id' ";
            $result = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        }

        return $result;
    }

    private function getAbilityOfElement($elementType, $element, $ability ) {
        $name_matinc = $elementType == 'room' ? 'rooms' : $elementType;
        $name_db = ($elementType == 'room' || $elementType == 'treasuere') ? $elementType.'_type' : 'specialistandquest_type_arg';

        $element_info = $this->game->{$name_matinc}[$element[$name_db]];
        if ( $element_info['ability'] != null ) {
            if(  key($element_info['ability']) == $ability) {
                return  $element_info['ability'][$ability];
            }
        }
        return array();
    }

    private function canGatherGem(){
        $rooms =  $this->getRooms();
        foreach($rooms as $room) {
            if ( in_array('gem', $this->getAbilityOfElement('room',$room, 'cangather') ) ) {
                return true;
            }
        }

        $specialists =  $this->getSpecialists();
        foreach($specialists as $specialist) {
            if ( in_array('gem', $this->getAbilityOfElement('specialist',$specialist, 'cangather') ) ) {
                return true;
            }
        }
        return false;
    }

    private function canGatherMagic(){
        $rooms =  $this->getRooms();
        foreach($rooms as $room) {
            if ( in_array('magic', $this->getAbilityOfElement('room',$room, 'cangather') ) ) {
                return true;
            }
        }

        $specialists =  $this->getSpecialists();
        foreach($specialists as $specialist) {
            if ( in_array('magic', $this->getAbilityOfElement('specialist',$specialist, 'cangather') ) ) {
                return true;
            }
        }
        return false;
    }

    public function getGatherBonus() {
        $specialists =  $this->getSpecialists();
        $bonus = array();
        foreach($specialists as $specialist) {
            $ability = $this->getAbilityOfElement('specialist',$specialist, 'gatherBonus');
            if ( !empty($ability)   ) {
                $bonus[] = $ability[0];
            }
        }
        
        return $bonus;
    }

    public function checkGather($resource_list, $ignore_conditions) {
        $res_count = count($resource_list);
        $laborer = $this->isSpecificSpecialistPresent('Laborer');
        // $bonuses_number = count($this->getGatherBonus());
        $max_res = $this->getMaxResources();

        // check if resource type is possible
        if (!$ignore_conditions) {
            if (end($resource_list) == 'gem' && !$this->canGatherGem() ) {
                return false;
            }

            if (end($resource_list) == 'magic' && !$this->canGatherMagic() ) {
                return false;
            }

            // check if combination of res is possible
            if ($res_count > 2) {           // check for specialists!!!
                if (!$laborer ) {
                    for($i=1;$i<3;$i++) {
                        if($resource_list[$i] != $resource_list[0]) {
                            return false;
                        }
                    }
                }
                if ($res_count > 3) {
                    return false;
                }

                // if($res_count+$bonuses_number >  $max_res ){
                if($res_count >  $max_res ){
                    return false;
                }
            }
        }

        // check if res needs to be replaced
        $replace = 0;
        // if ( $max_res < ($this->getResourceCount('all') + $res_count + $bonuses_number) ) {
        if ( $max_res < ($this->getResourceCount('all') + $res_count ) ) {
            $replace = ($this->getResourceCount('all')+$res_count) - $this->getMaxResources();
        }

        // indicates if max possible is reached
        $maxGather = true;
        if ($res_count == 1) {
            $maxGather = false;
        }

        if ($res_count == 2 && ( ($resource_list[0] == $resource_list[1]) || $laborer )  ) {
            $maxGather = false;
        }

        return array('triggerReplace' => $replace, 'maxGather' => $maxGather );
    }

    public function getPositionForResource() {
        $resources = $this->getResources();
        $locations = array_column($resources, 'token_location_arg');
        for($i=0;$i<count($locations);$i++) {
            $locations[$i] = intval($locations[$i]);
        }
        // $allPossibleLocations = range(0, $this->getResourceCount('all'));
        $allPossibleLocations = range(0, $this->getMaxResources()-1);
        $freePositions = array_values(array_diff($allPossibleLocations,$locations));

        return $freePositions;
    }

    public function getPossiblePositionsForRooms() {
        $free_tiles = array();
        $occupied_tiles = array();
        $rooms = $this->getRooms();
        $playable_tiles = array();
        $playable_doubletiles = array();
        $result = array();

        $builder_present = $this->isSpecificSpecialistPresent('Builder');

        foreach($rooms as $room) {
            $xy =  explode("_", $room['room_location_arg']);
            $occupied_tiles[] = array( (int)$xy[0], (int)$xy[1] );

            if($this->game->rooms[$room['room_type']]['doubleroom']) {
                $occupied_tiles[] = array( (int)$xy[0]+1, (int)$xy[1] );
            }
        }

        for($i=0;$i<4;$i++) {
            for($j=0;$j<3;$j++) { 
                if ( !in_array(array($i,$j),$occupied_tiles) ) {
                    $free_tiles[] = array($i,$j);
                }
            }
        }

    

        foreach($free_tiles as $tile) {
            $playable = false;
            $playable_double = false;
            $delta = array([-1,0], [1,0],[0,-1], [0,1]);
            $delta_double = array([-1,0], [2,0],[0,-1], [0,1], [1,-1], [1,1]);

            for ($i=0;$i<4;$i++) {
                if ($builder_present) {
                    $playable = true;
                } else {
                    if ( $tile[0]+$delta[$i][0] >-1 && $tile[0]+$delta[$i][0] < 4 && $tile[1]+$delta[$i][1] >-1 && $tile[1]+$delta[$i][1] < 3) {
                        if( in_array(array($tile[0]+$delta[$i][0],$tile[1]+$delta[$i][1]),$occupied_tiles) ) {
                            $playable = true;
                        }
                    } 
                }
            }

            if (in_array(array($tile[0]+1,$tile[1]),$free_tiles) ) {
                if ($builder_present) {
                    $playable_double = true;
                } else {
                    for ($i=0;$i<6;$i++) {
                        if ( $tile[0]+$delta_double[$i][0] >-1 && $tile[0]+$delta_double[$i][0] < 4 && $tile[1]+$delta_double[$i][1] >-1 && $tile[1]+$delta_double[$i][1] < 3) {
                            if( in_array(array($tile[0]+$delta_double[$i][0],$tile[1]+$delta_double[$i][1]),$occupied_tiles) ) {
                                $playable_double = true;
                            }
                        } 
                    }
                }
            }

            if ($playable) {
                $playable_tiles[] = array($tile[0], $tile[1]);
            }
            if ($playable_double) {
                $playable_doubletiles[] = array($tile[0], $tile[1]);
            }
        }

        $result['singletiles'] = $playable_tiles;
        $result['doubletiles'] = $playable_doubletiles;

        return $result;
    }

    public function getRoomPlacementBonus($room_id, $destination) {                                              // ???????? 
        $room_t = $this->getItemTypeFromId('room', $room_id);
        $mat = $this->getPlayerMat();

        if (in_array($destination, array_keys($this->game->playermats[$mat])) ) {
            if ($this->isSpecificSpecialistPresent('Builder')) {
                return array($this->game->playermats[$mat][$destination], $this->game->playermats[$mat][$destination]);
            } else {
                return array($this->game->playermats[$mat][$destination]);
            }
        }
        if ($this->game->rooms[$room_t]['doubleroom'] ) {
            $destination2 = (intval(substr($destination,0,1))+1).substr($destination, 1,2);
            if (in_array($destination2, array_keys($this->game->playermats[$mat])) ) {
                if ($this->isSpecificSpecialistPresent('Builder')) {
                    return array($this->game->playermats[$mat][$destination2], $this->game->playermats[$mat][$destination2]);
                } else {
                    return array($this->game->playermats[$mat][$destination2]);                              // undef index ????????
                }
            }
        }

        return false;
    }

    public function getPossiblePositionsForSpecialist($aristocrat = null) {
        $all_tiles = array('0_-1','1_-1','2_-1','3_-1');
        $rooms = $this->getRooms();
        $specialists =  $this->getSpecialists();

        foreach($rooms as $room) {
            $ab = $this->getAbilityOfElement('room',$room, 'tile');
            if ( in_array('specialist', $ab ) ) {
                if ($ab[1] > 1) {
                    $all_tiles[] = $room['room_location_arg'].'0';
                    $all_tiles[] = $room['room_location_arg'].'1';
                } else {
                    $all_tiles[] = $room['room_location_arg'];
                }
            }
        }
        $free_tiles = $all_tiles ;
        $specialists =  $this->getSpecialists();
        foreach($specialists as $specialist) {
             $loc = $specialist['specialistandquest_location_arg'];
             $free_tiles = array_diff( $free_tiles, [$loc]  );
        }

        if ($aristocrat == 'aristocrat') {
            $room_only = array_diff( $free_tiles,  array('0_-1','1_-1','2_-1','3_-1') );
            if (count($room_only) < 2) {
                $free_tiles = array_diff( $free_tiles, $room_only);
            }
        }

        if ($aristocrat == 'baggage') {
            $free_tiles = array_diff( $free_tiles,  array('0_-1','1_-1','2_-1','3_-1') );
        }

        return array_values($free_tiles);
    }

    public function getPlayerGold(){
        $sql = "SELECT player_gold FROM player WHERE player_id = '$this->player_id' ";
        $result = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        return $result;
    }

    private function getItemTypeFromId($item_type, $item_id) {
        $db_name = ($item_type == 'room' || $item_type == 'treasure') ? $item_type : 'specialistandquest';
        $elem_name = $db_name.'_id';
        $result_name =  ($item_type == 'room' || $item_type == 'treasure') ? $db_name.'_type' : $db_name.'_type_arg' ;

        $sql = "SELECT $result_name FROM $db_name WHERE $elem_name = '$item_id' ";
        $result = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );

        return $result;
    }

    public function canBuildItem($type, $id) {
        $player_gold = $this->getPlayerGold();
        $type_id = $this->getItemTypeFromId($type, $id);

        $mat_inc_name = $type == 'room' ? 'rooms': $type;
        $value = $this->game->{$mat_inc_name}[$type_id]['value'];

        if ($type == 'specialist') {
            $sql = "SELECT specialistandquest_discount FROM specialistandquest WHERE specialistandquest_id = '$id' ";
            $discount = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        } else {$discount = 0; }

        if ( $this->isSpecificSpecialistPresent('Recruiter') &&  $type == 'specialist' ) {
            $discount += $discount*2;
        }

        if ($player_gold + $discount >= $value) {
            return true;
        } else {
            return false;
        }
    }

    public function canBuildAristocrat() {
        $free_tiles = $this->getPossiblePositionsForSpecialist();
        $start_tiles = array('0_-1','1_-1','2_-1','3_-1');

        if (count($free_tiles) < 2) {
            return false;
        } else {
            
            $free_tiles = array_diff($free_tiles, $start_tiles);

            if (count($free_tiles) > 0) {
                return true;
            } else {
                return false;
            }
        }

    }

    public function getCraftDiscount() {
        $result = array('Weapon' => array(), 'Armor' => array());
        $specialists =  $this->getSpecialists();

        foreach($specialists as $specialist) {
            if ( !empty($this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']) ) {
                if (  array_keys( $this->game->specialist[$specialist['specialistandquest_type_arg']]['ability'])[0] == 'craftbonus' ) {
                    $result[$this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftbonus'][0]][] = $this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftbonus'][1];
                }
            }
        }

       return $result;
    }

    public function canCraftItem($type, $item_position) {
        $item_cost = $this->game->quest[$type]['cost'][$item_position];  
        $discount = $this->getCraftDiscount();
        $reduce = array();

        if ( $discount[ $this->game->quest[$type]['items'][$item_position][1] ] != null ) {
            $reduce = $discount[ $this->game->quest[$type]['items'][$item_position][1] ];
        } 

        foreach($item_cost as $resource_type => $needed) {

            $res_count = in_array($resource_type, $reduce) ? $needed-1: $needed;
            if ($this->getResourceCount($resource_type) < $res_count) {
                return false;
            }
        }

        return true;
    }

    private function getHandSize() {
        $sql = "SELECT player_hand_size hs  FROM player WHERE player_id = '$this->player_id' ";
        $result = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );
        return $result;
    }

    public function getPositionForTreasureCards($card_number) {
        $positions = array();
        $need2sell = 0;

        $sql = "SELECT COUNT(treasure_id) num  FROM treasure WHERE treasure_location = '$this->player_id' AND treasure_location_arg < 20 ";
        $cards_in_hand = $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' );

        $hand_size = $this->getHandSize();

        if ($cards_in_hand+$card_number > $hand_size) {             // check if card selection is triggered
            $need2sell = $cards_in_hand+$card_number - $hand_size;
            for ($i=20;$i<20+$card_number;$i++) {
                $positions[] = $i;
            }
        } else {
            for ($i=$cards_in_hand;$i<$cards_in_hand+$card_number;$i++) {
                $positions[] = $i;
            }
        }

        return array('positions' => $positions, 'needToSell' => $need2sell);
    }

    public function getCraftSpecialistAtions($hero_type) {                  
        $specialists =  $this->getSpecialists();
        $result = array();
        switch ($hero_type) {
            case 1:
                $hero = 'warrior';
            break;
            case 2:
                $hero = 'rogue';
            break;
            case 3:
                $hero = 'mage';
            break;
            default:
                $hero = null;
            break;
        }

        foreach($specialists as $specialist) {
            if (  array_keys( $this->game->specialist[$specialist['specialistandquest_type_arg']]['ability'])[0] == 'craftaction' ) {
                if ( $this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftaction'][0] == 'goldfortreasure' ) {  //filter according to quest type
                    if ($this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftaction'][1] == $hero ) {
                        $result[] = $specialist['specialistandquest_type_arg'];
                    }
                } elseif ($this->game->specialist[$specialist['specialistandquest_type_arg']]['name'] == 'Thug') {
                    // check if thug is free????
                    $result[] = $specialist['specialistandquest_type_arg'];
                } else {
                    $result[] = $specialist['specialistandquest_type_arg'];
                }

            }
       }

       return $result;
    }

    public function getActionBonuses() {
        $result['gather'] = array();
        $result['craft'] = array();
        $result['expand'] = array();

        if ( $this->canGatherGem() ) {
            $result['gather']['gems'] = true;
        }
        if ( $this->canGatherMagic() ) {
            $result['gather']['magic'] = true;
        }
        if ( !empty($this->getGatherBonus() ) ) {
            $result['gather']['bonus'] = $this->getGatherBonus();
        }
        if ($this->isSpecificSpecialistPresent('Laborer') ) {
            $result['gather']['Laborer'] = true;
        }

        $result['craft']['discounts'] = $this->getCraftDiscount();

        $specialists =  $this->getSpecialists();
        foreach($specialists as $specialist) {
            if ( !empty($this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']) ) {
                if (  array_keys( $this->game->specialist[$specialist['specialistandquest_type_arg']]['ability'])[0] == 'craftaction' ) {
                    if ( $this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftaction'][0] == 'goldfortreasure' ) {  //filter according to quest type
                        if ($this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftaction'][1] == 'warrior' ) {
                            $result['craft']['warrior'] = true;
                        }
                        if ($this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftaction'][1] == 'rogue' ) {
                            $result['craft']['rogue'] = true;
                        }
                        if ($this->game->specialist[$specialist['specialistandquest_type_arg']]['ability']['craftaction'][1] == 'mage' ) {
                            $result['craft']['mage'] = true;
                        }
                    }
                }
            }
        }

        if ($this->isSpecificSpecialistPresent('Builder') ) {
            $result['expand']['Builder'] = true;
        }
        if ($this->isSpecificSpecialistPresent('Recruiter') ) {
            $result['expand']['Recruiter'] = true;
        }
    
        return $result;
    }

    public function getScrollsIds() {
        $all_treasure_cards = $this->getTreasureCards();
        $scrolls = array();

        foreach($all_treasure_cards as $card) {
            if (  $this->game->treasures[$card['treasure_type']]['cathegory'] == 'Scroll' ) {
                $scrolls[] = $card['treasure_id'];
            }
        }

        return $scrolls;
    }

    public function canPlayTreasureEnd() {
        $cards = $this->getTreasureCards();

        foreach($cards as $card) {
            if (  $this->game->treasures[$card['treasure_type']]['effect'] != null ) {
                return true;
            }
        }

        return false;
    }

    public function calculateScore($includeLibrary = false) {
        $result['specialists'] = 0;
        $result['rooms']  = 0;
        $specialists = $this->getSpecialists(); 
        foreach($specialists as $specialist) {
            $result['specialists'] += $this->game->specialist[$specialist['specialistandquest_type_arg']]['point_value'] ;
        }
        
        $rooms = $this->getRooms(); 
        foreach($rooms as $room) {
            if ( $this->game->rooms[$room['room_type']]['point_value'] != null ) {
                $result['rooms']  += $this->game->rooms[$room['room_type']]['point_value'] ;
            }


            if ( $this->game->rooms[$room['room_type']]['end_points'] != null ) {
                if ( key($this->game->rooms[$room['room_type']]['end_points']) != 'scrollCount' || $includeLibrary )  // do not reveal numberof scrolls in mid game
                $result['rooms']  += $this->calculatePointsWithCondition( $room, 'room', $this->game->rooms[$room['room_type']]['end_points']  );
            }
        }

        $result['quests'] = count($this->getQuests()); 

        return $result;
    }

    private function calculatePointsWithCondition($element, $elementType, $condition) {
        $result = 0;
        switch (key($condition)) {
            case 'occupied':        // room must be occupied by specialists
                $location = $element['room_location_arg'];
                $sql = "SELECT COUNT(specialistandquest_id) FROM specialistandquest WHERE specialistandquest_location = '$this->player_id' AND SUBSTRING(specialistandquest_location_arg, 1,3) = '$location' ";
                if ( $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' ) > 1 &&  $this->game->rooms[$element['room_type']]['doubleroom']  ) {
                    $result = $condition['occupied'];
                }
                if ( $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' ) > 0 &&  !$this->game->rooms[$element['room_type']]['doubleroom']  ) {
                    $result = $condition['occupied'];
                }
            break;

            case 'goldvalue':
                $result = $this->getPlayerGold();
            break;

            case 'nextToStatue':
                $location = $element['room_location_arg'];
                $locationX = (int)explode("_", $location)[0]; $locationY = (int)explode("_", $location)[1];
                $shift = array(array(-1, 0),array(0,-1), array(0,1), array(1,0) );
                for ($i=0;$i<4;$i++) {
                    if ( $locationX+$shift[$i][0] >=0 && $locationX+$shift[$i][0] < 4 && $locationY+$shift[$i][1] >=0 && $locationY+$shift[$i][1] < 3 ) {
                        $testlocation = ($locationX+$shift[$i][0])."_".($locationY+$shift[$i][1]);
                        $sql = "SELECT COUNT(room_id) FROM room WHERE room_type = '24' AND room_location_arg = '$testlocation' ";
                        if ( $this->game::getDbOutside( $sql, 'getUniqueValueFromDB' ) > 0) {
                            $result = $condition['nextToStatue'];
                        }
                    }
                }
            break;

            case 'specialistCount':
                $result = count($this->getSpecialists())*$condition['specialistCount'];
            break;

            case 'setOfHeroes':
                $hero1 = $condition['setOfHeroes'][0];
                $hero2 = $condition['setOfHeroes'][1];
                $heroCount1 = 0;
                $heroCount2 = 0;
                $allQuests = $this->getQuests();

                foreach($allQuests as $quest) {
                    if ( $this->game->quest[$quest['specialistandquest_type_arg']]["hero"][0] == $hero1 ) { $heroCount1++;}
                    if ( $this->game->quest[$quest['specialistandquest_type_arg']]["hero"][0] == $hero2 ) { $heroCount2++;}
                }
                $result = min($heroCount1, $heroCount2 )*$condition['setOfHeroes'][2] ;
            break;

            case 'heroCount':
                $hero = $condition['heroCount'][0];
                $heroCount = 0;
                $allQuests = $this->getQuests();

                foreach($allQuests as $quest) {
                    if ( $this->game->quest[$quest['specialistandquest_type_arg']]["hero"][0] == $hero ) { $heroCount++;}
                }
                $result = $heroCount*$condition['heroCount'][1] ;
            break;

            case 'setOfItems':
                $item1 = $condition['setOfItems'][0];
                $item2 = $condition['setOfItems'][1];
                $itemCount1 = 0;
                $itemCount2 = 0;

                $allQuests = $this->getQuests();

                foreach($allQuests as $quest) {
                    $items =  $this->game->quest[$quest['specialistandquest_type_arg']]["items"];
                    foreach($items as $item) {
                        if ($item[1] == $item1 ) { $itemCount1++;}
                        if ($item[1] == $item2 ) { $itemCount2++;}
                    }
                }

                $result = min($itemCount1, $itemCount2 )*$condition['setOfItems'][2] ;
            break;

            case 'roomCount':
                $result = (count($this->getRooms())-1)*$condition['roomCount']; // deduct guild room
            break;

            case 'resourcesType':
                $res = array_values(array_column($this->getResources(), 'token_type_arg')) ;
                $result = count( array_values(array_unique($res)) )*$condition['resourcesType']; 
            break;

            case 'itemCount':
                $itemName = $condition['itemCount'][0];
                $itemCount = 0;

                $allQuests = $this->getQuests();

                foreach($allQuests as $quest) {
                    $items =  $this->game->quest[$quest['specialistandquest_type_arg']]["items"];
                    foreach($items as $item) {
                        if ($item[1] == $itemName ) { $itemCount++;}
                    }
                }

                $result = $itemCount*$condition['itemCount'][1]; 
            break;

            case 'scrollCount':
                $treasures = $this->getTreasureCards();
                $scrollCount = 0;
                foreach($treasures as $treasure) {
                    $cat =  $this->game->treasures[$treasure['treasure_type']]["cathegory"];
                    if ($cat != null) {
                        if ($cat == 'Scroll') {
                            $scrollCount++;
                        }
                    }
                }
                $result = $scrollCount*3;
            break;


        }


        return $result;
    }

    public function finalScoring($offering) {
        $result['score']['charms'] = 0;
        $result['score']['relics'] = 0;
        $result['score']['quests'] = 0;
        $result['score']['specialists'] = 0;
        $result['score']['rooms'] = 0;
        $result['score']['offering'] = 0;
        $result['charmNumber'] = 0;
        $result['gold'] = (int)$this->getPlayerGold();

        $scoreDuringGame = $this->calculateScore(true);
        $result['score']['specialists'] = $scoreDuringGame['specialists'];
        $result['score']['rooms'] = $scoreDuringGame['rooms'];
        $result['score']['quests'] =  $scoreDuringGame['quests']; 
        $treasures = $this->getTreasureCards();
        $relics = array();

        foreach($treasures as $treasure) {
            $cat =  $this->game->treasures[$treasure['treasure_type']]["cathegory"];
            if ($cat != null) {
                if ($cat == 'Charm') {
                    $result['score']['charms'] += $this->game->treasures[$treasure['treasure_type']]['points'];
                    $result['charmNumber']++;
                }

                if ($cat == 'Relic') {
                    $relics[] = $treasure['treasure_type']; //$this->game->treasures[$treasure['treasure_type']]["name"];
                }
            }
        }
        $relicPoints = array(0,2,5,9,13,17,22);
        $result['score']['relics'] = $relicPoints[count( array_values(array_unique($relics)) )];

        if ($offering) {
            $res = $this->getResourceCount('all');
            $points = 2;    // base 2 pts
            if ( !$this->isSpecificRoomTypePresent(null, 9) ) {  // exclude vault of riches
                $points += ($result['gold'] - ( $result['gold'] % 3) ) / 3;     // 1pt per 3 gold
            }
            $points += ($res - ( $res % 2) ) / 2;                           // 1pt per 2 resource
            $result['score']['offering'] = $points;
        }

        return $result;
    }


}