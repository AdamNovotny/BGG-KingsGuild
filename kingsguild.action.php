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
 * kingsguild.action.php
 *
 * kingsguild main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/kingsguild/kingsguild/myAction.html", ...)
 *
 */
  
  
  class action_kingsguild extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "kingsguild_kingsguild";
            self::trace( "Complete reinitialization of board game" );
      }
    } 
    
    public function selectAction() {
      self::setAjaxMode();     

      $arg1 = self::getArg( "selectedAction", AT_alphanum, true  );
      $this->game->selectAction( $arg1 );

      self::ajaxResponse( );
    }

    public function cancelAction() {
      self::setAjaxMode();     
      $this->game->cancelAction( );
      self::ajaxResponse( );
    }

    public function chooseResource() {
      self::setAjaxMode();     

      $arg1 = self::getArg( "resources", AT_alphanum, true  );

      if( substr( $arg1, -1 ) == '_' ) {
          $arg1 = substr( $arg1, 0, -1 );
      }
      if( $arg1 == '' ) {
          $arg = array();
      } else {
          $arg = explode( '_', $arg1 );
      }

      $this->game->chooseResource( $arg );

      self::ajaxResponse( );
    }

    public function takeResourcesAndReplace() {
      self::setAjaxMode();     

      $take_res = self::getArg( "take_res", AT_alphanum, true  );
      $return_res = self::getArg( "return_res", AT_alphanum, true  );

      if( substr( $take_res, -1 ) == '_' ) {
          $take_res = substr( $take_res, 0, -1 );
      }
      if( $take_res == '' ) {
          $arg1 = array();
      } else {
          $arg1 = explode( '_', $take_res );
      }

      if( substr( $return_res, -1 ) == '_' ) {
        $return_res = substr( $return_res, 0, -1 );
      }
      if( $return_res == '' ) {
          $arg2 = array();
      } else {
          $arg2 = explode( '_', $return_res );
      }

      $this->game->takeResourcesAndReplace( $arg1, $arg2 );

      self::ajaxResponse( );
    }

    public function selectExpandItem() {
      self::setAjaxMode();     
      $arg1 = self::getArg( "type", AT_alphanum, true  );
      $arg2 = self::getArg( "id", AT_posint, true  );
      $this->game->selectExpandItem( $arg1, $arg2 );
      self::ajaxResponse( );
    }

    public function drawTreasureCard() {
      self::setAjaxMode();     

      $arg1 = self::getArg( "card", AT_alphanum, true  );
      $this->game->drawTreasureCard( $arg1 );

      self::ajaxResponse( );
    }

    public function placeRoom() {
      self::setAjaxMode();     
      $room_id = self::getArg( "room_id", AT_posint, true  );
      $destination = self::getArg( "destination", AT_alphanum, true  );

      $this->game->placeRoom( $room_id, $destination );

      self::ajaxResponse( );
    }

    public function placeSpecialist() {
      self::setAjaxMode();     
      $specialist_id = self::getArg( "specialist_id", AT_posint, true  );
      $destination = self::getArg( "destination", AT_alphanum, true, null, array(), true  );

      $this->game->placeSpecialist( $specialist_id, str_replace("z", "-",$destination ) );

      self::ajaxResponse( );
    }

    public function passAction() {
      self::setAjaxMode();     
      $this->game->passAction();
      self::ajaxResponse( );
    }

    public function oracleAction() {
      self::setAjaxMode();     
      $this->game->oracleAction();
      self::ajaxResponse( );
    }

    public function stealResource() {
      self::setAjaxMode();   
      $resource_id = self::getArg( "stealId", AT_alphanum, true  );
      $player_id = self::getArg( "player", AT_alphanum, true );
      $return_id = self::getArg( "returnId", AT_alphanum, false );
      
      $this->game->stealResource($resource_id, $player_id, $return_id);
      self::ajaxResponse( );
    }

    public function confirmCraft() {
      self::setAjaxMode();     
        $quest_id = self::getArg( "quest_id", AT_posint, true  );
        $item_id = self::getArg( "item_id", AT_posint, true  );
        $second = self::getArg( "second_item", AT_bool, true  );

        $this->game->craftItem( $quest_id, $item_id, $second );
      self::ajaxResponse( );
    }

    public function selectCraftItem() {
      self::setAjaxMode();     
        $quest_id = self::getArg( "quest_id", AT_posint, true  );
        $item_id = self::getArg( "item_id", AT_posint, true  );
        $this->game->selectCraftItem( $quest_id, $item_id );
      self::ajaxResponse( );
    }

    public function selectTreasureCards() {
      self::setAjaxMode();     
        $treasure_ids = self::getArg( "treasure_ids", AT_alphanum, true  );

        if( substr( $treasure_ids, -1 ) == '_' ) {
          $treasure_ids = substr( $treasure_ids, 0, -1 );
        }
        
        if( $treasure_ids == '')
          $treasure_ids = array();
        else
          $treasure_ids = explode( '_', $treasure_ids );

        $this->game->selectTreasureCards( $treasure_ids );
      self::ajaxResponse( );
    }

    public function playTreasureCard() {
      self::setAjaxMode();     
        $treasure_id = self::getArg( "treasure_id", AT_posint, true  );
        $sell = self::getArg( "sell", AT_bool, true  );

        $this->game->playTreasureCard( $treasure_id, $sell );
      self::ajaxResponse( );
    }

    public function selectQuest() {
      self::setAjaxMode();     
        $quest_id = self::getArg( "quest_id", AT_posint, true  );

        $this->game->selectQuest( $quest_id );
      self::ajaxResponse( );
    }

    public function makeBid() {
      self::setAjaxMode();     
        $bid = self::getArg( "bid", AT_posint, true  );

        $this->game->makeBid( $bid );
      self::ajaxResponse( );
    }

    public function makeOffering() {
      self::setAjaxMode();     
        $this->game->makeOffering( );
      self::ajaxResponse( );
    }

    public function endTurn() {
      self::setAjaxMode();     
        $this->game->endTurn( );
      self::ajaxResponse( );
    }

}
  

