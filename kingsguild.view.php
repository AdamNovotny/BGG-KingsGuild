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
 * kingsguild.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in kingsguild_kingsguild.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_kingsguild_kingsguild extends game_view
  {
    function getGameName() {
        return "kingsguild";
    }   
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/
        global $g_user;
        $current_player_id = $g_user->get_id();
        $spectator =  $this->game->isSpectator($current_player_id);
        if ($spectator) {
            $this->tpl['SPECTATOR'] = 'spectatoritem';
        } else {
            $this->tpl['SPECTATOR'] = '';
        }

        if (!$spectator) {
            $this->tpl['THISID'] = $current_player_id;
            $this->tpl['THISCOLOR'] = $players[$current_player_id]['player_color'];
            $this->tpl['THISNAME'] = $players[$current_player_id]['player_name'];
            $this->tpl['THISMAT'] = $this->game->getPlayerMat($current_player_id);
            $this->tpl['THISGUILD'] = $this->game->getPlayerGuild($current_player_id, true);
            $this->tpl['HAND'] = self::_("My treasure cards");
            $this->tpl['SHOW_COMPLETED_QUESTS'] = self::_("Show my completed quests");
        }
        // $this->tpl['MASTERROOMS'] = self::_("<< SHOW MASTER ROOMS");
        $this->tpl['MASTERROOMS'] = "<< SHOW MASTER ROOMS"; // text is set using onLoad function in js

        $X_OFFSET = 50;
        $Y_OFFSET = 50;


        $this->page->begin_block( "kingsguild_kingsguild", "mainBoardMasterTiles" );
        for($i=0;$i<(3+$players_nbr);$i++) {
            $this->page->insert_block( "mainBoardMasterTiles", array( 
                "TYPE" => 'masterroom',
                "POSITION" => $i,
                "LEFT" => 110*$i+($i+1)*(1100-(3+$players_nbr)*110)/((3+$players_nbr)+1),
                "TOP" => (134-120)/2,
                "WIDTH" => 120, //$this->game->tiles[1]['size'][0],
                "HEIGHT" =>  120, //$this->game->tiles[1]['size'][1],
            ) );
        }

        $this->page->begin_block( "kingsguild_kingsguild", "mainBoardTiles" );
        foreach( $this->game->tiles as $group ) {

            for($i=0;$i<(count($group["x_spaces"])+1);$i++) {

                    $double = ($group['name'] == 'room' && $i==5) ? 1.9:1;
                    $this->page->insert_block( "mainBoardTiles", array( 
                        "TYPE" => $group['name'],
                        "POSITION" => $i,
                        "LEFT" => $group['start'][0]+ array_sum(array_slice($group['x_spaces'],0,$i)) -$X_OFFSET + $i*$group['size'][0],
                        "TOP" => $group['start'][1] - $Y_OFFSET,
                        "WIDTH" => $group['size'][0]*$double,
                        "HEIGHT" => $group['size'][1],
                    ) );
                
            }

            if ($group["y_spaces"] != null) {
                for($j=$i;$j<(count($group["y_spaces"])+$i);$j++) {
                    $this->page->insert_block( "mainBoardTiles", array( 
                        "TYPE" => $group['name'],
                        "POSITION" => $j,
                        "LEFT" => $group['start'][0]+ array_sum(array_slice($group['x_spaces'],0,$j-$i)) -$X_OFFSET+ ($j-$i)*$group['size'][0],
                        "TOP" => $group['start'][1]+$group['y_spaces'][$j-(count($group["x_spaces"])+1)]- $Y_OFFSET + $group['size'][1] ,
                        "WIDTH" => $group['size'][0],
                        "HEIGHT" => $group['size'][1],
                    ) );
                }
            }
        }

        if ($players_nbr > 4 || $players_nbr == 1) {     // add 6th quest card slot in case of 5/6 players and solo mode
            $this->page->insert_block( "mainBoardTiles", array( 
                "TYPE" => 'quest',
                "POSITION" => 6,
                "LEFT" => $this->game->tiles[3]['start'][0] -$X_OFFSET - $this->game->tiles[3]['size'][0] - $this->game->tiles[3]['x_spaces'][0],
                "TOP" => $this->game->tiles[3]['start'][1]+ $this->game->tiles[3]['y_spaces'][0]  - $Y_OFFSET + $this->game->tiles[3]['size'][1] ,
                "WIDTH" => $this->game->tiles[3]['size'][0],
                "HEIGHT" => $this->game->tiles[3]['size'][1],
            ) );
        }


        if (!$spectator) {
            $this->page->begin_block( "kingsguild_kingsguild", "playerBoardTiles" );

            $group = $this->game->playertiles[1];
            for($i=0;$i<4;$i++) {
                $this->page->insert_block( "playerBoardTiles", array( 
                    // "TYPE" => 'player_specialist',
                    "TYPE" => 'specialist',
                    "POSITIONX" => $i,
                    "POSITIONY" => -1,
                    "TILETYPE" => 'specialist',
                    "ID" => $current_player_id,
                    "LEFT" => $group['start'][0]+ $i*$group['x_spaces'][0] -$X_OFFSET + $i*$group['size'][0],
                    "TOP" => $group['start'][1] - $Y_OFFSET,
                    "WIDTH" => $group['size'][0],
                    "HEIGHT" => $group['size'][1],
                ) );
            }

            $group = $this->game->playertiles[2];
            for($i=0;$i<4;$i++) {
                for($j=0;$j<3;$j++) { 
                        $this->page->insert_block( "playerBoardTiles", array( 
                            // "TYPE" => 'player_'.$group['name'],
                            "TYPE" => $group['name'],
                            "POSITIONX" => $i,
                            "POSITIONY" => $j,
                            "TILETYPE" => 'room',
                            "ID" => $current_player_id,
                            "LEFT" => $group['start'][0]+ $i*$group['x_spaces'][0] -$X_OFFSET+ $i*$group['size'][0],
                            "TOP" => $group['start'][1]+ $j*$group['y_spaces'][0][0]- $Y_OFFSET + $j*$group['size'][1] ,
                            "WIDTH" => $group['size'][0],
                            "HEIGHT" => $group['size'][1],
                        ) );
                }
            }

            $this->page->begin_block( "kingsguild_kingsguild", "playerBoardDoubleTiles" );
            $group = $this->game->playertiles[2];
            for($i=0;$i<3;$i++) {
                for($j=0;$j<3;$j++) {
                    $this->page->insert_block( "playerBoardDoubleTiles", array( 
                        // "TYPE" => 'player_'.$group['name'],
                        "TYPE" => $group['name'],
                        "POSITION" => $i,
                        "POSITION2" => $j,
                        "TILETYPE" => 'room',
                        "ID" => $current_player_id,
                        "LEFT" => $group['start'][0]+ array_sum(array_slice($group['x_spaces'],0,$i)) -$X_OFFSET+ $i*$group['size'][0] + $group['x_spaces'][$i]/2,
                        "TOP" => $group['start'][1]+$j*$group["y_spaces"][0][0]- $Y_OFFSET + $j*$group['size'][1] ,
                        "WIDTH" => $group['size'][0]*2,
                        "HEIGHT" => $group['size'][1],
                    ) );
                }
            }
        }

        $this->page->begin_block( "kingsguild_kingsguild", "playerBoardDoubleTiles2" );
        $this->page->begin_block( "kingsguild_kingsguild", "playerBoardTiles2" );
        $this->page->begin_block( "kingsguild_kingsguild", "playerBoards" );
        $counter = 0;

        foreach( $players as $player_id => $player  )
        {
            if ($player_id != $current_player_id) {
                $counter++;
                $this->page->reset_subblocks( 'playerBoardTiles2' ); 
                
                $X_OFFSET = 50;
                $Y_OFFSET = 50;

                $group = $this->game->playertiles[1];
                for($i=0;$i<4;$i++) {
                    $this->page->insert_block( "playerBoardTiles2", array( 
                        // "TYPE" => 'player_specialist',
                        "TYPE" => 'specialist',
                        "POSITIONX" => $i,
                        "POSITIONY" => -1,
                        "TILETYPE" => 'specialist',
                        "ID" => $player_id,
                        "LEFT" => $group['start'][0]+ $i*$group['x_spaces'][0] -$X_OFFSET + $i*$group['size'][0],
                        "TOP" => $group['start'][1] - $Y_OFFSET,
                        "WIDTH" => $group['size'][0],
                        "HEIGHT" => $group['size'][1],
                    ) );
                }
        
                $group = $this->game->playertiles[2];
                for($i=0;$i<4;$i++) {
                    for($j=0;$j<3;$j++) { 
                            $this->page->insert_block( "playerBoardTiles2", array( 
                                // "TYPE" => 'player_'.$group['name'],
                                "TYPE" => $group['name'],
                                "POSITIONX" => $i,
                                "POSITIONY" => $j,
                                "TILETYPE" => 'room',
                                "ID" => $player_id,
                                "LEFT" => $group['start'][0]+ $i*$group['x_spaces'][0] -$X_OFFSET+ $i*$group['size'][0],
                                "TOP" => $group['start'][1]+ $j*$group['y_spaces'][0][0]- $Y_OFFSET + $j*$group['size'][1] ,
                                "WIDTH" => $group['size'][0],
                                "HEIGHT" => $group['size'][1],
                            ) );
                    }
                }

                $this->page->reset_subblocks( 'playerBoardDoubleTiles2' );
                $group = $this->game->playertiles[2];
                for($i=0;$i<3;$i++) {
                    for($j=0;$j<3;$j++) {
                        $this->page->insert_block( "playerBoardDoubleTiles2", array( 
                            // "TYPE" => 'player_'.$group['name'],
                            "TYPE" => $group['name'],
                            "POSITION" => $i,
                            "POSITION2" => $j,
                            "TILETYPE" => 'room',
                            "ID" => $player_id,
                            "LEFT" => $group['start'][0]+ array_sum(array_slice($group['x_spaces'],0,$i)) -$X_OFFSET+ $i*$group['size'][0] + $group['x_spaces'][$j]/2,
                            "TOP" => $group['start'][1]+$j*$group["y_spaces"][0][0]- $Y_OFFSET + $j*$group['size'][1] ,
                            "WIDTH" => $group['size'][0]*2,
                            "HEIGHT" => $group['size'][1],
                        ) );
                    }
                }

                if ($counter == ($players_nbr-1) && $players_nbr % 2 == 0 ) {
                    $margin = (1810 - 710)*(1/2)/(1810/100);
                } else {
                    $margin = (1810 - 2*710)*(1/3)/(1810/100);
                }

                $this->page->insert_block( "playerBoards", array( 
                                                        "OTHERID" => $player_id,
                                                        "OTHERMAT" => $this->game->getPlayerMat($player_id),
                                                        "OTHERGUILD" => $this->game->getPlayerGuild($player_id, true),
                                                        "OTHERCOLOR" => $player['player_color'],
                                                        "OTHERNAME" => $player['player_name'],
                                                        "MARGIN" => $margin,
                                                        ) );
            }
            
        }



        /*********** Do not change anything below this line  ************/
  	}
  }
  

