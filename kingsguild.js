/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * kingsguild implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * kingsguild.js
 *
 * kingsguild user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    g_gamethemeurl + "modules/kgClientStates.js",

],
function (dojo, declare) {
    return declare("bgagame.kingsguild", ebg.core.gamegui, {
        constructor: function(){
            console.log('kingsguild constructor');
              

            this.sizeRatio = 1;         // actual multiplayer to all dimensions
            this.mainBoardSize = 1100;  // size of main board  for calculations
            this.mainBoardRatio = 3/5;  // main board to player board ratio
            this.roomSizeCoef = 126/364; // 126/414;   // rooms size ratio 
            this.roomSizeCoefDual = 241/736; // 126/414;   
            this.roomSizeCoefEnlarged = 165/364; //165/414;
            this.roomSizeCoefEnlargedDual = 316/736; //316/786;
            this.specialistSizeCoef = 100/307; //100/337;
            this.specialistSizeCoefEnlarged = 138/307; //138/337;
            this.questSizeCoef = 126/308.3; // 63/164;
            this.treasureSizeCoef = 128/400; // 32/105; //90/276; //32/105; 128/400
            this.treasureSizeCoefEnlarged = 256/400; //64/105; //180/276; //64/105;

            this.cardsMargin = 10;
            this.cardsBaseWidth = 110;

            this.handlers = [];

            this.roomPosition = 0;
            this.specialistPosition = '0';
            this.selectedResourcesId = [];
            // this.actualGatherBonus = [];
            this.selectedCraftItem = [];
            this.selectedTreasure = [];
            this.selectedTreasureForPlay = 0;
            this.selectedQuest = 0;
            this.playerHasAuctioneer = false;
            this.fortunePotionActive = false;
            this.luckyPotionActive = false;
            this.endPhaseActive = false;
            this.dealerPassActive = false;
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                         
                var player_board_div = $('player_board_'+player_id);
                dojo.place( this.format_block('jstpl_player_panel', { player: player_id } ), player_board_div );

                this.addTooltip(player_id+'_gold', _('gold amount'), '');
                this.addTooltip(player_id+'_cardsblue', _('number of blue treasure cards in hand'), '');
                this.addTooltip(player_id+'_cardsred', _('number of red treasure cards in hand'), '');
                this.addTooltip(player_id+'_cardsyellow', _('number of yellow treasure cards in hand'), '');
                this.addTooltip(player_id+'_maxhand', _('maximum hand size'), '');
                this.addTooltip(player_id+'_sigilplace', _('free sigils'), '');

                this.addTooltip(player_id+'_questCounter_mage', _('completed mage quests'), '');
                this.addTooltip(player_id+'_questCounter_warrior', _('completed warrior quests'), '');
                this.addTooltip(player_id+'_questCounter_rogue', _('completed rogue quests'), '');
                this.addTooltip(player_id+'_questCounter_weapon', _('weapon number'), '');
                this.addTooltip(player_id+'_questCounter_armor', _('armor number'), '');
                this.addTooltip(player_id+'_counter_mage', _('completed mage quests'), '');
                this.addTooltip(player_id+'_counter_warrior', _('completed warrior quests'), '');
                this.addTooltip(player_id+'_counter_rogue', _('completed rogue quests'), '');
                this.addTooltip(player_id+'_counter_weapon', _('weapon number'), '');
                this.addTooltip(player_id+'_counter_armor', _('armor number'), '');

                this.addTooltip(player_id+'_resCount', _('number of available resources'), '');

                $(player_id+'_gold').innerText = player.gold;
                $(player_id+'_maxhand').innerText = player.handsize;
            }

            if (!this.isSpectator) {
                for(var i=0;i<gamedatas.players[this.player_id].handsize;i++) {
                    if(dojo.hasClass('ebd-body', 'mobile_version')) {
                        if (gamedatas.players[this.player_id].handsize < 8) {
                            this.addCardTileOnBoard(i, 180*0.7, 255.4*0.7);
                        } else if (gamedatas.players[this.player_id].handsize < 10) {
                            this.addCardTileOnBoard(i, 180*0.6, 255.4*0.6);
                        } else {
                            this.addCardTileOnBoard(i, 180*0.4, 255.4*0.4);
                        }
                    } else {
                        if (gamedatas.players[this.player_id].handsize < 8) {
                            this.addCardTileOnBoard(i, 180, 255.4);
                        } else if (gamedatas.players[this.player_id].handsize < 11) {
                            this.addCardTileOnBoard(i, 180*0.9, 255.4*0.9);
                        } else {
                            this.addCardTileOnBoard(i, 180*0.8, 255.4*0.8);
                        }
                    }
                }
                dojo.style('player_cards', 'width', dojo.style('player_cards', 'width')+dojo.style('tile_card_0', 'width')+'px'  );     // !!!!!!!!!!!!!!!
            }


            // Add rooms
            var doublerooms_side_up = [];
            var doublerooms_side_down = [];
            var singlerooms_side_up = [];
            var singlerooms_side_down = [];
            for(var room_id in gamedatas.rooms ) {
                var room = gamedatas.rooms[room_id];

                if (room.two_sided == false) {
                    this.addRoomOnBoard(room.name.replace(/ /g, '').replace(/'/g, ''), room_id , room.location, room.location_arg, room.cathegory == 'master' ? 'masterroom_' : 'room_', room.doubleroom ? 'doubleroom': 'singleroom', null,null, null, room.ability);
                } else {
                    if (room.doubleroom) {
                        room.side == 1 ? doublerooms_side_up.push(room): doublerooms_side_down.push(room);
                    } else {
                        room.side == 1 ? singlerooms_side_up.push(room):singlerooms_side_down.push(room);
                    }
                }
            }

            // sort side down rooms according 1.type 2.id
            doublerooms_side_down = this.sortByKey(doublerooms_side_down, 'type', true);
            singlerooms_side_down = this.sortByKey(singlerooms_side_down, 'type', true);

            for (var i=0;i<doublerooms_side_up.length;i++) {
                var up = doublerooms_side_up[i];
                var down = doublerooms_side_down[i];
                this.addRoomOnBoard(up.name.replace(/ /g, '').replace(/'/g, ''), up.id,up.location, up.location_arg, 'room_',up.doubleroom ? 'doubleroom': 'singleroom', true, down.name.replace(/ /g, ''), down.id, up.ability);
            }
            for (var i=0;i<singlerooms_side_up.length;i++) {
                var up = singlerooms_side_up[i];
                var down = singlerooms_side_down[i];
                this.addRoomOnBoard(up.name.replace(/ /g, '').replace(/'/g, ''), up.id,up.location, up.location_arg, 'room_',up.doubleroom ? 'doubleroom': 'singleroom', true, down.name.replace(/ /g, ''), down.id, up.ability);
            }

            // Add specialists
            var specialistkeys = Object.keys(gamedatas.specialist);
            // for(var specialist_id = Object.keys(gamedatas.specialist).length; specialist_id>0;specialist_id--) {
            for(var specialist_id = specialistkeys[specialistkeys.length - 1]; specialist_id>specialistkeys[0]-1;specialist_id--) {
                var specialist = gamedatas.specialist[specialist_id];
                if (specialist.location != 'removed' && specialist.location != 'notplaced') {
                    if (specialist.type) {
                        this.addSpecialistOnBoard(specialist_id, specialist.name.replace(/ /g, ''), specialist.cathegory, specialist.location, specialist.location_arg, specialist.visible,1, specialist.ability, specialist.discount );
                    } else {
                        this.addSpecialistOnBoard(specialist_id, specialist.name, specialist.cathegory, specialist.location, specialist.location_arg, specialist.visible,0, null, specialist.discount  );
                    }
                }
            }

            // Add quests
            var questkeys = Object.keys(gamedatas.quest);
            // for(var quest_id = Object.keys(gamedatas.quest).length; quest_id>0;quest_id--) {
            for(var quest_id = questkeys[questkeys.length - 1]; quest_id>questkeys[0]-1;quest_id--) {
                var quest = gamedatas.quest[quest_id];
                if (quest.type ) {
                    this.addQuestOnBoard(quest_id, quest.name.replace(/ /g, '').replace(/'/g, ''), quest.cathegory, quest.location, quest.location_arg, quest.visible,1  );
                } else {
                    this.addQuestOnBoard(quest_id, quest.name, quest.cathegory, quest.location, quest.location_arg, quest.visible,0  );
                }
            }

            // Add treasures
            var treasurCardsInMenu = [];
            // var treasureIdsOrdered = this.orderTreasureCards();
            var treasureIdsOrdered = this.orderTreasureCards(gamedatas.treasure);
            // for(var treasure_id = Object.keys(gamedatas.treasure).length; treasure_id>0;treasure_id--) {
            //     var treasure = gamedatas.treasure[treasure_id];
            for(var i=0;i<treasureIdsOrdered.length;i++) {
                var treasure_id = treasureIdsOrdered[i];
                var treasure = gamedatas.treasure[treasure_id];
                if (treasure.type && treasure.location == this.player_id && treasure.location_arg > 19) {
                    treasurCardsInMenu.push(treasure);
                } else {
                    if (treasure.type) {
                        if (treasure.location_arg > 19 && this.isCurrentPlayerActive() ) {
                            treasurCardsInMenu.push(treasure);
                        } else {
                            this.addTreasureOnBoard(treasure_id, treasure.name.replace(/ /g, ''), treasure.color, treasure.location, treasure.location_arg, treasure.visible  );
                        }
                    } else {
                        this.addTreasureOnBoard(treasure_id, treasure.name, treasure.color, treasure.location, treasure.location_arg, treasure.visible  );
                    }
                }
            }

            if ( treasurCardsInMenu.length > 0) {
                this.addCardSelectionMenu(treasurCardsInMenu.length);
                for (var i=0;i< treasurCardsInMenu.length;i++) {
                    treasure = treasurCardsInMenu[i];
                    this.addTreasureOnBoard(treasure.id, treasure.name.replace(/ /g, ''), treasure.color, treasure.location, treasure.location_arg, treasure.visible  );
                }
            }

            // rest tokens
            for(var token_id in gamedatas.tokens ) {
                var token = gamedatas.tokens[token_id];

                if (token['type'] == 'baseresource' || token['type'] == 'advresource') {
                    this.addResourceOnBoard(token_id,token['type_arg'], token['location'], token['location_arg'] );
                }

                if (token['type'] == 'sigil') {
                    this.addSigilOnBoard(token_id,token['type_arg'], token['location'], token['location_arg'] );
                }

                if (token['type'] == 'thug') {
                    if (token['location'] != 'none' ) {
                        this.addThugOnBoard(token['location'], token['location_arg'] );
                    }
                }
            }

            dojo.connect($('masterroomtoggler'), 'click', this, 'toggleMasterRooms');   
            // dojo.connect($('showtreasure'), 'click', this, dojo.hitch(this, 'toggleCards', false, false));           
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            dojo.connect(window, "onresize", this, dojo.hitch(this, "resizeViewportSize"));
            this.placeOnObjectPos('discard', 'main_board', -dojo.style('tile_baseresource_2', 'width')/(1+1/2) ,0);

            dojo.addOnLoad( dojo.hitch(this, 'onLoadFunction'));

            this.addTooltipToClass( 'rotate', '', _( 'rotate to the other side' ) );
            this.addTooltip(player_id+'_questCounter_armor', _('armor number'), '');
            this.addTooltip('discard', _('discard pile'), 'show all discarded treasure card');
            dojo.connect($('discard'), "click", this, "showDiscardPile");
            dojo.connect($('showcompquests'), "click", this, "showCompletedQuests");

            // card decks tooltips
            this.makeTooltipForDeck('specialist');
            this.makeTooltipForDeck('quest');

            // this.addTooltip('hinttoggler','', _('View reference cards'));

            if (dojo.query('#handcontainer .treasurecontainer').length >0 ) {
                this.toggleCards(false, false);
            }

            if (this.gamedatas.gamestate.name == 'playerGather' || this.gamedatas.gamestate.name == 'playerExpand' || this.gamedatas.gamestate.name == 'playerCraft'  ) {

                if (this.gamedatas.playerorder.length == 1 && this.gamedatas.soloExpandSecondPart == "1") {
                    //
                } else {
                    this.cancelAction();
                }
            }
            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            if( this.isCurrentPlayerActive() ) {  
                switch( stateName ) {
                    
                    case 'playerGuildTurn':
                        // var cs = constructClientState(args.args.stateToSwitch, args.args.stateArgs);
                        // this.setClientState(cs['name'], cs['parameters']);
                        var cs = constructClientState(args.args[this.player_id].stateToSwitch, args.args[this.player_id].stateArgs);
                        this.setClientState(cs['name'], cs['parameters']);
                    break;

                    case "playerReplaceBonusResource":
                        // this.selectedResourcesId = [];
                        // this.selectedResourcesId.push(args.args.bonus_resource);

                        // var cs = constructClientState('replaceBonus', {'resource':args.args.bonus_resource, 'number': 1, 'alreadySelected': [], 'selectedResources': this.selectedResourcesId, });
                        // this.setClientState(cs['name'], cs['parameters']);
                        var cs = constructClientState('replaceBonus', {'number': args.args.number, 'alreadySelected': [], 'selectedResources': args.args.bonus_resource, });
                        this.setClientState(cs['name'], cs['parameters']);
                    break;

                    case 'client_placeRoom': 
                        // dojo.query('#room_'+args.args.room_id).addClass('selection');
                        dojo.query('#room_'+args.args.room_id).addClass('selected').removeClass('selection');
                        if (args.args.room == 'single') {
                            var tiles = args.args.possibleTiles.singletiles;
                        } else {
                            var tiles = args.args.possibleTiles.doubletiles;
                        }
                        for (var i=0; i<tiles.length;i++) {
                            if (args.args.room == 'single') {
                                // var destination_id = 'tile_player_room_'+tiles[i][0]+'_'+tiles[i][1]+'_'+this.player_id;
                                var destination_id = 'tile_room_'+tiles[i][0]+'_'+tiles[i][1]+'_'+this.player_id;
                            } else {
                                // var destination_id = 'doubletile_player_room_'+tiles[i][0]+'_'+tiles[i][1]+'_'+this.player_id;
                                var destination_id = 'doubletile_room_'+tiles[i][0]+'_'+tiles[i][1]+'_'+this.player_id;
                            }

                            dojo.addClass(destination_id, 'selection');
                            this.handlers.push( dojo.connect($(destination_id),  'click',  this, dojo.partial(this.moveRoomToPlayer, 'room_'+args.args.room_id, destination_id ) ));
                        }
                    break;

                    case 'client_placeSpecialist': 
                        // dojo.query('#specialist_'+args.args.specialist_id+'_front').addClass('selection');
                        dojo.query('#specialist_'+args.args.specialist_id+'_front').addClass('selected').removeClass('selection');
                        var tiles = args.args.possibleTiles;

                        for (var i=0; i<tiles.length;i++) {
                            var destination_id = 'tile_specialist_'+tiles[i]+'_'+this.player_id;

                            dojo.addClass(destination_id, 'selection');
                            if (args.args.specialist_id != null) {
                                this.handlers.push( dojo.connect($(destination_id),  'click',  this, dojo.partial(this.moveSpecialistToPlayer, 'specialist_'+args.args.specialist_id, destination_id ) ));
                            }
                        }
                    break;

                    case 'playerTurn':
                        this.roomPosition = 0;
                        this.specialistPosition = '0';
                        this.endPhaseActive = false;

                        dojo.addClass('masterroomtoggler', 'glowing');

                        var cards = dojo.query('#player_cards .treasure.treasureenlarged').addClass('forSelection');
                        for(var i=0;i<cards.length;i++) {
                            this.handlers.push( dojo.connect(cards[i],'click',this, 'playTreasureCard') );
                        }

                        if(args.args != null && args.args.offeringActive ) {
                            if (args.args.vizierActive ) {
                                var text = _('The end of game has been triggered, you can make an offering for free (Vizier) and take a last usual action');
                            } else {
                                var text = _('The end of game has been triggered, you can either make an offering to the council or take a last usual action');
                            }

                            var btntxt = _('OK');
                            var closebtn = '<div style= "text-align: center;"><div id="infoclose" class="bgabutton bgabutton_blue"><span>'+btntxt+'</span></div></div>';
                            this.myDlg = new ebg.popindialog();
                            this.myDlg.create( 'lastturninfo' );
                            this.myDlg.setTitle( "" );
                            this.myDlg.setMaxWidth( 350 ); 
                            var html = '<div style="font-size: 20px;">'+text+'</div>'+closebtn;
                            this.myDlg.setContent( html ); 
                            this.myDlg.show();

                            dojo.connect( $('infoclose'), 'onclick', this, function(evt){
                                evt.preventDefault();
                                this.myDlg.destroy();
                            } );
                        }

                        // new verison items -------------------------------------------------------------------------------------

                        // Resources
                        this.selectedResourcesId = [];
                        var res = dojo.query('#main_board .resource');

                        for(var i=0;i<res.length;i++) {
                            var cls = res[i].id.split('_')[1];
                            dojo.addClass(res[i].id, 'selection');
                            this.handlers.push( dojo.connect(res[i],'click',this, dojo.partial(this.selectResource, cls )) );
                        }

                        // Expand items
                        var selection = dojo.query('#main_board .tile > .room:last-child').addClass('selection');
                        selection = selection.concat(dojo.query('#main_board .tile > .roomcontainer:last-child > .room').addClass('selection'));
                        selection = selection.concat(dojo.query('#main_board .tile > .specialistcontainer > .specialist:not(.specialistback)').addClass('selection'));
                        for(var i=0;i<selection.length;i++) {
                            var id = selection[i].id.split('_')[1];
                            var type = selection[i].id.split('_')[0];
                            this.handlers.push( dojo.connect(selection[i],'click',this, dojo.partial(this.selectExpandItem, id, type )) );
                        }

                        //Craft items
                        var q = dojo.query('.tilequest:empty ').addClass('selection');
                        for(var i=0;i<q.length;i++) {
                            this.handlers.push( dojo.connect(q[i],'click',this, 'craftItem') );
                        }

                    break;

                    case 'playerEndTurn':
                        this.endPhaseActive = true;
                        // this.toggleCards(true, false);
                        // var cards = dojo.query('#player_cards .treasurecontainer').addClass('forSelection');
                        var cards = dojo.query('#player_cards .treasure.treasureenlarged').addClass('forSelection');
                        for(var i=0;i<cards.length;i++) {
                            this.handlers.push( dojo.connect(cards[i],'click',this, 'playTreasureCard') );
                        }
                    break;

                    case 'playerGather':
                        // this.selectedResourcesId = [];
                        // var res = dojo.query('#main_board .resource');

                        // for(var i=0;i<res.length;i++) {
                        //     var cls = res[i].id.split('_')[1];
                        //     dojo.addClass(res[i].id, 'selection');
                        //     this.handlers.push( dojo.connect(res[i],'click',this, dojo.partial(this.selectResource, cls )) );
                        // }
                    break;

                    case 'client_playerGather':
                        var res = dojo.query('#main_board .resource');

                        for(var i=0;i<res.length;i++) {
                            var cls = res[i].id.split('_')[1];
                            if( this.selectedResourcesId.indexOf(cls) < 0) {
                                dojo.addClass(res[i].id, 'selection');
                                this.handlers.push( dojo.connect(res[i],'click',this, dojo.partial(this.selectResource, cls )) );
                            }
                        }

                        for (var i=0; i<this.selectedResourcesId.length;i++) {
                            dojo.addClass('resource_'+this.selectedResourcesId[i], 'selected');
                            var cls = 'resource_'+this.selectedResourcesId[i];
                            this.handlers.push( dojo.connect($(cls),'click',this, dojo.partial(this.deselectResource, cls ) ) );
                        }
                    break;
                
                    case 'client_playerGatherAndReplace':
                        var res = dojo.query('#player_mat_'+this.player_id+' .resource');

                        for(var i=0;i<res.length;i++) {
                            var cls = res[i].id.split('_')[1];
                            if( args.args.alreadySelected.indexOf(cls) < 0) {
                                dojo.addClass(res[i].id, 'selection');
                                this.handlers.push( dojo.connect(res[i],'click',this, dojo.partial(this.selectResourceForReplace, cls, args.args.number, args.args.alreadySelected, args.args.selectedResources, args.args.bonusVariant, args.args.treasureVariant )) );
                            }
                        }
                    break;

                    case 'playerExpand':
                        if (this.gamedatas.playerorder.length == 1 && this.gamedatas.soloExpandSecondPart == "1") {
                            this.roomPosition = 0;
                            this.specialistPosition = '0';

                            dojo.addClass('masterroomtoggler', 'glowing');

                            var cards = dojo.query('#player_cards .treasure.treasureenlarged').addClass('forSelection');
                            for(var i=0;i<cards.length;i++) {
                                this.handlers.push( dojo.connect(cards[i],'click',this, 'playTreasureCard') );
                            }

                            // Expand items
                            var selection = dojo.query('#main_board .tile > .room:last-child').addClass('selection');
                            selection = selection.concat(dojo.query('#main_board .tile > .roomcontainer:last-child > .room').addClass('selection'));
                            selection = selection.concat(dojo.query('#main_board .tile > .specialistcontainer > .specialist:not(.specialistback)').addClass('selection'));
                            for(var i=0;i<selection.length;i++) {
                                var id = selection[i].id.split('_')[1];
                                var type = selection[i].id.split('_')[0];
                                this.handlers.push( dojo.connect(selection[i],'click',this, dojo.partial(this.selectExpandItem, id, type )) );
                            }
                        }
                    break;

                    case 'client_hireSpecialist':
                        var selection = dojo.query('#main_board .tile > .specialistcontainer > .specialist:not(.specialistback)').addClass('selection');
                        for(var i=0;i<selection.length;i++) {
                            var id = selection[i].id.split('_')[1];
                            var type = selection[i].id.split('_')[0];
                            this.handlers.push( dojo.connect(selection[i],'click',this, dojo.partial(this.selectExpandItem, id, type )) );
                        }
                    break;

                    case 'playerBuildRoomOnly':
                        var selection = dojo.query('#main_board .tile > .room:last-child').addClass('selection');
                        // var selection = dojo.query('#main_board .tile > .room:last-child').addClass('selected');
                        selection = selection.concat(dojo.query('#main_board .tile > .roomcontainer:last-child > .room').addClass('selection'));
                        for(var i=0;i<selection.length;i++) {
                            var id = selection[i].id.split('_')[1];
                            var type = selection[i].id.split('_')[0];
                            this.handlers.push( dojo.connect(selection[i],'click',this, dojo.partial(this.selectExpandItem, id, type )) );
                        }

                        // var cards = dojo.query('#player_cards .treasurecontainer').addClass('forSelection');
                        var cards = dojo.query('#player_cards .treasure.treasureenlarged').addClass('forSelection');
                        for(var i=0;i<cards.length;i++) {
                            this.handlers.push( dojo.connect(cards[i],'click',this, 'playTreasureCard') );
                        }
                    break;

                    case 'playerHireSpecialistOnly':
                        var selection = dojo.query('#main_board .tile > .specialistcontainer > .specialist:not(.specialistback)').addClass('selection');

                        for(var i=0;i<selection.length;i++) {
                            var id = selection[i].id.split('_')[1];
                            var type = selection[i].id.split('_')[0];
                            this.handlers.push( dojo.connect(selection[i],'click',this, dojo.partial(this.selectExpandItem, id, type )) );
                        }

                        // var cards = dojo.query('#player_cards .treasurecontainer').addClass('forSelection');
                        var cards = dojo.query('#player_cards .treasure.treasureenlarged').addClass('forSelection');
                        for(var i=0;i<cards.length;i++) {
                            this.handlers.push( dojo.connect(cards[i],'click',this, 'playTreasureCard') );
                        }
                    break;

                    case 'playerSpecialistOneTimeAction':
                        this.selectedResourcesId = [];
                        if ('parameters' in args.args ) {
                            if (args.args.parameters.dealerPass) {
                                this.dealerPassActive = true;
                            }
                        }
                        var cs = constructClientState(args.args.action_name, args.args.parameters );
                        this.setClientState(cs['name'], cs['parameters']);
                    break;

                    case 'client_tradeResources':
                        var resSell = dojo.query('#player_mat_'+this.player_id+' .resource');

                        for(var i=0;i<resSell.length;i++) {
                            var cls = resSell[i].id.split('_')[1];

                            dojo.addClass(resSell[i].id, 'selection');
                            this.handlers.push( dojo.connect(resSell[i],'click',this, dojo.partial(this.selectResourceForTrade, cls, 'sell', args.args.forBuy )) );
                        
                        }

                        var resBuy = dojo.query('#main_board'+' .resource');

                        for(var i=0;i<resBuy.length;i++) {
                            var cls = resBuy[i].id.split('_')[1];

                            dojo.addClass(resBuy[i].id, 'selection');
                            this.handlers.push( dojo.connect(resBuy[i],'click',this, dojo.partial(this.selectResourceForTrade, cls, 'buy', args.args.forSell )) );  
                        }
                    break;

                    case 'client_stealResource':
                        var res = dojo.query('.restboardswrap .resource');

                        for(var i=0;i<res.length;i++) {
                            var cls = res[i].id.split('_')[1];
                            dojo.addClass(res[i].id, 'selection');
                            this.handlers.push( dojo.connect(res[i],'click',this, dojo.partial(this.selectResourceForSteal, cls, args.args.selected, args.args.replaceTrigger, args.args.selectedForReplace )) );
                        }

                        // in case of return
                        if (args.args.replaceTrigger) {
                            var res = dojo.query('#player_mat_'+this.player_id+' .resource');

                            for(var i=0;i<res.length;i++) {
                                var cls = res[i].id.split('_')[1];
                                dojo.addClass(res[i].id, 'selection');
                                this.handlers.push( dojo.connect(res[i],'click',this, dojo.partial(this.selectResourceForSteal, cls, args.args.selected, args.args.replaceTrigger, args.args.selectedForReplace )) );
                            }
                        }
                    break;

                    case 'playerCraft':
                        // var q = dojo.query('.tilequest:empty ').addClass('selection');
                        // for(var i=0;i<q.length;i++) {
                        //     this.handlers.push( dojo.connect(q[i],'click',this, 'craftItem') );
                        // }
                    break;

                    case 'client_playerCraft':
                        var q = dojo.query('.tilequest:empty ').addClass('selection');
                        for(var i=0;i<q.length;i++) {
                            this.handlers.push( dojo.connect(q[i],'click',this, 'craftItem') );
                        }
                    break;

                    case 'client_craftItem':
                        this.selectedCraftItem[0] = args.args.quest_id;
                        this.selectedCraftItem[1] = args.args.item_id;
                        // dojo.query('#tile_quest_'+ args.args.quest_id+'_'+ args.args.item_id).addClass('selection');
                        dojo.query('#tile_quest_'+ args.args.quest_id+'_'+ args.args.item_id).addClass('selected');
                    break;

                    case 'playerSelectTreasureCard':
                        var tr = args.args.possible_treasures;
                        for (var i=0;i<tr.length;i++) {
                            // dojo.addClass('treasure_'+tr[i], 'forSelection');
                            // this.handlers.push( dojo.connect(dojo.query('#treasure_'+tr[i])[0],'click',this, dojo.partial(this.selectTreasure, tr[i] )));
                            dojo.addClass('treasure_'+tr[i]+'_front', 'forSelection');
                            this.handlers.push( dojo.connect(dojo.query('#treasure_'+tr[i]+'_front')[0],'click',this, dojo.partial(this.selectTreasure, tr[i] )));
                        }
                    break;

                    case 'playerSellTreasure':
                        if (args.args.appraiser) {
                            var cs = constructClientState('appraiserDiscard', args.args[this.player_id] );
                            this.setClientState(cs['name'], cs['parameters']);
                        } else {
                            var cs = constructClientState('playerSellTreasure', args.args[this.player_id] );
                            this.setClientState(cs['name'], cs['parameters']);
                        }
                    break;

                    case 'client_playerSellTreasure':
                        var tr = args.args.possible_treasures;
                        for (var i=0;i<tr.length;i++) {
                            // dojo.addClass('treasure_'+tr[i], 'forSelection');
                            // this.handlers.push( dojo.connect(dojo.query('#treasure_'+tr[i])[0],'click',this, dojo.partial(this.selectTreasure, tr[i] )));
                            dojo.addClass('treasure_'+tr[i]+'_front', 'forSelection');
                            this.handlers.push( dojo.connect(dojo.query('#treasure_'+tr[i]+'_front')[0],'click',this, dojo.partial(this.selectTreasure, tr[i] )));
                        }

                        if (args.args.selected_treasure) {
                            // dojo.addClass(args.args.selected_treasure, 'selected');
                            dojo.addClass(args.args.selected_treasure+'_front', 'selected');
                        }
                    break;

                    case  'playerSpecialistCraftAction':
                        var cs = constructClientState(args.args.action_name, args.args.parameters );
                        this.setClientState(cs['name'], cs['parameters']);
                    break;

                    case 'client_playTreasureCard':
                        // dojo.addClass('treasure_'+args.args.selectedCard, 'selected');
                        dojo.addClass('treasure_'+args.args.selectedCard+'_front', 'selected');
                    break;

                    case 'playerPlayTreasureEffect':
                        // dojo.addClass('treasure_'+args.args.selectedCard, 'selected');
                        dojo.addClass('treasure_'+args.args.selectedCard+'_front', 'selected');

                        var cs = constructClientState(args.args.switchToState, args.args.parameters );
                        this.setClientState(cs['name'], cs['parameters']);
                    break;

                    case 'playerPlaceKingStatue':
                        var cs = constructClientState(args.args.action_name, args.args.parameters );
                        this.setClientState(cs['name'], cs['parameters']);
                    break;

                    case 'client_placeThug':
                        var quests = args.args.possibleQuests;
                        for (var i=0;i<quests.length;i++) {
                            dojo.addClass('quest_'+quests[i], 'forSelection');
                            this.handlers.push( dojo.connect(dojo.query('#quest_'+quests[i])[0],'click',this, dojo.partial(this.selectQuest, quests[i] )));
                        }
                    break;

                    case 'client_selectQuestcard':
                        var quests = args.args.possibleQuests;
                        for (var i=0;i<quests.length;i++) {
                            dojo.addClass('quest_'+quests[i], 'forSelection');
                            this.handlers.push( dojo.connect(dojo.query('#quest_'+quests[i])[0],'click',this, dojo.partial(this.selectQuest, quests[i] )));
                        }
                    break;

                    case 'client_selectSpecialist' :
                        var specialist = dojo.query('#player_mat_'+this.player_id+' .specialist').addClass('selection');
                        for (var i=0;i<specialist.length;i++) {
                            this.handlers.push( dojo.connect( specialist[i],'click',this, 'selectSpecialistForBardAction'  ));
                        }
                    break;
                
                    case 'dummmy':
                    break;
                }
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
                case 'client_placeRoom': 
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    dojo.query('.selection').removeClass('selection');
                    dojo.query('.selected').removeClass('selected');
                    this.roomPosition = 0;
                    this.specialistPosition ='0';
                break;

                case 'client_placeSpecialist': 
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    dojo.query('.selection').removeClass('selection');
                    dojo.query('.selected').removeClass('selected');
                    this.roomPosition = 0;
                    this.specialistPosition = '0';
                break;

                case 'playerTurn':
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    // dojo.removeClass('actionpanel', 'opened');
                    // dojo.addClass('actionpanel', 'closed');
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selection').removeClass('selection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.removeClass('masterroomtoggler', 'glowing');
                break;

                case 'playerEndTurn':
                    // this.toggleCards(false, true);
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                break;

                case 'playerGather':
                    // dojo.query('.resource.selection').removeClass('selection');
                    // dojo.forEach(this.handlers,dojo.disconnect);
                    // this.handlers = [];
                break;

                case 'client_playerGather':
                    dojo.query('.resource.selected').removeClass('selected');
                    dojo.query('.resource.selection').removeClass('selection');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break;

                case 'client_playerGatherAndReplace':
                    dojo.query('.resource.selection').removeClass('selection');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break;

                case 'playerExpand':
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selection').removeClass('selection');
                    dojo.query('.room.selection').removeClass('selected');
                    dojo.query('.specialist.selection').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break;   

                case 'client_hireSpecialist':
                    dojo.query('.specialist.selection').removeClass('selection');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    this.specialistPosition = '0';
                break;
                
                case 'playerBuildRoomOnly':
                    dojo.query('.room.selection').removeClass('selection');
                    dojo.query('.specialist.selection').removeClass('selection');
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break; 

                case 'playerHireSpecialistOnly':
                    dojo.query('.room.selection').removeClass('selection');
                    dojo.query('.specialist.selection').removeClass('selection');
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break; 

                case 'client_tradeResources': 
                    dojo.query('.resource.selection').removeClass('selection');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break;

                case 'client_stealResource': 
                    dojo.query('.resource.selection').removeClass('selection');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break;

                case 'playerCraft':
                        dojo.query('.tilequest').removeClass('selection');
                        dojo.query('.tilequest').removeClass('selected');
                        dojo.forEach(this.handlers,dojo.disconnect);
                        this.handlers = [];
                        this.selectedCraftItem = [];
                break;

                case 'client_playerCraft':
                        dojo.query('.tilequest').removeClass('selection');
                        dojo.forEach(this.handlers,dojo.disconnect);
                        this.handlers = [];
                        this.selectedCraftItem = [];
                break;

                case 'client_craftItem':
                        dojo.query('.tilequest').removeClass('selection');
                        dojo.query('.tilequest').removeClass('selected');
                        dojo.forEach(this.handlers,dojo.disconnect);
                        this.handlers = [];
                        this.selectedCraftItem = [];
                break;

                case 'playerSelectTreasureCard':
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    this.selectedTreasure = [];
                break;

                case 'playerSellTreasure':
                    // dojo.query('.forSelection').removeClass('forSelection');
                    // dojo.query('.selected').removeClass('selected');
                    // dojo.forEach(this.handlers,dojo.disconnect);
                    // this.handlers = [];
                    // this.selectedTreasure = [];
                    // dojo.destroy('card_menu0');
                    // dojo.destroy('card_menu1');
                break;

                case 'client_playerSellTreasure':
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    this.selectedTreasure = [];
                    dojo.destroy('card_menu0');
                    if ( !this.gamedatas.gamestate.args.dontdestroy) {
                        dojo.destroy('card_menu1');
                    }
                break;

                case 'client_playTreasureCard':
                    this.selectedTreasureForPlay = 0;
                    dojo.query('.selected').removeClass('selected');
                break;

                case 'playerPlayTreasureEffect':
                    // dojo.query('.selected').removeClass('selected');
                break;

                case 'nextPlayerPlayTreasure':
                    dojo.query('.selected').removeClass('selected');
                break;

                case 'client_placeThug':
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    this.selectedQuest = 0;
                break;

                case 'client_selectQuestcard':
                    dojo.query('.forSelection').removeClass('forSelection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                    this.selectedQuest = 0;
                break;

                case 'client_selectSpecialist' :
                    dojo.query('.selection').removeClass('selection');
                    dojo.query('.selected').removeClass('selected');
                    dojo.forEach(this.handlers,dojo.disconnect);
                    this.handlers = [];
                break;

                case 'client_takeTreasure' :
                    this.gamedatas.gamestate.descriptionmyturn = '';
                    this.gamedatas.gamestate.acive_player = null;
                case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName ) {
                    case 'client_takeResources': 
                        if (args.startBonus) {
                            for(var i=0;i<args.resources.length;i++) {
                                var txt = '${resource1}${resource2}';

                                var paramObject = {};
                                for (j = 1; j < 3; j++){
                                    paramObject['resource'+j] = 'resource_'+args.resources[i][j-1];
                                }
                                var str = this.format_string_recursive(txt,paramObject );
                                this.addActionButton( 'takeres'+i, str, dojo.partial(this.takeResources,i, args.resources, 0, true), null, null, 'gray');
                            }
                        } else {
                            for(var i=0;i<args.resources.length;i++) {
                                var txt = '';
                                var paramObject = {};
                                for(var j=1;j<args.resources[i].length+1;j++) {
                                    txt += '${resource'+j+'}';
                                    paramObject['resource'+j] = 'resource_'+args.resources[i][j-1];
                                }

                                var str = this.format_string_recursive(txt,paramObject );
                                if (args.drop) {
                                    this.addActionButton( 'takeres'+i, str, dojo.partial(this.selectResource, args.resources[i] ), null, null, 'gray');
                                } else {
                                    this.addActionButton( 'takeres'+i, str, dojo.partial(this.takeResources,i, args.resources, args.replaceCount, true), null, null, 'gray');
                                }
                                
                            }
                        }
                    break;

                    case 'client_takeTreasure': 
                        if (args.cards_sel) {
                            for(var i=0;i<args.treasure.length;i++) {
                                var str = this.format_string_recursive('${cardback1} ${cardback2}', {
                                    cardback1: 'treasure_'+args.treasure[i][0],
                                    cardback2: 'treasure_'+args.treasure[i][1],
                                });
                                this.addActionButton( 'taketreasure_'+args.treasure[i], str, dojo.partial(this.takeTreasure,args.treasure[i] ), null, null, 'gray');
                            }
                        } else if (args.merchant) {
                            for(var i=0;i<args.treasure.length;i++) {
                                var str = this.format_string_recursive('${cardback1} ${cardback2} ${cardback3}', {
                                    cardback1: 'treasure_'+args.treasure[i][0],
                                    cardback2: 'treasure_'+args.treasure[i][1],
                                    cardback3: 'treasure_'+args.treasure[i][2],
                                });
                                this.addActionButton( 'taketreasure_'+args.treasure[i], str, dojo.partial(this.takeTreasure,args.treasure[i] ), null, null, 'gray');
                            }
                        } else {
                            for(var i=0;i<args.treasure.length;i++) {
                                var str = this.format_string_recursive('${cardback}', {
                                    cardback: 'treasure_'+args.treasure[i],
                                });
                                this.addActionButton( 'taketreasure_'+args.treasure[i], str, dojo.partial(this.takeTreasure,args.treasure[i] ), null, null, 'gray');
                            }
                        }

                        if (args.pass) {
                            this.addActionButton( 'pass', _('Do not take'), 'passAction');
                        }

                        if (args.cards_sel) {
                            for(var i=0;i<args.cards_sel.length;i++) {
                               dojo.addClass(args.cards_sel[i], 'selected');
                            }
                        }
                    break;

                    case 'client_placeRoom': 
                        this.addActionButton( 'confirm', _('Confirm'), dojo.partial(this.confirmRoomPlacement,args.room_id) );
                        if (args.cancel) {
                            this.addActionButton( 'cancelroom', _('Cancel'), 'cancelClientState'  );
                        }
                    break;
                    
                    case 'client_placeSpecialist': 
                        this.addActionButton( 'confirm', _('Confirm'), dojo.partial(this.confirmSpecialistPlacement,args.specialist_id) );
                        if (args.cancel) {
                            this.addActionButton( 'cancelspecialist', _('Cancel'), 'cancelClientState'  );
                        }
                    break;

                    case 'playerGather':
                        // this.addActionButton( 'cancel', _('cancel gathering'), 'cancelAction'  );
                    break;

                    case 'client_playerGather':
                        if (this.selectedResourcesId.length > 0) {
                            var txt = _('Gather ');
                            var paramObject = {};

                            for(var i=0;i<this.selectedResourcesId.length;i++) {
                                txt += '${resource'+i+'}';
                                paramObject['resource'+i] = 'resource_'+this.gamedatas.tokens[this.selectedResourcesId[i]].type_arg;
                            }

                            var str = this.format_string_recursive(txt,paramObject );
                            this.addActionButton( 'takeres'+i, str, dojo.partial(this.takeResources,0, [this.selectedResourcesId], args.replaceTrigger, args.maxReached), null, null, 'gray');
                            dojo.addClass('takeres'+i, 'actioncustombutton resbutton');
                        }
                        this.addActionButton( 'cancel', _('Cancel'), 'cancelClientState'  );
                        if (this.dealerPassActive) {
                            this.addActionButton( 'pass', _('Do not use'), 'passAction'  );
                        }
                    break;

                    case 'client_playerGatherAndReplace':
                        if ( !args.treasureVariant) {
                            var txt = _('Gather ');
                            var paramObject = {};
                            for(var i=0;i<args.selectedResources.length;i++) {
                                txt += '${resource'+i+'}';
                                if (args.selectedResources[i].length < 3)  {
                                    paramObject['resource'+i] = 'resource_'+this.gamedatas.tokens[args.selectedResources[i]].type_arg;
                                } else {
                                    paramObject['resource'+i] = 'resource_'+args.selectedResources[i];
                                }
                            }

                            if (args.alreadySelected.length>0) {
                                txt += _(' and return ');
                                for(var j=i+1;j<(args.alreadySelected.length+i+1);j++) {
                                    txt += '${resource'+j+'}';
                                    paramObject['resource'+j] = 'resource_'+this.gamedatas.tokens[args.alreadySelected[j-i-1]].type_arg;
                                }
                            }
                            var str = this.format_string_recursive(txt,paramObject );
                            this.addActionButton( 'takeres'+i, str, dojo.partial(this.takeResourcesAndReplace, args.selectedResources, args.alreadySelected, args.number, false), null, null, 'gray');
                            dojo.addClass('takeres'+i, 'actioncustombutton resbutton');
                            this.addActionButton( 'cancel', _('Cancel'), 'cancelClientState'  );
                        } else {
                            var txt1 = _('Take only ');
                            var txt2 = _('Do not take any ');
                            var txt3 = _('Take ');
                            var number_deduct = args.number - args.alreadySelected.length;
                            if (args.selectedResources.length - number_deduct == 0 ) {
                                var txt = txt2;
                                var endcount = args.selectedResources.length;
                            } else if (args.selectedResources.length - number_deduct > 0 && number_deduct> 0) {
                                var txt = txt1;
                                var endcount = args.selectedResources.length - number_deduct;
                            } else {
                                var txt = txt3;
                                var endcount = args.selectedResources.length - number_deduct;
                            }
                            var arrayOfPermutations = [];
                            var permutes = this.permute(args.selectedResources) ;
                            arrayOfPermutations.push(permutes[0]);

                            if ( permutes.length > 1 && endcount !=  args.selectedResources.length ) {
                                for(var i=1;i<permutes.length;i++) {
                                    var include = true;
                                    for (var j=0;j<arrayOfPermutations.length;j++) {
                                        var subarrayA = arrayOfPermutations[j].slice(0,endcount);
                                        var subarrayB = permutes[i].slice(0,endcount);
                                        subarrayA.sort(); 
                                        subarrayB.sort(); 
                                        if ( this.arraysEqual(subarrayA, subarrayB)  ) {
                                            include = false;
                                        }
                                    }

                                    if (include) {
                                        arrayOfPermutations.push(permutes[i]);
                                    }
                                }
                            }

                            for(var k=0;k<arrayOfPermutations.length;k++) {
                                var text = txt;
                                var actualSet = arrayOfPermutations[k];
                                var paramObject = {};
                                for(var i=0;i<endcount;i++) {
                                    text += '${resource'+i+'}';
                                    if (actualSet[i].length < 3)  {
                                        paramObject['resource'+i] = 'resource_'+this.gamedatas.tokens[actualSet[i]].type_arg;
                                    } else {
                                        paramObject['resource'+i] = 'resource_'+actualSet[i];
                                    }
                                }

                                if (args.alreadySelected.length>0) {
                                    text += _(' and return ');
                                    for(var j=i+1;j<(args.alreadySelected.length+i+1);j++) {
                                        text += '${resource'+j+'}';
                                        paramObject['resource'+j] = 'resource_'+this.gamedatas.tokens[args.alreadySelected[j-i-1]].type_arg;
                                    }
                                }
                                var str = this.format_string_recursive(text,paramObject );
                                this.addActionButton( 'takeres'+k, str, dojo.partial(this.takeResourcesAndReplace, actualSet, args.alreadySelected, args.number, false), null, null, 'gray');
                                dojo.addClass('takeres'+k, 'actioncustombutton resbutton');

                            }
                            this.addActionButton( 'cancel', _('Cancel'), dojo.partial(this.cancelReplaceResource, args.number, args.alreadySelected, args.selectedResources, args.bonusVariant, args.treasureVariant ) );
                            if (this.dealerPassActive) {
                                this.addActionButton( 'pass', _('Do not use'), 'passAction'  );
                            }
                        }
                    break;

                    case 'client_tradeResources':
                        var txtA = _('Trade ');
                        var txtB = _('for ');
                        var txt = '';
                        var paramObject = {};

                        if (args.forSell) {
                            txt = txtA+'${resource1} ';
                            paramObject['resource1'] = 'resource_'+this.gamedatas.tokens[args.forSell].type_arg;
                        }
                        if (args.forBuy) {
                            txt += txtB+'${resource2}';
                            paramObject['resource2'] = 'resource_'+this.gamedatas.tokens[args.forBuy].type_arg;
                        }
                        var str = this.format_string_recursive(txt,paramObject );

                        if (str .length > 3 ) {
                            this.addActionButton( 'trade', str, dojo.partial(this.takeResourcesAndReplace, [args.forBuy], [args.forSell], 1, true), null, null, 'gray' );
                        }
                        this.addActionButton( 'stoptrading', _('Stop trading'), 'passAction');
                    break;

                    case 'playerExpand':
                        if (this.gamedatas.playerorder.length == 1 && this.gamedatas.soloExpandSecondPart == "1") {
                            this.addActionButton( 'pass', _('Pass'), 'passAction'  );
                        }
                    break;

                    case 'playerBuildRoomOnly':
                        this.addActionButton( 'pass', _('Pass'), 'passAction'  );
                    break;

                    case 'playerHireSpecialistOnly':
                        this.addActionButton( 'pass', _('Pass'), 'passAction'  );
                    break;

                    case 'playerCraft':
                        this.addActionButton( 'confirmCraft', _('Craft'), 'confirmCraft'  );
                        this.addActionButton( 'cancel', _('Cancel'), 'cancelAction'  );
                    break;

                    case 'client_playerCraft':
                        this.addActionButton( 'confirmCraft', _('Craft'), 'confirmCraft'  );
                        this.addActionButton( 'cancel', _('Cancel'), 'cancelSelection'  );
                        this.addActionButton( 'pass', _('Do not use special ability'), 'passAction'  );
                    break;

                    case 'client_craftItem':
                        var str = '';
                        if (args.thug != null) {
                            var paramObject = {};
                            var txt = _('(and pay ${gold} to ${player_name_id} )');
                            paramObject['player_name_id'] = args.thug.player_id;
                            paramObject['gold'] = 'gold_'+args.thug.value;
                            str = this.format_string_recursive(txt,paramObject );
                        }
                        if (args.second_item) {
                            this.addActionButton( 'confirmSingleCraft', _('Craft single item only ')+str, 'confirmCraft' );
                            this.addActionButton( 'confirmDoubleCraft', _('Craft both items ')+str, 'confirmCraft'  );
                            this.addActionButton( 'cancel', _('Cancel'), 'cancelClientState'  );
                            dojo.addClass('confirmSingleCraft', 'kgbutton');
                            dojo.addClass('confirmDoubleCraft', 'kgbutton');
                        } else {
                            this.addActionButton( 'confirmCraft', _('Craft ')+str, 'confirmCraft'  );
                            dojo.addClass('confirmCraft', 'kgbutton');
                            this.addActionButton( 'cancel', _('Cancel'), 'cancelAction'  );
                        }
                    break;

                    case 'client_stealResource': 
                        var txt = '';
                        var paramObject = {};

                        if (args.selected && !args.replaceTrigger) {
                            txt = _('Steal ${resource} from ${player_name_id}');
                            paramObject['resource'] = 'resource_'+this.gamedatas.tokens[args.selected].type_arg;
                            var player_id = dojo.query('#resource_'+args.selected)[0].parentNode.id.split("_")[3];
                            paramObject['player_name_id'] = this.gamedatas.players[player_id].id;
                        }

                        if (args.replaceTrigger && args.selected ) {
                            txt = _('Steal ${resource} from ${player_name_id} and return ${resource1}');
                            paramObject['resource'] = 'resource_'+this.gamedatas.tokens[args.selected].type_arg;
                            if (args.selectedForReplace) {
                            paramObject['resource1'] = 'resource_'+this.gamedatas.tokens[args.selectedForReplace].type_arg;
                            } else {paramObject['resource1'] = ''; }
                            var player_id = dojo.query('#resource_'+args.selected)[0].parentNode.id.split("_")[3];
                            paramObject['player_name_id'] = this.gamedatas.players[player_id].id;
                        }
                        
                        var str = this.format_string_recursive(txt,paramObject );

                        if (str.length > 3) {
                            this.addActionButton( 'steal', str, dojo.partial(this.stealResource, args.selected, args.replaceTrigger, args.selectedForReplace) );
                            dojo.addClass('steal', 'kgbutton');
                        }

                        this.addActionButton( 'pass', _('Do not use'), 'passAction'  );
                    break;

                    case 'playerSelectTreasureCard':
                        this.addActionButton( 'confirm', _('Confirm'), 'confirmTreasureSelection'  );
                    break;

                    case 'playerSellTreasure':
                        // this.addActionButton( 'confirm', _('confirm'), 'confirmTreasureSelection'  );
                    break;

                    case 'client_playerSellTreasure':
                        this.addActionButton( 'confirm', _('Confirm'), 'confirmTreasureSelection'  );
                        if (args.pass) {
                            this.addActionButton( 'pass', _('Do not use'), 'passAction'  );
                        }
                    break;

                    case 'client_placeThug':
                        this.addActionButton( 'confirm', _('Confirm'), 'confirmQuestSelection'  );
                        this.addActionButton( 'pass', _('Do not use'), 'passAction'  );  
                    break;

                    case 'client_playTreasureCard':
                        this.addActionButton( 'play', _('Play'), 'playSelctedTreasure'  );
                        this.addActionButton( 'sell', _('Sell'), 'playSelctedTreasure'  );
                        this.addActionButton( 'cancel', _('Cancel'), 'cancelClientState'  );
                    break;
                    
                    case 'client_selectQuestcard':
                        this.addActionButton( 'confirm', _('Confirm'), 'confirmQuestSelection'  );
                        this.addActionButton( 'pass', _('Do not use'), 'passAction'  );
                    break;

                    case 'playerTurn':
                        if(args != null && args.bardActionActive ) {
                            this.addActionButton( 'bard', _('Move specialist (Bard action)'), 'bardAction'  );
                        }
                        if(args != null && args.oracleActionActive ) {
                            this.addActionButton( 'oracle', _('Look at top 2 quests (Oracle action)'), 'oracleAction'  );
                        }
                        if(args != null && args.offeringActive ) {
                            if (args.vizierActive ) {
                                this.addActionButton( 'offering', _('Make offering to the Council for free - Vizier'), 'offeringAction'  );
                            } else {
                                this.addActionButton( 'offering', _('Make offering to the Council'), 'offeringAction'  );
                            }
                        }

                        if (args.soloKingsFuneral == 1) {
                            this.addActionButton( 'soloFuneral', _("Build King's Statue"), 'soloFuneral'  );
                        }

                        this.addActionButton( 'hint', _('Show hint') , 'showHint'  );
                        // dojo.addClass('hint', 'actioncustombutton');

                    break;

                    case 'playerEndTurn':
                        if(args != null && args.bardActionActive ) {
                            this.addActionButton( 'bard', _('Move specialist (Bard action)'), 'bardAction'  );
                        }
                        if(args != null && args.oracleActionActive ) {
                            this.addActionButton( 'oracle', _('Look at top 2 quests (Oracle action)'), 'oracleAction'  );
                        }
                        if (args.vizierActionActive) {
                            this.addActionButton( 'offering', _('Make offering to the Council for free - Vizier'), 'offeringAction'  );
                        }
                        this.addActionButton( 'endturn', _('End turn'), 'endTurn'  );
                    break;

                    case 'client_selectSpecialist' :
                        this.addActionButton( 'cancel', _('Cancel'), 'cancelClientState'  );
                    break;

                    case 'kingsFuneralBidding': 
                        for(var i=0;i<(parseInt(args[this.player_id])+1);i++) {
                            this.addActionButton( 'bid'+i, i, dojo.partial(this.makeBid, i)  );
                        }
                    break;
                    
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        
        permute: function (inputArr) {
            var results = [];

            function permute(arr, memo) {
                var cur, memo = memo || [];

                for (var i = 0; i < arr.length; i++) {
                cur = arr.splice(i, 1);
                if (arr.length === 0) {
                    results.push(memo.concat(cur));
                }
                permute(arr.slice(), memo.concat(cur));
                arr.splice(i, 0, cur[0]);
                }

                return results;
            }

            return permute(inputArr);
        },

        arraysEqual: function (a, b) {
            if (a === b) return true;
            if (a == null || b == null) return false;
            if (a.length != b.length) return false;
          
            for (var i = 0; i < a.length; ++i) {
              if (a[i] !== b[i]) return false;
            }
            return true;
          },

        resizeViewportSize : function() {  // resize all alements within given nodes
            var nodesToResize = new Array('main_board','player_cards');
            var previousRatio = this.sizeRatio;
            var wraps = dojo.query('.playerboardwrap');
            for(var i=0;i<wraps.length;i++) {
                nodesToResize.push(wraps[i].id);
            }

            if (dojo.window.getBox().w < 990) {
                var mBratio = 1;
                var c = dojo.query('#handcontainer')[0];
                dojo.place(c, 'main_board', 'after');
            } else {
                var mBratio =  this.mainBoardRatio;
            }

            this.sizeRatio = adaptViewportSize(nodesToResize, this.mainBoardSize, mBratio, this.sizeRatio);

            var texts = dojo.query('.specialistdiscount');
            texts.push(dojo.query('.masterroomtoggler')[0]);
            texts.push(dojo.query('.showtreasure')[0]);
            // texts.push(dojo.query('.showtreasure')[1]);
            for (var i=0;i<texts.length;i++) {
                this.recalculateFontSize(texts[i].id);
            }

            if ($('card_menu0') || $('card_menu1')) {
                var menu = $('card_menu0') ? 'card_menu0': 'card_menu1';
                var elements = dojo.query('.card_menu div');
                resizeChildNode(menu, this.sizeRatio, previousRatio);
                for(var i =0;i< elements.length;i++) {
                    resizeChildNode(elements[i].id, this.sizeRatio, previousRatio);
                }
            }
        },

        orderTreasureCards: function(set) {
            var keys = Object.keys(set);
            var result = [];
            var arr = [];
            dojo.forEach(keys, dojo.hitch(this, function(value, ke) {
                arr.push(  [parseInt(set[value]['id']),parseInt(set[value]['location_arg'])] );
            }) );

            arr.sort(function(a, b){return b[1] - a[1]});
            for(var i=0;i<arr.length;i++) {
                result.push(arr[i][0]);
            }

            return result;
        },

        sortByKey: function(array, key, reverse) {
            return array.sort(function(a, b) {
                var x = a[key]; var y = b[key];
                var result = ((x < y) ? -1 : ((x > y) ? 1 : 0));

                if (reverse) {
                    return result*-1;
                } else {
                    return result;
                }
            });
        },

        onLoadFunction: function() {
            if ($('card_menu0')) {
                this.placeOnObjectPos('card_menu0', 'player_cards', 0, -2*dojo.style('tile_card_0', 'height')+10);
                // this.placeOnObjectPos('card_menu0', 'player_cards', 0, dojo.style('tile_card_0', 'height')+10);
                this.toggleCards(true, false);
            }
            if ($('card_menu1')) {
                this.placeOnObjectPos('card_menu1', 'player_cards', 0, -2*dojo.style('tile_card_0', 'height')+10);
                // this.placeOnObjectPos('card_menu1', 'player_cards', 0, dojo.style('tile_card_0', 'height')+10);
                this.toggleCards(true, false);
            }
        },

        recalculateFontSize: function(id) {
            var w = dojo.style(id, 'height');
            dojo.style(id, 'font-size', '' );
            var fs = parseInt(dojo.style(id, 'font-size'));
            dojo.style(id, 'font-size', fs*this.sizeRatio+'px' );

            if (id != 'showcompquests' && id != 'showtreasure' ) {
                dojo.style(id, 'line-height', (w+1)+'px' );
            }

        },

        calculateFont: function(node) {         //recalculates font size - different languages needs different size
            var element = dojo.byId(node);
            var run = true;
            while (run == true) {
                if (element.scrollWidth > element.clientWidth) {
                    var actFontSize = dojo.style(element, "font-size" );
                    dojo.style(element, "font-size", parseInt(actFontSize.slice(0,-2))-1+"px" );
                } else {
                    run = false;
                }
            }
        },

        changeItemSize: function(nodeid, width, height) {
            dojo.style(nodeid,'margin','');
            dojo.style(nodeid,'background-position', '');
            var actBx = parseFloat(dojo.style(nodeid,'background-position-x'));
            var actBy = parseFloat(dojo.style(nodeid,'background-position-y'));

            dojo.style(nodeid,'height',height+'px'); 
            dojo.style(nodeid,'width',width+'px'); 
            // var coefx = width/276; // width/296;
            // var coefy = height/400; //420
            // dojo.style(nodeid,'background-position', actBx*coefx+'px '+actBy*coefy+'px');
            var coef =  height/400;
            dojo.style(nodeid,'background-position', actBx*coef+'px '+actBy*coef+'px');
        },

        countSpecialistInDeck: function() {
            var A = dojo.query('#tile_specialist_5 .bg-BackA').length;
            var B = dojo.query('#tile_specialist_5 .bg-BackB').length;
            var C = dojo.query('#tile_specialist_5 .bg-BackC').length;
            if (C > 0) {
                var last =  dojo.query('#tile_specialist_5 .specialist:last-child')[0].id;
            } else {
                var last = null;
            }

            return {"A": A, "B": B, "C": C, "last": last};
        },

        makeTooltipForDeck: function(deckType) {
            if (deckType == 'specialist') {
                var speccount = this.countSpecialistInDeck();
                var txt = _('Deck of specialists');
                var txt1 = '<p>'+_('Number of A specialist left: ')+speccount.A+'</p>';
                var txt2 = '<p>'+_('Number of B specialist left: ')+speccount.B+'</p>';
                var txt3 = '<p>'+_('Number of C specialist left: ')+speccount.C+'</p>';
                if (speccount.last != null) {
                    this.addTooltip(speccount.last, txt+txt1+txt2+txt3, '');
                }
            }

            if (deckType == 'quest') {
                if (Object.keys(this.gamedatas.players).length > 4 || Object.keys(this.gamedatas.players).length == 1) {
                    var questcount1 = dojo.query('#tile_quest_6 .bg-QuestBack1N').length + dojo.query('#tile_quest_6 .bg-QuestBack1S').length;
                    var questcount2 = dojo.query('#tile_quest_6 .bg-QuestBack2').length;
                    if (questcount2 > 0) {
                        var id = dojo.query('#tile_quest_6 .quest:last-child')[0].id;
                    }
                } else {
                    var questcount1 = dojo.query('#tile_quest_5 .bg-QuestBack1N').length + dojo.query('#tile_quest_5 .bg-QuestBack1S').length;
                    var questcount2 = dojo.query('#tile_quest_5 .bg-QuestBack2').length;
                    if (questcount2 > 0) {
                        var id = dojo.query('#tile_quest_5 .quest:last-child')[0].id;
                    }
                }
                var txt = _('Deck of quests');
                var txt1 = '<p>'+_('Age I quests left: ')+questcount1+'</p>';
                var txt2 = '<p>'+_('Age II quests left: ')+questcount2+'</p>';
                if (id != 'undefined') {
                    this.addTooltip( id , txt+txt1+txt2, '');
                }
            }
        },

        showDiscardPile: function(evt) {
            dojo.stopEvent(evt);

            this.myDlg = new ebg.popindialog();
            this.myDlg.create( 'discardpilewindow' );
            this.myDlg.setTitle( _("Discard pile") );
            // this.myDlg.setMaxWidth( 500 ); // Optional
            html = ''; 
            var discards = dojo.query('#discard .treasure:last-child');

            for(var i =0;i<discards.length;i++) {
                var clone = dojo.clone(discards[i]);
                dojo.setAttr(clone, "id", 'clone'+i);
                dojo.setStyle(clone, "position", 'relative');
                dojo.setStyle(clone, "display", 'inherit');

                html += clone.outerHTML;
                dojo.destroy(clone);
            }

            var wrap = '<div class="discard_wrap">'+html+'</div>';
            // Show the dialog
            this.myDlg.setContent( wrap ); 
            this.myDlg.show();
        },

        showCompletedQuests: function(evt) {
            dojo.stopEvent(evt);

            this.myDlg = new ebg.popindialog();
            this.myDlg.create( 'completedquests' );
            this.myDlg.setTitle( _("My completed quests") );

            var quests = [];
            for (var key in this.gamedatas.quest) {
                if (this.gamedatas.quest[key].location == this.player_id) {
                            quests.push( this.gamedatas.quest[key].name );
                }
            }

            html = ''; 
            for(var i =0;i<quests.length;i++) {
                html += this.format_block('jstpl_questbackonly', {
                    id: 'sh'+i,
                    cssBackType: 'bg-'+quests[i].replace(/ /g, '').replace(/'/g, ''),
                } );
            }

            var wrap = '<div class="discard_wrap">'+html+'</div>';
            // Show the dialog
            this.myDlg.setContent( wrap ); 
            this.myDlg.show();

            for(var i =0;i<quests.length;i++) {
                dojo.setAttr('questback_sh'+i, 'style', 'position: relative;');
                dojo.setStyle('questback_sh'+i, 'display', 'inline-block');
                adjustBackgroundPosition('questback_sh'+i, this.questSizeCoef);
            }
        },

        addCardTileOnBoard: function(id, width, height) {
                dojo.place( this.format_block( 'jstpl_player_cardtile', {
                    id: id,
                    width: width,
                    height: height,
                } ) , 'player_cards');
        },

        addResourceOnBoard: function(id, resType, location, position) {
            if (location == 'board') {
                var position = getPositionForResource(resType,this.sizeRatio);
                dojo.place( this.format_block( 'jstpl_resource', {
                    id: id,
                    left: position[0],
                    top: position[1],
                    type: resType
                } ) , getTileNumberFromResource(resType) );
            } else {
                dojo.place( this.format_block( 'jstpl_resource', {
                    id: id,
                    left: 0,
                    top: 0,
                    type: resType
                } ) , 'tile_storage_'+position+'_'+location );

                this.placeOnObject( 'resource_'+id,  'tile_storage_'+position+'_'+location);
                // player panel
                var item_id = location+'_resCounter_'+resType;
                $(item_id).innerText =  parseInt($(item_id).innerText) +1;
            }
        },

        addSigilOnBoard: function(id, player, location, position) {
            if (location == 'free') {
                dojo.place( this.format_block( 'jstpl_sigil', {
                    id: id,
                    player: player,
                    size: 'small',
                    guild: this.gamedatas.players[player].guildname.replace(/ /g, '')
                } ) , player+'_sigilplace');
            } else if (position != '' ) {
                dojo.place( this.format_block( 'jstpl_sigil', {
                    id: id,
                    player: player,
                    size: '',
                    guild: this.gamedatas.players[player].guildname.replace(/ /g, '')
                } ) , 'tile_'+location+'_'+position );

                this.placeOnObject( 'sigil_'+player+'_'+id,  'tile_'+location+'_'+position);
            } else {
                dojo.place( this.format_block( 'jstpl_sigil', {
                    id: id,
                    player: player,
                    size: '',
                    guild: this.gamedatas.players[player].guildname.replace(/ /g, '')
                } ) , location );

                this.placeOnObject( 'sigil_'+player+'_'+id,  location);

                // sigilhere
                var sigilCount = dojo.query('#'+location+' .sigil').length;

                dojo.addClass('sigil_'+player+'_'+id, 'sigil'+sigilCount);
            }
        },

        addThugOnBoard: function(location, position) {
            var destination = location+'_'+position;
            dojo.place( this.format_block( 'jstpl_thug', {
            } ) , destination);

            var h = dojo.style(destination, 'height');
            resizeNode('thugicon', this.sizeRatio,1);
            this.placeOnObjectPos( 'thugicon',  destination, 0,-h/6);
        },

        getThugId: function() {                                                 //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            var specialistkeys = Object.keys(this.gamedatas.specialist);
            for(var specialist_id = specialistkeys[0]; specialist_id<=specialistkeys[specialistkeys.length-1];specialist_id++) {
                if (this.gamedatas.specialist[specialist_id].type == "11" ) {
                    return this.gamedatas.specialist[specialist_id].id;
                }
            }
        },

        addRoomOnBoard: function(name, id, location, position, cathegory, size, dualside, dualname, dualid, ability) {
            if (location == '0') {
                return;
            }

            if (location == 'board') {
                var destination = "tile_"+cathegory+position;
            } else {
                if (location == 'overall_player_board_') {
                    var destination = 'overall_player_board_'+position;
                } else {
                    // var destination = size == 'singleroom' ? "tile_player_room_"+position+"_"+location : "doubletile_player_room_"+position+"_"+location;
                    var destination = size == 'singleroom' ? "tile_room_"+position+"_"+location : "doubletile_room_"+position+"_"+location;
                }
            }

            if (!dualside) {
                dojo.place( this.format_block( 'jstpl_room', {
                    id: id,
                    cssType: 'bg-'+name,
                    size: size,
                } ) , destination);

                // adjustBackgroundPosition('room_'+name+'_'+id, this.roomSizeCoef);
                if (size == 'singleroom') {
                    adjustBackgroundPosition('room_'+id, this.roomSizeCoef);
                } else {
                    adjustBackgroundPosition('room_'+id, this.roomSizeCoefDual);
                }
                var numberInDeck = location == 'board' ? dojo.query('#'+destination+' .room').length : null;
                this.constructTooltip('room_'+id, _(this.gamedatas.rooms[id]['nameTr']), this.gamedatas.rooms[id]['text'], numberInDeck, null);                             

                if (location != 'board') {
                    // this.enlargeItem('room_'+name+'_'+id, size, size == 'singleroom' ? this.roomSizeCoefEnlarged : this.roomSizeCoefEnlargedDual);
                    this.enlargeItem('room_'+id, size, size == 'singleroom' ? this.roomSizeCoefEnlarged : this.roomSizeCoefEnlargedDual);
                }

                // this.placeOnObject( 'room_'+name+'_'+id,  destination);
                this.placeOnObject( 'room_'+id,  destination);

                if (location == 'board') {
                    // dojo.style('room_'+name+'_'+id,'margin-top', (this.specifyPositionInDeck( destination))*2+'px');
                    dojo.style('room_'+id,'margin-top', (this.specifyPositionInDeck( destination))*2+'px');
                } else {
                    if (ability) {
                        if ( Object.keys(ability)[0] == 'tile' ) {
                            // this.addRoomTilesOnBoard('room_'+name+'_'+id, location, ability['tile'][0], ability['tile'][1]);
                            this.addRoomTilesOnBoard('room_'+id, location, position, ability['tile'][0], ability['tile'][1]);
                        }
                     }
                }
            } else {
                dojo.place( this.format_block( 'jstpl_room_dual', {
                    id: id+'_'+dualid,
                    id_front: id,
                    id_back: dualid,
                    cssType: 'bg-'+name,
                    cssBackType: 'bg-'+dualname,
                    // type_front: name,
                    // type_back: dualname,
                    size: size,
                } ) , destination );

                // adjustBackgroundPosition('room_'+name+'_'+id, this.roomSizeCoef);
                // adjustBackgroundPosition('room_'+dualname+'_'+dualid, this.roomSizeCoef);
                var numberInDeck = location == 'board' ? dojo.query('#'+destination+' .roomcontainer').length: null;
                if (size == 'singleroom') {
                    adjustBackgroundPosition('room_'+id, this.roomSizeCoef);
                    adjustBackgroundPosition('room_'+dualid, this.roomSizeCoef);
                } else {
                    adjustBackgroundPosition('room_'+id, this.roomSizeCoefDual);
                    adjustBackgroundPosition('room_'+dualid, this.roomSizeCoefDual);
                }
                this.constructTooltip('room_'+id, _(this.gamedatas.rooms[id]['nameTr']), this.gamedatas.rooms[id]['text'], numberInDeck, null);
                this.constructTooltip('room_'+dualid, _(this.gamedatas.rooms[dualid]['nameTr']), this.gamedatas.rooms[dualid]['text'], numberInDeck, null);

                if (location != 'board') {
                    this.enlargeItem('room_'+id+'_'+dualid, size, size == 'singleroom' ? this.roomSizeCoefEnlarged : this.roomSizeCoefEnlargedDual);
                }

                this.placeOnObject( 'room_'+id+'_'+dualid,  destination);

                if (location == 'board') {
                    // dojo.connect($("room_"+id+'_'+dualid+"_flipA"), 'click', this, 'rotateRoom');
                    // dojo.connect($("room_"+id+'_'+dualid+"_flipB"), 'click', this, 'rotateRoom');

                    dojo.connect($("room_"+id+"_flip"), 'click', this, 'rotateRoom');
                    dojo.connect($("room_"+dualid+"_flip"), 'click', this, 'rotateRoom');
                } else {
                    // dojo.destroy("room_"+id+'_'+dualid+"_flipA");
                    // dojo.destroy("room_"+id+'_'+dualid+"_flipB");

                    dojo.destroy("room_"+id+"_flip");
                    dojo.destroy("room_"+dualid+"_flip");
                }

                if (location == 'board') {
                    dojo.style("room_"+id+'_'+dualid,'margin-top', (this.specifyPositionInDeck( destination))*2+'px');
                } else {
                    if (ability != null) {
                        if ( Object.keys(ability)[0] == 'tile' ) {
                            // this.addRoomTilesOnBoard('room_'+name+'_'+id, location, ability['tile'][0], ability['tile'][1]);
                            this.addRoomTilesOnBoard('room_'+id, location, position, ability['tile'][0], ability['tile'][1]);
                        }
                    }
                }
            }
        },

        addRoomTilesOnBoard: function(parent_id, player_id, tile_id, tiletype, number) {
            var baseW = dojo.style(parent_id, 'width');
            var baseH = dojo.style(parent_id, 'height');
            if ( dojo.hasClass(parent_id, 'room') ||  dojo.hasClass(parent_id, 'roomcontainer') ) {
                var roomsize = dojo.hasClass(parent_id, 'doubleroomenlarged')  ? 'doubleroom': 'singleroom';
                var size= getPercentageTileSize(roomsize,number, tiletype, baseW, baseH );
            } else if (dojo.hasClass(parent_id, 'specialistcontainer') || dojo.hasClass(parent_id, 'specialist') ) {
                var size= getPercentageTileSize('specialist',number, tiletype, baseW, baseH );
            } else {

                var itemsize = Object.keys(this.gamedatas.quest[tile_id].items).length > 1 ? 'doubleitem' : 'singleitem';
                var size= getPercentageTileSize(itemsize,number, tiletype, baseW, baseH );                      
            }

            if (dojo.hasClass(parent_id, 'roomcontainer')) {
                parent_id = dojo.query('#'+parent_id)[0].parentNode.id;
            }

            if (tiletype == 'storage') {
                var nextId = dojo.query('.storage'+player_id).length;
                for (var i=nextId;i<number+nextId;i++) {
                    dojo.place( this.format_block( 'jstpl_storagetile', {
                        player_id: player_id,
                        id: i,
                        width: size['w'],
                        height: size['h'],
                        left: size['left'][i-nextId],
                        top: size['top'][i-nextId],
                    } ) , parent_id );
                }
            }

            if (tiletype == 'specialist') {
                for (var i=0;i<number;i++) {
                    var id = roomsize == 'singleroom' ? tile_id : tile_id+i;
                    dojo.place( this.format_block( 'jstpl_specialisttile', {
                        player_id: player_id,
                        id: id,
                        width: size['w'],
                        height: size['h'],
                        left: size['left'][i],
                        top: size['top'][i],
                    } ) , parent_id );
                }
            }

            if (tiletype == 'item') {
                for (var i=0;i<number;i++) {
                    dojo.place( this.format_block( 'jstpl_questtile', {
                        id: tile_id+'_'+i,
                        width: size['w'],
                        height: size['h'],
                        left: size['left'][i],
                        top: size['top'][i],
                    } ) , parent_id );

                    var id = parent_id.split("_")[1];
                    this.constructTooltip( 'tile_quest_'+tile_id+'_'+i, this.gamedatas.quest[id].nameTr, this.gamedatas.quest[id].text, null);
                }
            }
        },

        rotateRoom: function(evt) {
            var target = evt.target || evt.srcElement;
            // var id = target.id.slice(0,-6);

            var room_id = dojo.query('#'+target.id)[0].parentNode.id;
            var container_id = dojo.query('#'+room_id)[0].parentNode.id;

            dojo.toggleClass(container_id,"flipped");
        },

        // addSpecialistOnBoard: function(id, name, back, location, position, visible, side, ability = null, discount = 0) {
        addSpecialistOnBoard: function(id, name, back, location, position, visible, side, ability, discount) {
            // var tile = location == 'board' ? 'tile_specialist_'+position : 'tile_player_specialist_'+position+'_'+location;
            var tile = location == 'board' ? 'tile_specialist_'+position : 'tile_specialist_'+position+'_'+location;

            if (name != 'baggage') {
                if (visible == 0) {
                    dojo.place( this.format_block( 'jstpl_specialistbackonly', {
                        cssBackType: 'bg-Back'+back,
                        id: id,
                    } ) , tile );

                    adjustBackgroundPosition('specialistback_'+id, this.specialistSizeCoef);
                    
                    this.placeOnObject( 'specialistback_'+id, tile);
                    dojo.style('specialistback_'+id,'margin-top', this.specifyPositionInDeck(tile)*0.5+'px');
                } else {
                    dojo.place( this.format_block( 'jstpl_specialist', {
                        id: id,
                        cssType: 'bg-'+name,
                        cssBackType: 'bg-Back'+back,
                        type: name,
                        side: side == 0 ? 'flipped' : '',
                    } ) , tile );

                    adjustBackgroundPosition('specialist_'+id+'_front', this.specialistSizeCoef);
                    adjustBackgroundPosition('specialist_'+id+'_back', this.specialistSizeCoef);

                    if (location == 'board') {
                        dojo.style('specialist_'+id,'margin-top', this.specifyPositionInDeck(tile)*0.5+'px');
                        if (discount != '0' ) {
                            dojo.place( this.format_block( 'jstpl_specialistdiscount', {
                                id: id,
                                value: discount,
                            } ) , 'specialist_'+id );
                            this.placeOnObject( 'specialistdiscount_'+id, 'specialist_'+id);
                            this.recalculateFontSize( 'specialistdiscount_'+id);
                        }
                        this.constructTooltip('specialist_'+id+'_front', _(this.gamedatas.specialist[id]['nameTr']), this.gamedatas.specialist[id]['text'],discount); 

                    } else {
                        this.enlargeItem('specialist_'+id, 'specialist', this.specialistSizeCoefEnlarged);
                        if (ability != null) {
                            if ( Object.keys(ability)[0] == 'tile' ) {
                                this.addRoomTilesOnBoard('specialist_'+id, location, position, ability['tile'][0], ability['tile'][1]);
                            }
                        }
                        this.placeOnObject( 'specialist_'+id, tile);
                        this.constructTooltip('specialist_'+id+'_front', _(this.gamedatas.specialist[id]['nameTr']), this.gamedatas.specialist[id]['text'], null); 

                        if (location == this.player_id && this.gamedatas.specialist[id]['name'] == 'Auctioneer') {
                            this.playerHasAuctioneer = true;
                        }
                    }
                    resizeNode('specialist_'+id, this.sizeRatio,1);
                }
            } else {
                tile = position == null ? 'overall_player_board_'+location : 'tile_specialist_'+position+'_'+location;
                dojo.place( this.format_block( 'jstpl_baggage', {
                    id: id,
                } ) , tile );
                resizeNode('specialist_'+id, this.sizeRatio,1);
                this.placeOnObject( 'specialist_'+id, tile);
            }
        },

        
        addQuestOnBoard: function(id, name, back, location, position, visible, side) {
            if ( location == 'board' ) {
                var tile = 'tile_quest_'+position;
                if (visible == 0) {
                    dojo.place( this.format_block( 'jstpl_questbackonly', {
                        cssBackType: 'bg-QuestBack'+back,
                        id: id,
                    } ) , tile );

                    adjustBackgroundPosition('questback_'+id, this.questSizeCoef);
                    
                    this.placeOnObject( 'questback_'+id, tile);
                    dojo.style('questback_'+id,'margin-top', this.specifyPositionInDeck(tile)*0.5+'px');
                } else {
                    dojo.place( this.format_block( 'jstpl_quest', {
                        id: id,
                        cssType: 'bg-'+name,
                        cssBackType: 'bg-QuestBack'+back,
                        type: name,
                        side: side == 0 ? 'flipped' : '',
                    } ) , tile );

                    adjustBackgroundPosition('quest_'+id+'_front', this.questSizeCoef);
                    adjustBackgroundPosition('quest_'+id+'_back', this.questSizeCoef);

                    // not for Kings Funeral and Offering
                    if (name != 'TheKingsFuneral' && name != 'OfferingtotheCouncil' ) {
                        this.addRoomTilesOnBoard('quest_'+id, null, id, 'item', Object.keys(this.gamedatas.quest[id].items).length);
                    }

                    this.constructTooltip('quest_'+id+'_front', this.gamedatas.quest[id].nameTr, this.gamedatas.quest[id].text, null);
                    
                    resizeNode('quest_'+id, this.sizeRatio,1);
                    this.placeOnObject( 'quest_'+id, tile);
                }
            } else if (location == 'removed') {

            } else {
                // player panel
                if (location != 'discard') {
                    var hero_id = location+'_questCounter_'+this.gamedatas.quest[id].hero[0];
                    $(hero_id).innerText =  parseInt($(hero_id).innerText) +1;

                    for (var i=0;i<Object.keys(this.gamedatas.quest[id].items).length; i++) {
                        var item_id = location+'_questCounter_'+this.gamedatas.quest[id].items[i+1][1].toLowerCase();
                        $(item_id).innerText =  parseInt($(item_id).innerText) +1;
                    }
                }
            }
        },

        addTreasureOnBoard: function(id, name, back, location, position, visible) {
            if ( visible == 0) {
                if (location == 'overall_player_board') {
                    var tile = 'overall_player_board_'+position;
                } else {
                    if (back == 'blue') { var deck = 0;}
                    if (back == 'red') { var deck = 1;}
                    if (back == 'yellow') { var deck = 2;}
                    var tile = 'tile_treasure_'+deck; 
                //    var tile = 'tile_treasure_'+position; 
                }
                
                dojo.place( this.format_block( 'jstpl_treasurebackonly', {
                    id: id,
                    cssBackType: 'bg-CardBack'+back,
                } ) , tile );

                adjustBackgroundPosition('treasureback_'+id, this.treasureSizeCoef);
                resizeChildNode('treasureback_'+id, this.sizeRatio,1);

                this.placeOnObject( 'treasureback_'+id, tile);
                dojo.style('treasureback_'+id,'margin-top', this.specifyPositionInDeck(tile)*0.35+'px');

            } else {
                if (location == 'board') {
                    var tile = 'tile_treasure_'+position;
                    dojo.place( this.format_block( 'jstpl_treasure', {
                        id: id,
                        cssType: 'bg-'+name,
                        cssBackType: 'bg-CardBack'+back,
                        side: 'flipped',
                    } ) , tile );

                    adjustBackgroundPosition('treasure_'+id+'_front', this.treasureSizeCoef);
                    adjustBackgroundPosition('treasure_'+id+'_back', this.treasureSizeCoef);
                    resizeNode('treasure_'+id, this.sizeRatio,1);

                    this.placeOnObject( 'treasure_'+id, tile);
                    dojo.style('treasure_'+id,'margin-top', this.specifyPositionInDeck(tile)*0.35+'px');
                } else if (location.split("_")[0] == 'overall') {
                        var tile = location;
                        dojo.place( this.format_block( 'jstpl_treasure', {
                            id: id,
                            cssType: 'bg-'+name,
                            cssBackType: 'bg-CardBack'+back,
                            side: 'flipped',
                        } ) , tile );

                        adjustBackgroundPosition('treasure_'+id+'_front', this.treasureSizeCoef);
                        adjustBackgroundPosition('treasure_'+id+'_back', this.treasureSizeCoef);
                        resizeNode('treasure_'+id, this.sizeRatio,1);

                        this.placeOnObject( 'treasure_'+id, tile);
                } else if (location == 'discard') {
                        dojo.place( this.format_block( 'jstpl_treasure', {
                            id: id,
                            cssType: 'bg-'+name,
                            cssBackType: 'bg-CardBack'+back,
                            side: '',
                        } ) , location );

                        adjustBackgroundPosition('treasure_'+id+'_front', this.treasureSizeCoef);
                        adjustBackgroundPosition('treasure_'+id+'_back', this.treasureSizeCoef);
                        resizeNode('treasure_'+id, this.sizeRatio,1);

                        if (position > 19 && this.isCurrentPlayerActive()) {
                            this.attachToNewParent('treasure_'+id, 'tile_card_'+position);

                            this.enlargeTreasure(id);
                            this.placeOnObject( 'treasure_'+id, 'tile_card_'+position);
                        } else {
                            this.placeOnObject( 'treasure_'+id, location);
                        }
                        this.constructTooltipTreasure(id, true, false);

                } else {
                    if (location == this.player_id) {
                        dojo.place( this.format_block( 'jstpl_treasure', {
                            id: id,
                            cssType: 'bg-'+name,
                            cssBackType: 'bg-CardBack'+back,
                            side: '',
                        } ) , 'tile_card_'+position );

                        this.enlargeTreasure(id);

                        this.placeOnObject( 'treasure_'+id, 'tile_card_'+position);

                        $(location+'_cards'+back).innerText = parseInt($(location+'_cards'+back).innerText) +1;

                        this.constructTooltipTreasure(id, false, false);
                    } else {
                        // player panel (number)
                        $(location+'_cards'+back).innerText = parseInt($(location+'_cards'+back).innerText) +1;
                    }
                }
            }

        },

        drawAndMoveCard: function(card_type, card_id, card_name, card_back, location_from, location_to ) {
            dojo.destroy(card_type+'back_'+card_id);
            var functionToCall = 'add'+card_type.charAt(0).toUpperCase()+card_type.slice(1)+'OnBoard';
            var func = dojo.hitch(this,functionToCall, card_id, card_name, card_back, 'board', location_from,1, 0, null,0 ); // ability and discount added !!!
            func();
            dojo.removeClass(card_type+'_'+card_id, 'flipped');

            // this.attachToNewParent( card_type+'_'+card_id, 'tile_'+card_type+'_'+location_to);                                       

            // exclude Kings Funeral and Offering
            if (location_to != null && ( card_name != 'TheKingsFuneral' || this.gamedatas.playerorder.length == 1) && card_name != 'OfferingtotheCouncil' ) {
                var anim = this.slideToObject( card_type+'_'+card_id, 'tile_'+card_type+'_'+location_to, 700,800 );
                dojo.connect(anim, 'onEnd', dojo.hitch(this, function() {
                    //update tooltip
                    this.attachToNewParent( card_type+'_'+card_id, 'tile_'+card_type+'_'+location_to); 
                    if (card_type == 'specialist') {
                        this.constructTooltip(card_type+'_'+card_id+'_front', _(this.gamedatas.specialist[card_id]['nameTr']), this.gamedatas.specialist[card_id]['text'],0);  
                    } else {
                        this.constructTooltip(card_type+'_'+card_id+'_front', _(this.gamedatas.quest[card_id]['nameTr']), this.gamedatas.quest[card_id]['text'], null); 
                        for (var index = 0; index <  this.gamedatas.quest[card_id]['reward'].length; index++) {
                            this.constructTooltip('tile_quest_'+card_id+'_'+index, _(this.gamedatas.quest[card_id]['nameTr']), this.gamedatas.quest[card_id]['text'], null);
                        }
                    }
                    this.makeTooltipForDeck(card_type );
                } ));
                anim.play();
            }
        },

        drawAndMoveCardToPlayerHand: function(card_id, card_name, card_back, location_from, location_to ) {
            dojo.destroy('treasureback_'+card_id);

            if ( dojo.hasClass('handcontainer', 'hidden') ) {
                this.toggleCards(false, false);
            }

            if ( String(location_from).split("_")[0] == 'overall'){
                this.addTreasureOnBoard(card_id,  card_name.replace(/ /g, ''), card_back, location_from, location_from,1);
            } else {
                this.addTreasureOnBoard(card_id,  card_name.replace(/ /g, ''), card_back,'board', location_from,1);
            }
            // this.attachToNewParent( 'treasure_'+card_id, 'tile_card_'+location_to);
            this.enlargeItem('treasure_'+card_id, 'treasure', this.treasureSizeCoefEnlarged);                          
            this.changeItemSize('treasure_'+card_id, dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height'));
            this.changeItemSize('treasure_'+card_id+'_front', dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height'));
            this.changeItemSize('treasure_'+card_id+'_back', dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height'));
            dojo.removeClass('treasure_'+card_id, 'flipped');


            if (location_to != null) {
                if (dojo.hasClass('handcontainer', 'hidden')) {
                    var anim = this.slideToObject('treasure_'+card_id, 'overall_player_board_'+this.player_id, 500,400 );
                } else {
                    var anim = this.slideToObject('treasure_'+card_id, 'tile_card_'+location_to, 500,400 );
                }
                dojo.connect(anim, 'onEnd', dojo.hitch(this, function() {
                    this.attachToNewParent( 'treasure_'+card_id, 'tile_card_'+location_to);
                    this.placeOnObject( 'treasure_'+card_id, 'tile_card_'+location_to);
                    this.constructTooltipTreasure(card_id, false, false);
                } ) );
                anim.play();
            }
        },

        moveCardAndDestroy: function(card_id, card_type, location_to ) {
            if (card_type == 'quest') {
                this.attachToNewParent( 'quest_'+card_id, location_to);
                this.slideToObjectAndDestroy( 'quest_'+card_id, location_to, 1000, 500 );
            } else if (card_type == 'treasure') {
                if($('treasure_'+card_id)) {
                    this.slideToObjectAndDestroy( 'treasure_'+card_id, location_to, 1000, 500 );
                } else {
                    this.slideToObjectAndDestroy( 'treasureback_'+card_id, location_to, 1000, 500 );
                }
            } else {
                this.attachToNewParent( card_type+'back_'+card_id, location_to);
                this.slideToObjectAndDestroy( card_type+'back_'+card_id, location_to, 1000, 500 );
            }
        },

        moveItemOnBoard: function(item_id, location_to, destroy) {   
            this.attachToNewParent(item_id, location_to);
            if (destroy) {
                this.slideToObjectAndDestroy( item_id, location_to, 1000 );
            } else {
                if (item_id == 'thugicon') {
                    var h = dojo.style(location_to, 'height');
                    var w = dojo.style(location_to, 'width');
                    this.slideToObjectPos( item_id, location_to,w/4, h/6 ,1000 ).play();
                } else {
                    this.slideToObject( item_id, location_to, 1000 ).play();
                }
            }
        },

        moveSpecialistToPlayer: function(id, destination, evt) {                                                        // discount movement!!!!!!!!!!!!!!!!!!
            if (evt) { dojo.stopEvent(evt); }

            if ( $(id)) {
                if ( this.gamedatas.gamestate.args == null || !this.gamedatas.gamestate.args.bard ) { // exclude movement of specialist inside player's guild (bard action)
                    var chld = dojo.query('#'+id+' * ').removeClass('specialistbase').addClass('specialistenlarged');
                    dojo.removeClass(id,'specialistbase');
                    dojo.addClass(id,'specialistenlarged');

                    for (var i=0;i<chld.length;i++) {
                        dojo.style(chld[i].id,'width','');
                        dojo.style(chld[i].id,'height','');
                        dojo.style(chld[i].id,'background-position','');

                        bX = parseFloat(dojo.style(chld[i].id,'background-position-x'))*this.specialistSizeCoefEnlarged;
                        bY = parseFloat(dojo.style(chld[i].id,'background-position-y'))*this.specialistSizeCoefEnlarged ;
                        if ( chld[i].id != 'specialistdiscount_'+id.split("_")[1]) {
                            dojo.style(chld[i].id,'background-position', bX+'px '+bY+'px');
                        } else {
                            dojo.query('#'+chld[i].id).removeClass('specialistenlarged');
                        }
                    }
                    resizeNode(id, this.sizeRatio,1);
                    if ($('specialistdiscount_'+id.split("_")[1])) {
                        this.placeOnObject('specialistdiscount_'+id.split("_")[1], id);
                    }
                }
            } else {
                this.addSpecialistOnBoard(id.split("_")[1], this.gamedatas.specialist[id.split("_")[1]].cathegory, null, destination.split("_")[4],  null, null, null, null, 0); // baggage
            }

            this.specialistPosition = destination.split("_")[2]+'_'+destination.split("_")[3];
            var anim = this.slideToObject( id, destination ); 
            dojo.connect(anim, 'onEnd', dojo.hitch(this, 'attachToNewParentOnEnd', id, destination ));
            anim.play();
        },

        moveRoomToPlayer: function(id, destination, evt) {
            if (evt) {
                dojo.stopEvent(evt);
                var target = evt.target || evt.srcElement;

                if (target.id.slice(-5,-1) == 'flip') {
                    return;
                }
            }

            var room = this.gamedatas.rooms[id.split("_")[1]]
            if ( ! $(id) ) {          // shrine room not yet placed
                this.addRoomOnBoard(room.name.replace(/ /g, '').replace(/'/g, ''), id.split("_")[1], 'overall_player_board_', destination.split("_")[4], room.cathegory, 'singleroom', false, false, false, room.ability);
            }

            if ( room.two_sided != false ) {                    // dualside room, move whole container
                id = dojo.query('#'+id)[0].parentNode.id;
            }

            var chld = [];
            var doubleroom = false;
            if ( !dojo.hasClass(id,'singleroomenlarged') && !dojo.hasClass(id,'doubleroomenlarged') ) {
                if ( dojo.hasClass(id,'singleroom')) {
                    chld = dojo.query('#'+id+' > .room ').removeClass('singleroom').addClass('singleroomenlarged');
                    dojo.removeClass(id,'singleroom');
                    dojo.addClass(id,'singleroomenlarged');
                } else {
                    chld = dojo.query('#'+id+' > .room ').removeClass('doubleroom').addClass('doubleroomenlarged');
                    dojo.removeClass(id,'doubleroom');
                    dojo.addClass(id,'doubleroomenlarged');
                    doubleroom = true;
                }
            }

            dojo.style(id,'margin-top','');
            dojo.style(id,'width','');
            dojo.style(id,'height','');
            dojo.style(id,'background-position','');

            if (doubleroom) {
                bX = parseFloat(dojo.style(id,'background-position-x'))*this.roomSizeCoefEnlargedDual;
                bY = parseFloat(dojo.style(id,'background-position-y'))*this.roomSizeCoefEnlargedDual ;
                dojo.style(id,'background-position', bX+'px '+bY+'px');
            } else {
                bX = parseFloat(dojo.style(id,'background-position-x'))*this.roomSizeCoefEnlarged;
                bY = parseFloat(dojo.style(id,'background-position-y'))*this.roomSizeCoefEnlarged ;
                dojo.style(id,'background-position', bX+'px '+bY+'px');
            }

            for (var i=0;i<chld.length;i++) {
                dojo.style(chld[i].id,'width','');
                dojo.style(chld[i].id,'height','');
                dojo.style(chld[i].id,'background-position','');

                if (doubleroom) {
                    bX = parseFloat(dojo.style(chld[i].id,'background-position-x'))*this.roomSizeCoefEnlargedDual;
                    bY = parseFloat(dojo.style(chld[i].id,'background-position-y'))*this.roomSizeCoefEnlargedDual ;
                    dojo.style(chld[i].id,'background-position', bX+'px '+bY+'px');
                } else {
                    bX = parseFloat(dojo.style(chld[i].id,'background-position-x'))*this.roomSizeCoefEnlarged;
                    bY = parseFloat(dojo.style(chld[i].id,'background-position-y'))*this.roomSizeCoefEnlarged ;
                    dojo.style(chld[i].id,'background-position', bX+'px '+bY+'px');
                }
            }

            if (chld.length >0) {
                //remove rotate icons
                var id1= id.split("_")[1];
                var id2= id.split("_")[2];
                dojo.destroy('room_'+id1+'_flip');
                dojo.destroy('room_'+id2+'_flip');
                resizeNode(id, this.sizeRatio,1);
            } else {
                resizeChildNode(id, this.sizeRatio,1);
            }

            var anim = this.slideToObject( id, destination ); 
            dojo.connect(anim, 'onEnd', dojo.hitch(this, 'attachToNewParentOnEnd', id, destination ));
            dojo.connect(anim, 'onEnd', dojo.hitch(this, function() {
                if (room.ability!= null) {
                    if ( Object.keys(room.ability)[0] == 'tile' ) {
                        this.addRoomTilesOnBoard(id, destination.split("_")[4], destination.split("_")[2]+'_'+destination.split("_")[3], room.ability['tile'][0], room.ability['tile'][1]);
                    }
                }
            }));
            anim.play();
            this.roomPosition = destination.split("_")[2]+'_'+destination.split("_")[3];
        },

        moveItemBack: function(id, destination, coef) {
            var chld = [];
            if ( dojo.hasClass(id,'singleroomenlarged')) {
                chld = dojo.query('#'+id+' > .room ').removeClass('singleroomenlarged').addClass('singleroom');
                dojo.removeClass(id,'singleroomenlarged');
                dojo.addClass(id,'singleroom');
                var param =  dojo.query('#'+destination+' .room').length+1 ;
            } 
            if ( dojo.hasClass(id,'doubleroomenlarged')){
                chld = dojo.query('#'+id+' > .room ').removeClass('doubleroomenlarged').addClass('doubleroom');
                dojo.removeClass(id,'doubleroomenlarged');
                dojo.addClass(id,'doubleroom');
                var param =  dojo.query('#'+destination+' .roomcontainer').length+1 ;
            }
            if ( dojo.hasClass(id,'specialistenlarged')){
                chld = dojo.query('#'+id+' > .specialist ').removeClass('specialistenlarged').addClass('specialistbase');
                dojo.removeClass(id,'specialistenlarged');
                dojo.addClass(id,'specialistbase');
                var param =   this.gamedatas.specialist[id.split("_")[1]]['discount'];
            }


            dojo.style(id,'width','');
            dojo.style(id,'height','');
            dojo.style(id,'background-position','');

            bX = parseFloat(dojo.style(id,'background-position-x'))*coef;
            bY = parseFloat(dojo.style(id,'background-position-y'))*coef;
            dojo.style(id,'background-position', bX+'px '+bY+'px');


            for (var i=0;i<chld.length;i++) {
                dojo.style(chld[i].id,'width','');
                dojo.style(chld[i].id,'height','');
                dojo.style(chld[i].id,'background-position','');

                bX = parseFloat(dojo.style(chld[i].id,'background-position-x'))*coef;
                bY = parseFloat(dojo.style(chld[i].id,'background-position-y'))*coef;
                dojo.style(chld[i].id,'background-position', bX+'px '+bY+'px');
            }

            if (chld.length >0) {
                resizeNode(id, this.sizeRatio,1);
            } else {
                resizeChildNode(id, this.sizeRatio,1);
            }

            if ($('specialistdiscount_'+id.split("_")[1])) {
                dojo.style('specialistdiscount_'+id.split("_")[1],'width','');
                dojo.style('specialistdiscount_'+id.split("_")[1],'height','');
                dojo.style('specialistdiscount_'+id.split("_")[1],'background-position','');
                resizeChildNode('specialistdiscount_'+id.split("_")[1], this.sizeRatio,1);
                this.placeOnObject('specialistdiscount_'+id.split("_")[1], id);
            }

            this.attachToNewParent(id, destination);
            var anim = this.slideToObject( id, destination ); 
            dojo.connect(anim, 'onEnd', dojo.hitch(this,function() {
                    dojo.style(id, 'top','0px'); 
                    dojo.style(id,'margin-top', (this.specifyPositionInDeck( destination))*2+'px'); 
                    dojo.style(id, 'left','0px');} ));
                    if (id.split("_")[0] == 'room') {
                        if (dojo.hasClass(id, 'roomcontainer') ) {
                            var id1 = id.split("_")[1];
                            var id2 = id.split("_")[2];
                            this.constructTooltip('room_'+id1, _(this.gamedatas.rooms[id1]['nameTr']), this.gamedatas.rooms[id1]['text'],param );
                            this.constructTooltip('room_'+id2, _(this.gamedatas.rooms[id2]['nameTr']), this.gamedatas.rooms[id2]['text'],param );
                            //add rotate icons
                            dojo.place( this.format_block( 'jstpl_flip', {
                                id: id1,
                                side: '',
                            } ) , 'room_'+id1);
                            dojo.place( this.format_block( 'jstpl_flip', {
                                id: id2,
                                side: 'back',
                            } ) , 'room_'+id2);
                            resizeNode('room_'+id1+'_flip', this.sizeRatio,1);
                            resizeNode('room_'+id2+'_flip', this.sizeRatio,1);
                            dojo.connect($("room_"+id1+"_flip"), 'click', this, 'rotateRoom');
                            dojo.connect($("room_"+id2+"_flip"), 'click', this, 'rotateRoom');

                        } else {
                            this.constructTooltip(id, _(this.gamedatas.rooms[id.split("_")[1]]['nameTr']), this.gamedatas.rooms[id.split("_")[1]]['text'],param );
                        }
                    } else {
                        this.constructTooltip(id, _(this.gamedatas.specialist[id.split("_")[1]]['nameTr']), this.gamedatas.specialist[id.split("_")[1]]['text'], param );
                    }
            anim.play();
        },

        moveResource: function(id, destination) {
            var anim = this.slideToObject( id, destination, 300 ); 
            dojo.connect(anim, 'onEnd', dojo.hitch(this, 'attachToNewParentOnEnd', id, destination ));
            anim.play();
        },

        enlargeItem: function(id, itemclass, coef) {
            if(itemclass.slice(-4) == 'room') {
                var chld = dojo.query('#'+id+' * ').removeClass(itemclass).addClass(itemclass+'enlarged');
                dojo.removeClass(id,itemclass);
            } else {
                var chld = dojo.query('#'+id+' * ').removeClass(itemclass+'base').addClass(itemclass+'enlarged');
                dojo.removeClass(id,itemclass+'base');
            }
            dojo.addClass(id,itemclass+'enlarged');

            dojo.style(id,'width','');
            dojo.style(id,'height','');
            dojo.style(id,'margin','');
            dojo.style(id,'background-position','');

            bX = parseFloat(dojo.style(id,'background-position-x'))*coef;
            bY = parseFloat(dojo.style(id,'background-position-y'))*coef ;
            dojo.style(id,'background-position', bX+'px '+bY+'px');

            for (var i=0;i<chld.length;i++) {
                dojo.style(chld[i].id,'width','');
                dojo.style(chld[i].id,'height','');
                dojo.style(chld[i].id,'background-position','');

                bX = parseFloat(dojo.style(chld[i].id,'background-position-x'))*coef;
                bY = parseFloat(dojo.style(chld[i].id,'background-position-y'))*coef ;
                dojo.style(chld[i].id,'background-position', bX+'px '+bY+'px');
            }

            if (chld.length >0) {
                resizeNode(id, this.sizeRatio,1);
            } else {
                resizeChildNode(id, this.sizeRatio,1);
            }
        },

        enlargeTreasure: function(id) {
            this.enlargeItem('treasure_'+id, 'treasure', this.treasureSizeCoefEnlarged);
            this.changeItemSize('treasure_'+id, dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height'));
            this.changeItemSize('treasure_'+id+'_front', dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height'));
            this.changeItemSize('treasure_'+id+'_back', dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height'));
        },

        getSmallerTreasureCard: function(id) {
            var all_ids = ['treasure_'+id,'treasure_'+id+'_back', 'treasure_'+id+'_front' ];
            for (var i=0;i<3;i++) {
                dojo.removeClass(all_ids[i],'treasureenlarged');
                dojo.addClass(all_ids[i],'treasurebase');
                dojo.style(all_ids[i],'width','');
                dojo.style(all_ids[i],'height','');
                dojo.style(all_ids[i],'background-position','');
                bX = parseFloat(dojo.style(all_ids[i],'background-position-x'))*this.treasureSizeCoef;
                bY = parseFloat(dojo.style(all_ids[i],'background-position-y'))*this.treasureSizeCoef ;
                dojo.style(all_ids[i],'background-position', bX+'px '+bY+'px');
            }
            resizeNode(all_ids[0], this.sizeRatio,1);
           
        },

        specifyPositionInDeck: function(tile) {
            var number = dojo.query('#'+tile+' > ').length -1;

            return number;
        },

        attachToNewParentOnEnd: function(id, newparent) {
            this.attachToNewParent(id,newparent);
            if ( newparent.split("_")[1] == 'baseresource' || newparent.split("_")[1] == 'advresource' ) {
                var resType = dojo.query('#'+id)[0].className.split(' ')[1];
                var newpos = getPositionForResource(resType, this.sizeRatio); 
                var left = newpos[0];
                var top = newpos[1];

                dojo.style(id, 'left', left+'px');
                dojo.style(id, 'top', top+'px');
            }

            if (newparent.split("_")[1] == 'room') {
                dojo.style(id, 'top','0px');
                dojo.style(id, 'left','0px');
                var spectype1 = id.split("_")[1];
                var spectype2 = id.split("_")[2];
                this.constructTooltip('room_'+spectype1, _(this.gamedatas.rooms[spectype1]['nameTr']), this.gamedatas.rooms[spectype1]['text'], null);
                if ( typeof spectype2 != 'undefined') {
                    this.constructTooltip('room_'+spectype2, _(this.gamedatas.rooms[spectype2]['nameTr']), this.gamedatas.rooms[spectype2]['text'], null);
                }
            }

            if (newparent.split("_")[1] == 'specialist') {
                var spectype = id.split("_")[1];
                if ($('specialist_'+spectype+'_front') ) {
                    this.constructTooltip('specialist_'+spectype+'_front', _(this.gamedatas.specialist[spectype]['nameTr']), this.gamedatas.specialist[spectype]['text'], null);
                }
            }

            if (id.split("_")[0] == 'sigil' ) {    
                //dojo.toggleClass(id, 'small');
                if (newparent.split("_")[1] == 'quest' || newparent.split("_")[0] == 'quest') {
                    //sigilhere
                    if (dojo.hasClass(newparent, 'questcontainer')) {
                        var sigilCount = dojo.query('#'+newparent+' .sigil').length;
                        dojo.addClass(id, 'sigil'+sigilCount);
                    } else {
                        dojo.toggleClass(id, 'small');
                    }
                    resizeChildNode(id, this.sizeRatio,1);
                }
                if (newparent.split("_")[1] == 'sigilplace') {
                    dojo.toggleClass(id, 'small');
                    dojo.style(id, 'left', '');
                    dojo.style(id, 'top', '');
                    dojo.style(id, 'width', '');
                    dojo.style(id, 'height', '');
                    dojo.style(id, 'background-position', '');
                }
            }
        },

        toggleMasterRooms: function() {
            var pos = dojo.style('masterroombox','left');

            if (pos > 0) {
                dojo.style('masterroombox','left','0px');
                $('masterroomtoggler').innerText = _('HIDE MASTER ROOMS >>');
            } else {
                dojo.style('masterroombox','left',1100*this.sizeRatio+'px');
                $('masterroomtoggler').innerText = _('<< SHOW MASTER ROOMS');
            }
        },

        toggleCards: function(setON, setOFF) {
            if (setON) {
                dojo.removeClass('handcontainer', 'hidden');
            } else if (setOFF) {
                dojo.addClass('handcontainer', 'hidden');
            } else {
                dojo.toggleClass('handcontainer', 'hidden'); 
            }

            // if (dojo.hasClass('handcontainer', 'hidden')) {
            //     $('showtreasure').innerText = _('▽ Show my treasure cards');
            //     // dojo.style('handcontainer','height','');
            // } else {
            //     // var h = dojo.marginBox('handcontainer').h;
            //     // dojo.style('handcontainer','height',h+'px');
            //     $('showtreasure').innerText = _('△ Hide my treasure cards');
            // }
        },

        incrementQuestCounters: function(quest_id, player_id) {
            var hero = this.gamedatas.quest[quest_id].hero[0];
            var items = [];
            for(var i=1;i<Object.keys(this.gamedatas.quest[quest_id].items).length+1;i++ ) {
                items.push( this.gamedatas.quest[quest_id].items[i][1].toLowerCase() );
            }

            $(player_id+'_questCounter_'+hero).innerText =  parseInt($(player_id+'_questCounter_'+hero).innerText) +1;
            for(var i=0;i<items.length;i++ ) {
                $(player_id+'_questCounter_'+items[i]).innerText =  parseInt($(player_id+'_questCounter_'+items[i]).innerText) +1;
            }
        },

        updateGamedatas: function(what, id, data) {
            this.gamedatas[what][id];
            var data_keys = Object.keys(data);
            for(var i=0;i<data_keys.length;i++ ) {
                this.gamedatas[what][id][data_keys[i]] = data[data_keys[i]];
            }
        },

        addCardSelectionMenu: function(numberOfCards) {
                var id = $('card_menu0')? 1:0;
                dojo.place( this.format_block( 'jstpl_card_selector', {
                    width: (dojo.style('tile_card_0', 'width') + dojo.style('tile_card_0', 'width')*0.25)*numberOfCards,
                    height: dojo.style('tile_card_0', 'height')+10,
                    id: id,
                } ) , 'overall_game_content');
                // } ) , 'handcontainer');

                for (var i=0;i<numberOfCards;i++) {
                    dojo.place( this.format_block( 'jstpl_player_cardtile', {
                        id:  (id == 1)? i+40 : i+20,
                        width: dojo.style('tile_card_0', 'width'),
                        height: dojo.style('tile_card_0', 'height'),
                    } ) , 'card_menu'+id);
                }

                this.placeOnObjectPos('card_menu'+id, 'player_cards', 0, -2*dojo.style('tile_card_0', 'height')+10);
                // this.placeOnObjectPos('card_menu'+id, 'player_cards', 0, dojo.style('tile_card_0', 'height')+10);
                this.toggleCards(true, false);
        },

        moveSigil: function(sigil_id, destination, offering) {
            this.attachToNewParent(sigil_id, destination);
            if (offering) {
                // count nbr of sigils
                var sigilCount = dojo.query('#'+destination+' .sigil').length -1 ;
                var x = -(dojo.style('tile_quest_0', 'width'))+sigilCount*(dojo.style('tile_quest_0', 'width')/6);
                var y =  dojo.style('tile_quest_0', 'height')/1.1;

                dojo.toggleClass(sigil_id, 'small');
                var anim = this.slideToObject(sigil_id, destination);

                //sigilhere
            } else {
                var anim = this.slideToObject( sigil_id, destination ); 
                // dojo.connect(anim, 'onEnd', dojo.hitch(this, 'attachToNewParentOnEnd', sigil_id, destination ));
            }
            dojo.connect(anim, 'onEnd', dojo.hitch(this, 'attachToNewParentOnEnd', sigil_id, destination ));
            anim.play();
        },

        showHint: function() {
            // Create the new dialog over the play zone. You should store the handler in a member variable to access it later
            this.myDlg = new ebg.popindialog();
            this.myDlg.create( 'hintwindow' );
            this.myDlg.setTitle( _('Reference cards') );


            var htmlA = '<div class="quest tooltipitem refcard bg-refcardback">'+this.makeReferenceCards().front+'</div>';
            var htmlB = '<div class="quest tooltipitem refcard bg-refcardfront">'+this.makeReferenceCards().back+'</div>';

            var wrap = '<div class="overseer_wrap">'+htmlA+htmlB+'</div>';
            this.myDlg.setContent( wrap ); 

            var texts = dojo.query('.refcardtext');
            this.myDlg.show();
            for(var i=0;i<texts.length;i++) {
                this.calculateFont(texts[i].id);
            }
        },



        //--------------------------/** Log injection */-------------------------------------------------------

        /* @Override */
        format_string_recursive : function(log, args) {
            try {
                if (log && args && !args.processed) {

                    args.processed = true;
                    
                    if (!this.isSpectator){
                        args.You = this.divYou(); // will replace ${You} with coloreill replace ${You} with colored version
                    
                        if (args.player_name_id) {
                            args.player_name_id = this.divColoredName(args.player_name_id);
                        }

                        if (args.player2_name_id) {
                            args.player2_name_id = this.divColoredName(args.player2_name_id);
                        }
                    }
                    
                    if (args.resourceList) {
                        args.resourceList = this.divResourceList(args.resourceList);
                    }

                    var keys = ['resource', 'cardback', 'card_text', 'gold', 'newline', 'armor', 'weapon', 'magic', 'gems', 'points', 'warrior', 'rogue', 'mage', 'treasure', 'hero', 'actionicon' ];
                    for (var i=0;i<10;i++) {
                        keys.push('resource'+i);
                        keys.push('cardback'+i);
                        keys.push('card'+i);
                        keys.push('treasure'+i);
                        keys.push('hero'+i);
                        keys.push('points'+i);
                        keys.push('gold'+i);
                    }

                    for ( var i in keys) {
                        var key = keys[i];

                        if (typeof args[key] == 'string' ) {
                            args[key] = this.getTokenDiv(key, args);                            
                        }

                        if (typeof args[key] == 'string' && key == 'card_text') {
                            args[key] = this.getBoldText(args[key]);                            
                        }
                    }
                }
            } catch (e) {
                console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited(arguments);
        },

        divYou : function() {
            if (this.gamedatas.players[this.player_id] != null) {
                var color = this.gamedatas.players[this.player_id].color;
                var color_bg = "";
                if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
                    color_bg = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
                }
                var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + __("lang_mainsite", "You") + "</span>";
                return you;
            } 
            return __("lang_mainsite", "You");
        },

        divColoredName : function(player_name_id) {
            if (this.gamedatas.players != null) {
                var color = this.gamedatas.players[player_name_id].color;
                var color_bg = "";
                if (this.gamedatas.players[player_name_id] && this.gamedatas.players[player_name_id].color_back) {
                    color_bg = "background-color:#" + this.gamedatas.players[player_name_id].color_back + ";";
                }
                var name = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + this.gamedatas.players[player_name_id].name + "</span>";
                return name;
            } 
            return player_name_id;
        },

        divResourceList : function(resourceList) {
            var string = '';
            var paramObject = {};
            for (var i=0;i<resourceList.length;i++) {
                string = string+'${resource'+i+'} ';
                paramObject['resource'+i] = resourceList[i];
            }

            return this.format_string_recursive(string, paramObject);
        },

        getBoldText: function(args) {
            // var boldText = "<div style=\"font-weight:bold; padding: 6px;\">" +args+ "</div>";
            var boldText = "<div style=\"font-weight:bold; padding: 6px;\">" +_(args)+ "</div>";
            return boldText;
        },

        getTokenDiv : function(key, args) {
            var token = args[key];
            var item_type = token.split("_")[0];

            switch (item_type) {
                case 'resource':
                    if (token.split("_")[1] == 'plain') {
                        var tokenDiv = '<div class="logitem resourceplain"></div>';
                    } else {
                        var tokenDiv = '<div class="resource logresource '+token.split("_")[1]+'"></div>';
                    }
                return tokenDiv;           
                    
                case 'treasure':
                    if (token.split("_")[1] == 'plain') {
                        var tokenDiv = '<div class="logitem treasureplain"></div>';
                    } else {
                        var tokenDiv = '<div class="treasure treasurelog bg-CardBack'+token.split("_")[1]+'"></div>';
                    }
                return tokenDiv;  

                case 'gold':
                    var tokenDiv = '<div class="logitem goldlog ">'+token.split("_")[1]+'</div>';
                return tokenDiv; 

                case 'points':
                    var tokenDiv = '<div class="logitem pointslog ">'+token.split("_")[1]+'</div>';
                return tokenDiv; 

                case 'newline':
                return "<br><br>";

                case 'armor':
                case 'weapon':
                case 'hero':
                    var tokenDiv = '<div class="logitem smallcell '+token.split("_")[1]+'"></div>';
                return tokenDiv; 

                case 'gems':
                case 'magic':
                    var tokenDiv = '<div class="logitem '+token.split("_")[1]+'"></div>';
                return tokenDiv; 

                case 'actionicon':
                    // var tokenDiv = '<div class="iconholder"><div class="actionicon '+token.split("_")[1]+'"></div></div>';
                    var tokenDiv = '<div class="actionicon '+token.split("_")[1]+'"></div>';
                return tokenDiv; 
    
                default:
                    break;
            }
            return token;
       },

       //-------------------------------- TOOLTIPS --------------------------------------------------//

       constructTooltip: function(id, headerText, bottomText, discount) {
            this.removeTooltip( id );
            if (id.split("_")[0] == 'tile') {
                var clone = dojo.clone( $('quest_'+id.split("_")[2]+'_front') );
            } else {
                var clone = dojo.clone( $(id) );
            }
            dojo.setAttr(clone, "id", 'clone');
            dojo.empty(clone);
            dojo.place(clone.outerHTML, 'fakeplace' );

            dojo.removeClass('clone', 'roomback selected forSelection selection');
            if (dojo.hasClass('clone', 'specialistenlarged')) {
                dojo.removeClass('clone', 'specialistenlarged');
                dojo.addClass('clone', 'specialistbase');
            }
            if (dojo.hasClass('clone', 'singleroomenlarged')) {
                dojo.removeClass('clone', 'singleroomenlarged');
                dojo.addClass('clone', 'singleroom');
            }
            if (dojo.hasClass('clone', 'doubleroomenlarged')) {
                dojo.removeClass('clone', 'doubleroomenlarged');
                dojo.addClass('clone', 'doubleroom');
            }

            // dojo.place('clone', 'fakeplace' );
            dojo.style('clone', 'background-position', '' );
            dojo.style('clone','width', '');
            dojo.style('clone','height', '');

            var actW = dojo.style('clone','width');
            var actH = dojo.style('clone','height');
            var actBx = parseFloat(dojo.style('clone','background-position-x'));
            var actBy = parseFloat(dojo.style('clone','background-position-y'));

            var item_type = id.split("_")[0];
            if (bottomText) {
                var additionalLines = '<div class="tooltipLine"></div><div class="tooltipFooter">'+this.getTr(bottomText)+'</div>';
            } else {
                var additionalLines = '<div class="tooltipFooter"></div>';
            }

            switch (item_type) {
                case 'room':
                    var coefWH = 2; var coefBG = dojo.hasClass('clone', 'singleroom') ? this.roomSizeCoef*2 : this.roomSizeCoefDual*2;
                    var txt = _('Available rooms: ');
                    if (discount != null) {
                        var additionalLines = additionalLines+'<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+discount+'</div>';
                    }
                break;  
                case 'specialist':
                    var coefWH = 2; var coefBG = this.specialistSizeCoef*2;
                    var dsc = _('Current discount: ${gold}'); var args = {'gold': 'gold_'+discount};
                    if (discount != null) {
                        var additionalLines = additionalLines+'<div class="tooltipLine"></div><div class="tooltipFooter">'+this.format_string_recursive(dsc,args)+'</div>';
                    }
                break; 
                case 'quest':
                    var coefWH = 2; var coefBG = this.questSizeCoef*2;

                    var questId = id.split("_")[1];
                    var items = this.gamedatas.quest[questId].items;
                    if (items) {
                        var itemNumber = Object.keys(items).length;
                        if (itemNumber == 1 ) {
                            var txt = _('Item: ');
                            var additionalLines = additionalLines+'<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+this.getTr(this.gamedatas.quest[questId].items[1][0])+'</div>';
                        } else {
                            var txt = _('Items: ');
                            var additionalLines = additionalLines+'<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+this.getTr(this.gamedatas.quest[questId].items[1][0])+' & '+this.getTr(this.gamedatas.quest[questId].items[2][0])+'</div>';
                        }
                    }
                break; 

                case 'tile':
                    var coefWH = 2; var coefBG = this.questSizeCoef*2;
                    var questId = id.split("_")[2];
                    var items = this.gamedatas.quest[questId].items;
                    var itemNumber = Object.keys(items).length;
                    if (itemNumber == 1 ) {
                        var txt = _('Item: ');
                        var additionalLines = additionalLines+'<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+this.getTr(this.gamedatas.quest[questId].items[1][0])+'</div>';
                    } else {
                        var txt = _('Items: ');
                        var additionalLines = additionalLines+'<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+this.getTr(this.gamedatas.quest[questId].items[1][0])+' & '+this.getTr(this.gamedatas.quest[questId].items[2][0])+'</div>';
                    }
                break; 
            }

            dojo.style('clone','width', coefWH*actW+'px');
            dojo.style('clone','height', coefWH*actH+'px');
            dojo.style('clone','background-position-x', coefBG*actBx+'px');
            dojo.style('clone','background-position-y', coefBG*actBy+'px');

            var ttip =  this.format_block( 'jstpl_tooltipItem', {
                    headerText: headerText,
                    main: $('clone').outerHTML,
                    maxWidthHeader: coefWH*actW,
                    maxWidth: coefWH*actW,
                    additionalLines: additionalLines ,
            });
            this.addTooltipHtml( id, ttip );
            dojo.destroy($('clone'));
       },

       constructTooltipTreasure: function(treasure_id, discardInfo, cloned ) {
            var additionalLines = '<div class="tooltipLine"></div>'+_('No playable efffect');
            if (this.gamedatas.treasure[treasure_id].effect != null) {
                var txt = this.getTr(this.gamedatas.treasure[treasure_id].text);
                var additionalLines = '<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+'</div>';
            }
            var txt2 = '';
            if (this.gamedatas.treasure[treasure_id].cathegory == 'Relic') {
                var txt = this.getTr(this.gamedatas.treasure[treasure_id].text);
                switch (this.gamedatas.treasure[treasure_id].color) {
                    case 'blue':
                            txt2 = _(' (blue treasure card)');
                    break;
                    case 'red':
                            txt2 = _(' (red treasure card)');
                    break;
                    case 'yellow':
                            txt2 = _(' (yellow treasure card)');
                    break;
                }
                var additionalLines = '<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+'</div>';
                if (this.playerHasAuctioneer) { 
                    var sellValue = this.format_string_recursive('${gold}', {'gold': 'gold_4'} )+_(' (Auctioneer)');
                }
            }
            if (this.gamedatas.treasure[treasure_id].cathegory == 'Scroll' && this.playerHasAuctioneer) {
                var sellValue = this.format_string_recursive('${gold}', {'gold': 'gold_4'} )+_(' (Auctioneer)');
            }
            if (this.gamedatas.treasure[treasure_id].cathegory == 'Charm' && this.playerHasAuctioneer) {
                var sellValue = this.format_string_recursive('${gold}', {'gold': 'gold_4'} )+_(' (Auctioneer)');
            }
            if (discardInfo) {
                var txt = _('Click to view all discarded treasure cards');
                additionalLines = additionalLines + '<div class="tooltipLine"></div><div class="tooltipFooter">'+txt+'</div>'
            }

            var htext = _(this.gamedatas.treasure[treasure_id].nameTr);
            if (this.gamedatas.treasure[treasure_id].cathegory === null) {
                var mtext = '';
            } else {
                var mtext = _(this.gamedatas.treasure[treasure_id].cathegory);
            }

            if (typeof sellValue == 'undefined') {
                var sellValue = this.format_string_recursive('${gold}', {'gold': 'gold_'+this.gamedatas.treasure[treasure_id].sellcost} );
            }

            var ttip =  this.format_block( 'jstpl_tooltipItem', {
                headerText: htext, //'txt', //_(this.gamedatas.treasure[treasure_id].nameTr),
                main: mtext+txt2, //'txt',  //_(this.gamedatas.treasure[treasure_id].cathegory),
                footerText: _('Sell value: ')+sellValue,
                maxWidthHeader: 200,
                maxWidth: 200,
                additionalLines: additionalLines ,
            });

            if (cloned) {
                this.addTooltipHtml( 'clone'+cloned, ttip );
            } else {
                this.addTooltipHtml( 'treasure_'+treasure_id, ttip );
            }
       },   
       
       makeReferenceCards: function() {
            var textsFront = [_('Turn Actions (Choose One)'), _('Gather'), _('the same'), _('or any'), _('Craft'), _('Expand'), _('and/or') ];
            var stylesFront = ['mid header', 'mid header', 'start', 'end', 'mid header', 'mid header', 'mid'];
            var textsBack= [ _('Scoring'), _('Charms'), _('Each Charm:'), _('or'), _('Player(s) with the most Charms'), _('Relics'), _('Different Relics collected:'), _('Quests'), _('Specialists'), _('Rooms') ];
            var stylesBack = ['mid header', 'mid header', 'end', 'mid', 'mid', 'mid header', 'mid', 'mid header', 'mid header', 'mid header'];

            var leftFront =[26,  26, 225, 118,  26,  26, 180];
            var topFront = [20,  82,  96, 138, 200, 320, 356];
            var sizeFront = [ [280,30], [80,24], [85,22], [80,22], [80,24], [80,28], [56,18]  ];

            var leftBack =[26,  26, 122, 250,  127,  26, 118, 36, 124, 232];
            var topBack = [20,  86,  95, 95, 129, 198, 210, 326, 326, 326];
            var sizeBack = [ [280,30], [80,24], [100,20], [22,20], [135,38], [80,24], [184,20], [60,20], [80,20], [60,20]   ];

            var resultFront = '';
            var resultBack = '';
            for(var i =0;i<textsFront.length;i++) {
                resultFront = resultFront + this.format_block('jstpl_refcarddiv', {
                    textId: 'tf'+i,
                    left: leftFront[i]+'px',
                    top: topFront[i]+'px',
                    width: sizeFront[i][0]+'px',
                    height: sizeFront[i][1]+'px',
                    text: textsFront[i],
                    s: stylesFront[i],
                });
            }

            for(var i =0;i<textsBack.length;i++) {
                resultBack = resultBack + this.format_block('jstpl_refcarddiv', {
                    textId: 'tb'+i,
                    left: leftBack[i]+'px',
                    top: topBack[i]+'px',
                    width: sizeBack[i][0]+'px',
                    height: sizeBack[i][1]+'px',
                    text: textsBack[i],
                    s: stylesBack[i],
                });
            }

            return { 'front' : resultFront, 'back': resultBack };
        },

       getTr : function(name) {
            if (typeof name == 'undefined') return null;
            if (typeof name.log != 'undefined') {
                name = this.format_string_recursive( name.log , name.args);
            } else {
                name = this.clienttranslate_string(name);
            }
            return name;
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        selectAction: function(evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "selectAction" )) { return;};

            var target = evt.target || evt.srcElement;
            var seleted_action = target.id.split("_")[1];

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/selectAction.html", {selectedAction: seleted_action, lock : true}, 
                this, function(result) {}, function(is_error) {
            });

        },

        cancelAction: function(evt) {
            if(evt) {
                dojo.stopEvent( evt );
            }
            if (!this.checkAction( "cancel" )) { return;};


            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/cancelAction.html", {lock : true}, 
                this, function(result) {}, function(is_error) {
            });
        },

        cancelSelection: function(evt) {
            dojo.stopEvent( evt );

            if (!this.checkAction( "cancel" )) { return;};

            dojo.query('.tilequest').removeClass('selection');
            dojo.forEach(this.handlers,dojo.disconnect);
            this.handlers = [];
            this.selectedCraftItem = [];
            this.restoreServerGameState();
        },

        cancelClientState:function(evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "cancel" )) { return;};

            var src = evt.target || evt.srcElement;

            if (src.id == 'cancelroom') {
                // return rooom back
                var room_id = this.gamedatas.gamestate.args.room_id;
                var tile = this.gamedatas.gamestate.args.tile_from;
                if (this.gamedatas.gamestate.args.dualside == false) {
                    var parent = dojo.query('#room_'+room_id)[0].parentNode.id;
                    room_id = 'room_'+room_id;
                } else {
                    var parent = dojo.query('#room_'+room_id)[0].parentNode.parentNode.id;
                    room_id = dojo.query('#room_'+room_id)[0].parentNode.id;
                }

                if (parent != tile ) {
                    if (dojo.hasClass(room_id, 'doubleroomenlarged' ) ) {
                        this.moveItemBack(room_id, tile, this.roomSizeCoefDual);
                    } else {
                        this.moveItemBack(room_id, tile, this.roomSizeCoef);
                    }
                }
            }

            if (src.id == 'cancelspecialist' && this.gamedatas.gamestate.args.specialist_id != null) {
                var specialist_id = this.gamedatas.gamestate.args.specialist_id;
                var tile = this.gamedatas.gamestate.args.tile_from;
                var parent = dojo.query('#specialist_'+specialist_id)[0].parentNode.id;

                if (this.gamedatas.gamestate.args.bard) {
                        this.moveItemOnBoard('specialist_'+specialist_id, tile, false);
                } else {
                    // return specialist back
                    if (parent != tile ) {
                        this.moveItemBack('specialist_'+specialist_id, tile, this.specialistSizeCoef);
                    }
                }
            }

            if (this.gamedatas.gamestate.name == "client_playTreasureCard") {
                this.restoreServerGameState();
            } else {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/cancelAction.html", {lock : true}, 
                    this, function(result) {}, function(is_error) {
                });
            }
            

        },

        selectResource: function(resId, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "chooseResource" )) { return;};

            var res = '';
            for(var i=0;i<this.selectedResourcesId.length;i++) {
                res += this.selectedResourcesId[i]+'_';
            }
        
            res += resId;

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/chooseResource.html", {resources: res, lock : true}, 
                this, function(result) {  }, function(is_error) {
            });
        },

        deselectResource: function(res, evt) {
            dojo.stopEvent( evt );

            var id = res.split("_")[1];
            var index = this.selectedResourcesId.indexOf( id);
            if (index > -1) {
                this.selectedResourcesId.splice(index, 1);
            }

            var cs = constructClientState('gather', {'replaceTrigger': false, 'maxReached': false });
            this.setClientState(cs['name'], cs['parameters']);
        },

        selectResourceForTrade: function(resId, tradeType, otherId, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "chooseTradeResource" )) { return;};

            if (tradeType == 'buy') {
                var cs = constructClientState('traderesources', {"forSell": otherId, "forBuy": resId} );
                this.setClientState(cs['name'], cs['parameters']);
            }

            if (tradeType == 'sell') {
                var cs = constructClientState('traderesources', {"forSell": resId, "forBuy": otherId} );
                this.setClientState(cs['name'], cs['parameters']);
            }
        },

        selectResourceForSteal: function(resId, selectedId, replace, returnId, evt) { 
            dojo.stopEvent( evt );
            if (!this.checkAction( "selectResource" )) { return;};

            var player = dojo.query('#resource_'+resId)[0].parentNode.id.split("_")[3] 
            
            if (player == this.player_id) {
                var selected = selectedId;
                var forreturn = resId;
            } else {
                var selected = resId;
                var forreturn = returnId;
            }

            var cs = constructClientState('steal', {"selected": selected, "triggerReplace": replace, "selectedForReplace": forreturn } );
            this.setClientState(cs['name'], cs['parameters']);
        },

        stealResource: function(stealId, replace, returnId, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "stealResource" )) { return;};

            var player = dojo.query('#resource_'+stealId)[0].parentNode.id.split("_")[3];

            if (stealId == null) {
                this.showMessage( _('You must select one resource to steal'), "error" );
                return;
            }

            if (replace && returnId == null) {
                this.showMessage( _('You must also select one resource to return'), "error" );
                return;
            }

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/stealResource.html", {stealId: stealId, player: player, returnId: returnId,  lock : true}, 
                this, function(result) {}, function(is_error) {
            });
        },

        selectResourceForReplace: function(resType, numberToSelect, alreadySelected, selectedNew, bonusVariant, treasureVariant, evt ) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "chooseResource" )) { return;};

            if (alreadySelected.length == numberToSelect) { return;}
            var sel = alreadySelected;
            sel.push(resType);

            if (bonusVariant) {
                // var cs = constructClientState('replaceBonus', {'resource':selectedNew[0], 'number': 1, 'alreadySelected': sel, 'selectedResources': selectedNew, });
                // this.setClientState(cs['name'], cs['parameters']);
                var cs = constructClientState('replaceBonus', {'number': numberToSelect, 'alreadySelected': sel, 'selectedResources': selectedNew, });
                this.setClientState(cs['name'], cs['parameters']);
            } else if (treasureVariant) {
                var cs = constructClientState('replaceRes', {'number': numberToSelect, 'alreadySelected': sel, 'selectedResources': selectedNew, });
                this.setClientState(cs['name'], cs['parameters']);
            } else {
                var cs = constructClientState('gatherAndReplace', {'number': numberToSelect, 'alreadySelected': sel, 'selectedResources': selectedNew, });
                this.setClientState(cs['name'], cs['parameters']);
            }
        },

        cancelReplaceResource: function(numberToSelect, alreadySelected, selectedNew, bonusVariant, treasureVariant, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "cancel" )) { return;};

            if (bonusVariant) {
                var cs = constructClientState('replaceBonus', {'number': numberToSelect, 'alreadySelected': [], 'selectedResources': selectedNew, });
                this.setClientState(cs['name'], cs['parameters']);
            } else if (treasureVariant) {
                var cs = constructClientState('replaceRes', {'number': numberToSelect, 'alreadySelected': [], 'selectedResources': selectedNew, });
                this.setClientState(cs['name'], cs['parameters']);
            }
        },

        takeResources: function(i, resources, replaceCount, maxReached, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "takeResources" )) { return;};

            if ( maxReached == false) {
                this.confirmationDialog( _('You can still gather more resources. Are you sure?'), 
                dojo.hitch( this,
                    dojo.partial(this.takeResources,i, resources, replaceCount, true, evt)   )); 
                return;
            }

            if ( dojo.isArray(replaceCount) ) {
                if (replaceCount[i] > 0 ) {                                                                             
                    var cs = constructClientState('replaceRes', {'number': replaceCount[i], 'alreadySelected': [], 'selectedResources': resources[i], });
                    this.setClientState(cs['name'], cs['parameters']); 
                    return;
                }
            } else {
                if (replaceCount > 0 ) {                                                                             
                    this.confirmationDialog( _("You don't have enough free space - you will have to replace some resources. Are you sure?"), 
                        dojo.hitch( this, function() {
                            var cs = constructClientState('gatherAndReplace', {'number': replaceCount, 'alreadySelected': [], 'selectedResources': resources[i], });
                            this.setClientState(cs['name'], cs['parameters']);
                        })
                    );  
                    return;
                }
            }

            var res = i != null ? resources[i].join("_") : "_";
            this.dealerPassActive = false;
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/takeResourcesAndReplace.html", {take_res: res, return_res: '_', lock : true}, 
                this, function(result) {}, function(is_error) {
            });
        },

        takeResourcesAndReplace: function( resourcesToGather, resourcesToReturn, numberToReplace, trade, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "takeResourcesAndReplace" )) { return;};
            
            if (numberToReplace > resourcesToReturn.length && !this.gamedatas.gamestate.args.treasureVariant) {
                this.showMessage( _('You must select more resources to return'), "error" );
                return;
            }

            if ( ( this.arraysEqual(resourcesToGather, [null]) || this.arraysEqual(resourcesToReturn, [null]) ) && trade )   {
                this.showMessage( _('You must select resource to trade'), "error" );
                return;
            }

            var res_take = resourcesToGather.join("_");
            var res_return = resourcesToReturn.join("_");

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/takeResourcesAndReplace.html", {take_res: res_take, return_res: res_return,  lock : true}, 
                this, function(result) {
                    this.restoreServerGameState();
                    this.dealerPassActive = false;
                }, function(is_error) {
            });
        },

        takeTreasure: function(arg,evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "takeTreasure" )) { return;};

            if (dojo.isArray(arg)) {
                var newarg = '';
                for(var i=0;i<arg.length;i++) {
                    newarg = newarg+arg[i]+'_';
                }
                arg = newarg;
            }

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/drawTreasureCard.html", {card: arg, lock : true}, 
                this, function(result) {}, function(is_error) {
            });
        },

        selectExpandItem: function(id, type, evt) {
            dojo.stopEvent( evt );
            if (!this.checkAction( "selectExpandItem" )) { return;};

            var src = evt.target || evt.srcElement;
            if (dojo.hasClass(src.id,'rotate')) {
                return;
            }

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/selectExpandItem.html", {id: id, type: type, lock : true}, 
                this, function(result) {}, function(is_error) {
            });
        },

        confirmRoomPlacement: function(room_id, evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "placeRoom" )) { return;};

            //check for bonuses
            var bonusres1 = this.gamedatas.mat[this.roomPosition];
            var bonusres2 = null;
            var bonusrescount = dojo.query('#main_board .'+bonusres1).length;
            if (dojo.hasClass('room_'+room_id, 'doubleroomenlarged')) {
                var newpos = (parseInt(this.roomPosition.split("_")[0])+1)+'_'+this.roomPosition.split("_")[1] ;
                bonusres2 = this.gamedatas.mat[newpos];
                bonusrescount += dojo.query('#main_board .'+bonusres2).length;
            }

            if ( (bonusres1 || bonusres2 ) && bonusrescount< 1 && (bonusres1 || bonusres2) != 'gold' ) {
                // warn player about out of stock bonus res
                this.confirmationDialog( _("Bonus resource out of stock. Are you sure?"), 
                        dojo.hitch( this, function() {
                            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/placeRoom.html", {room_id: room_id, destination : this.roomPosition, lock : true}, 
                                this, function(result) {
                                    dojo.query('.room.selection').removeClass('selection');
                                    this.roomPosition = 0;
                                }, function(is_error) {
                            });
                        })
                    );  
                return;
            } else {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/placeRoom.html", {room_id: room_id, destination : this.roomPosition, lock : true}, 
                    this, function(result) {
                        dojo.query('.room.selection').removeClass('selection');
                        this.roomPosition = 0;
                    }, function(is_error) {
                });
            }
        },

        confirmSpecialistPlacement: function(specialist_id, evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "placeSpecialist" )) { return;};
            if (specialist_id == null) { return;};

            var  pos = this.specialistPosition.replace("-", "z");
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/placeSpecialist.html", {specialist_id: specialist_id, destination : pos, lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        craftItem: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "craftItem" )) { return;};

            var src = evt.target || evt.srcElement;
            var quest_id =  src.id.split("_")[2];
            var item_id = src.id.split("_")[3];

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/selectCraftItem.html", {quest_id: quest_id, item_id: item_id, lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        confirmCraft:function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "craftItem" )) { return;};

            if (this.selectedCraftItem.length == 0 ) {
                this.showMessage( _('You must select item to craft'), "error" );
                return;
            }

            var src = evt.target || evt.srcElement;
            if (src.id == 'confirmSingleCraft' || src.id == 'confirmCraft') {
                var quest_id = this.selectedCraftItem[0];
                var item_id = this.selectedCraftItem[1];
                var second = false;
            } else {
                var quest_id = this.selectedCraftItem[0];
                var item_id = this.selectedCraftItem[1];
                var second = true;
            }
            
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/confirmCraft.html", {quest_id: quest_id, item_id: item_id, second_item: second, lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        selectTreasure: function(treasure_id, evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "selectTreasureCards" )) { return;};

            // if (dojo.hasClass('treasure_'+treasure_id, 'selected')) {
                // dojo.addClass('treasure_'+treasure_id, 'forSelection');
                // dojo.removeClass('treasure_'+treasure_id, 'selected');
            if (dojo.hasClass('treasure_'+treasure_id+'_front', 'selected')) {
                dojo.addClass('treasure_'+treasure_id+'_front', 'forSelection');
                dojo.removeClass('treasure_'+treasure_id+'_front', 'selected');
                var index = this.selectedTreasure.indexOf(treasure_id);
                if (index !== -1) this.selectedTreasure.splice(index, 1);
            } else {
                this.selectedTreasure.push(treasure_id);
                // dojo.addClass('treasure_'+treasure_id, 'selected');
                // dojo.removeClass('treasure_'+treasure_id, 'forSelection');
                dojo.addClass('treasure_'+treasure_id+'_front', 'selected');
                dojo.removeClass('treasure_'+treasure_id+'_front', 'forSelection');
            }
        },

        selectQuest: function(quest_id, evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "selectQuest" )) { return;};

            if (this.selectedQuest == quest_id) {
                return;
            }

            if (this.selectedQuest != 0) {
                dojo.removeClass('quest_'+this.selectedQuest, 'selected');
                dojo.addClass('quest_'+this.selectedQuest, 'forSelection');
            } 
            dojo.addClass('quest_'+quest_id, 'selected');
            dojo.removeClass('quest_'+quest_id, 'forSelection');
            this.selectedQuest = quest_id;

            if (this.gamedatas.gamestate.name == 'client_placeThug') {
                this.moveItemOnBoard('thugicon', 'quest_'+quest_id, false);
            }
        },

        confirmTreasureSelection: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "confirm" )) { return;};

            var treasure_ids = this.selectedTreasure.join('_');

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/selectTreasureCards.html", {treasure_ids: treasure_ids,lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        confirmQuestSelection: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "confirm" )) { return;};

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/selectQuest.html", {quest_id: this.selectedQuest,lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        playTreasureCard: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "playTreasureCard" )) { return;};

            var src = evt.target || evt.srcElement;
            var treasure_id = src.id.split("_")[1];
            this.selectedTreasureForPlay = treasure_id;

            // if (dojo.hasClass('treasure_'+treasure_id, 'selected')) {
            //     dojo.addClass('treasure_'+treasure_id, 'forSelection');
            //     dojo.removeClass('treasure_'+treasure_id, 'selected');
            // } else {
            //     dojo.addClass('treasure_'+treasure_id, 'selected');
            //     dojo.removeClass('treasure_'+treasure_id, 'forSelection');
            // }

            var cs = constructClientState('treasureCardPlay', { 'selectedCard': treasure_id });
            this.setClientState(cs['name'], cs['parameters']);
        },

        playSelctedTreasure: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "playTreasureCard" )) { return;};

            var src = evt.target || evt.srcElement;
            var sell = src.id == 'sell' ? true:false;

            if (sell && this.gamedatas.treasure[this.selectedTreasureForPlay].sellcost == 0 && !this.playerHasAuctioneer ) {
                this.confirmationDialog( _('You will sell this card for zero gold, are you sure?'), dojo.hitch( this, function() {
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/playTreasureCard.html", {treasure_id: this.selectedTreasureForPlay,  sell: sell ,lock : true}, 
                    this, function(result) {  }, function(is_error) {});
                } ) ); 
                return;
            } else {
                var txt = null;
                if (!sell && this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Fortune Potion' && this.fortunePotionActive) {
                    var txt = _('Fortune Potion will have no effect for the 2nd time, are you sure?');
                } 
                if (!sell && this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Lucky Potion' && this.luckyPotionActive) {
                    var txt = _('Lucky Potion will have no effect for the 2nd time, are you sure?');
                }
                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Fortune Potion' || this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Lucky Potion') && this.endPhaseActive){
                    var txt = _('Lucky potion and Fortune potion have no effect at the end phase of your turn, are you sure?');
                }

                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Oak Branches')){
                    var woodnumber = dojo.query('#tile_baseresource_1 .wood').length;
                    if (woodnumber < 3) {
                        var txt = dojo.string.substitute( _("Only ${number} wood available, are you sure?"), { number: woodnumber } );
                    }
                }

                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Mithril Bars')){
                    var ironnumber = dojo.query('#tile_baseresource_0 .iron').length;
                    if (ironnumber < 3) {
                        var txt = dojo.string.substitute( _("Only ${number} iron available, are you sure?"), { number: ironnumber } );
                    }
                }

                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Bolts of Silk')){
                    var clothnumber = dojo.query('#tile_baseresource_3 .cloth').length;
                    if (clothnumber < 3) {
                        var txt = dojo.string.substitute( _("Only ${number} iron available, are you sure?"), { number: clothnumber } );
                    }
                }

                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Dragon Hides')){
                    var leathernumber = dojo.query('#tile_baseresource_2 .leather').length;
                    if (leathernumber < 3) {
                        var txt = dojo.string.substitute( _("Only ${number} leather available, are you sure?"), { number: leathernumber } );
                    }
                }

                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Brilliant Diamonds')){
                    var gemnumber = dojo.query('#tile_advresource_0 .gem').length;
                    if (gemnumber < 2) {
                        var txt = dojo.string.substitute( _("Only ${number} gem available, are you sure?"), { number: gemnumber } );
                    }
                }

                if (!sell && (this.gamedatas.treasure[this.selectedTreasureForPlay].name == 'Bottle of Faerie Dust')){
                    var magicnumber = dojo.query('#tile_advresource_1 .magic').length;
                    if (magicnumber < 2) {
                        var txt = dojo.string.substitute( _("Only ${number} magic available, are you sure?"), { number: magicnumber } );
                    }
                }

                if (txt != null) {
                    this.confirmationDialog( txt, dojo.hitch( this, function() {
                        this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/playTreasureCard.html", {treasure_id: this.selectedTreasureForPlay,  sell: sell ,lock : true}, 
                        this, function(result) {  }, function(is_error) {});
                    } ) ); 
                    return;
                } else {
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/playTreasureCard.html", {treasure_id: this.selectedTreasureForPlay,  sell: sell ,lock : true}, 
                    this, function(result) {  }, function(is_error) {});
                }
            }
        },

        bardAction: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "bardAction" )) { return;};

            var cs = constructClientState('selectSpecialistBardAction', {'possibleTiles': this.gamedatas.gamestate.args.possibleTiles });
            this.setClientState(cs['name'], cs['parameters']);
        },

        selectSpecialistForBardAction: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "selectSpecialist" )) { return;};

            var src = evt.target || evt.srcElement;
            var specialist_id = src.id.split("_")[1];
            var tile_from = src.parentNode.parentNode.id;

            var cs = constructClientState('placeSpecialistBardAction', {'possibleTiles': this.gamedatas.gamestate.args.possibleTiles, "specialist_id" : specialist_id, "tile_from": tile_from });
            this.setClientState(cs['name'], cs['parameters']);
        },

        oracleAction: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "oracleAction" )) { return;};
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/oracleAction.html", {lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        passAction: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "pass")) { return;};

            var src = evt.target || evt.srcElement;

            if (src.id == 'stoptrading') {
                this.confirmationDialog( _('Are you sure you want to stop with trading resources?'), dojo.hitch( this, function() {
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/passAction.html", {lock : true}, 
                    this, function(result) {  }, function(is_error) {});
                } )  ); 
                return;
            }

            this.dealerPassActive = false;
            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/passAction.html", {lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        makeBid: function(bid, evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "makeBid" )) { return;};

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/makeBid.html", {bid: bid, lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        offeringAction: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "makeOffering" )) { return;};

            // warn about last move
            if (!this.gamedatas.gamestate.args.vizierActive) {
                this.confirmationDialog( _('This will be your last move, are you sure?'), dojo.hitch( this, function() {
                    this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/makeOffering.html", {lock : true}, 
                    this, function(result) {  }, function(is_error) {});
                } )  ); 
                return;
            } else {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/makeOffering.html", {lock : true}, 
                this, function(result) {}, function(is_error) {});
            }
        },

        endTurn: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "endTurn" )) { return;};

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/endTurn.html", {lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        soloFuneral: function(evt) {
            dojo.stopEvent(evt);
            if (!this.checkAction( "soloFuneral" )) { return;};

            this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/soloFuneral.html", {lock : true}, 
            this, function(result) {}, function(is_error) {});
        },

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your kingsguild.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            dojo.subscribe( 'pause', this, "notif_pause" );
            this.notifqueue.setSynchronous( 'pause', 5000 );
            dojo.subscribe( 'drawCard', this, "notif_drawCard" );
            this.notifqueue.setSynchronous( 'drawCard', 1600 );
            dojo.subscribe( 'placeRoom', this, "notif_placeRoom" );
            this.notifqueue.setSynchronous( 'placeRoom', 600 );
            dojo.subscribe( 'takeResource', this, "notif_takeResource" );
            this.notifqueue.setSynchronous( 'takeResource', 400 );
            dojo.subscribe( 'chooseResource', this, "notif_chooseResource" );
            dojo.subscribe( 'returnResource', this, "notif_returnResource" );
            this.notifqueue.setSynchronous( 'returnResource', 400 );
            dojo.subscribe( 'itemToPlace', this, "notif_itemToPlace" );
            dojo.subscribe( 'placeSpecialist', this, "notif_placeSpecialist" );
            this.notifqueue.setSynchronous( 'placeSpecialist', 800 );
            dojo.subscribe( 'moveSpecialist', this, "notif_moveSpecialist" );
            this.notifqueue.setSynchronous( 'moveSpecialist', 500 );
            dojo.subscribe( 'updateGold', this, "notif_updateGold" );
            dojo.subscribe( 'logInfo', this, "notif_logInfo" );
            dojo.subscribe( 'updateSpecDiscount', this, "notif_updateSpecDiscount" );
            dojo.subscribe( 'stealResource', this, "notif_stealResource" );
            this.notifqueue.setSynchronous( 'stealResource', 600 );
            dojo.subscribe( 'selectCraftItem', this, "notif_selectCraftItem" );
            dojo.subscribe( 'craftItem', this, "notif_craftItem" );
            this.notifqueue.setSynchronous( 'craftItem', 800 );

            dojo.subscribe( 'expandHand', this, "notif_expandHand" );
            dojo.subscribe( 'sellTreasureMenu', this, "notif_sellTreasureMenu" );

            dojo.subscribe( 'thisPlayerChooseTreasure', this, "notif_thisPlayerChooseTreasure" );
            dojo.subscribe( 'thisPlayerGetTreasureFromPlayer', this, "notif_thisPlayerGetTreasureFromPlayer" );
            dojo.subscribe( 'treasureHandle', this, "notif_treasureHandle" );

            dojo.subscribe( 'sellTreasure', this, "notif_sellTreasure" );
            this.notifqueue.setSynchronous( 'sellTreasure', 500 );
            dojo.subscribe( 'updateHand', this, "notif_updateHand" );
            dojo.subscribe( 'reorderRelics', this, "notif_reorderRelics" );
            dojo.subscribe( 'playerChooseRelics', this, "notif_playerChooseRelics" );
            dojo.subscribe( 'thisPlayerChooseRelics', this, "notif_thisPlayerChooseRelics" );
            dojo.subscribe( 'moveThug', this, "notif_moveThug" );
            dojo.subscribe( 'cancelClientState', this, "notif_cancelClientState" );
            dojo.subscribe( 'oracleShow', this, "notif_oracleShow" );
            dojo.subscribe( 'discardFuneral', this, "notif_discardFuneral" );
            dojo.subscribe( 'updateScore', this, "notif_updateScore" );
            dojo.subscribe( 'makeOffering', this, "notif_makeOffering" );
            dojo.subscribe( 'questAndSigilMovement', this, "notif_questAndSigilMovement" );
            dojo.subscribe( 'reshuffleDeck', this, "notif_reshuffleDeck" );
            this.notifqueue.setSynchronous( 'reshuffleDeck', 500 );
            dojo.subscribe( 'updateBard', this, "notif_updateBard" );
            dojo.subscribe( 'resetPotions', this, "notif_resetPotions" );


            dojo.subscribe( 'soloExpand', this, "notif_soloExpand" );
            dojo.subscribe( 'soloKingsFuneral', this, "notif_soloKingsFuneral" );
            dojo.subscribe( 'moveQuest', this, "notif_moveQuest" );
            this.notifqueue.setSynchronous( 'moveQuest', 500 );
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
       notif_pause: function(notif) {

       },

       notif_drawCard: function(notif) {      /// update gamedatas!!!!!!!!!!!!!!!!!
           if (notif.args.card_type != 'treasure') {
                this.updateGamedatas(notif.args.card_type,notif.args.card_id, notif.args.card_info);
                // check if the card is Kings Funeral or Offering -> do not move card and do not apply
                this.drawAndMoveCard(notif.args.card_type, notif.args.card_id, notif.args.card_name.replace(/ /g, '').replace(/'/g, ''), notif.args.card_back, notif.args.location_from, notif.args.location_to);
            } else {
               if (notif.args.player_id == this.player_id) {
                    if ( notif.args.card_name) {
                        this.updateGamedatas(notif.args.card_type,notif.args.card_id, notif.args.card_info);
                        this.drawAndMoveCardToPlayerHand(notif.args.card_id, notif.args.card_name.replace(/ /g, '').replace(/'/g, ''), notif.args.card_back, notif.args.location_from, notif.args.location_to);
                        $(notif.args.player_id+'_cards'+notif.args.color).innerText =  parseInt($(notif.args.player_id+'_cards'+notif.args.color).innerText) +1;
                        //add tooltip
                        this.constructTooltipTreasure(notif.args.card_id, false, false);
                    }
               } else {
                    this.moveCardAndDestroy(notif.args.card_id, notif.args.card_type, 'overall_player_board_'+notif.args.player_id);
                    $(notif.args.player_id+'_cards'+notif.args.color).innerText =  parseInt($(notif.args.player_id+'_cards'+notif.args.color).innerText) +1;
               }
           }
       },

       notif_placeRoom: function(notif) {
           var isReplay = document.getElementById('previously_on').style.display == 'block' || document.getElementById("archivecontrol").style.display == 'block';

            if (notif.args.player_id != this.player_id || isReplay) {
                // flip dual sided room to correct side
                if (notif.args.dual_id != null) {
                    var cont_id = (parseInt(notif.args.dual_id) > parseInt(notif.args.room_id)) ? 'room_'+notif.args.room_id+'_'+notif.args.dual_id : 'room_'+notif.args.dual_id+'_'+notif.args.room_id;
                    if ( parseInt(notif.args.room_id) > parseInt(notif.args.dual_id) && !dojo.hasClass(cont_id,'flipped') ) {
                        dojo.addClass(cont_id, 'flipped');
                    }
                    if ( parseInt(notif.args.room_id) < parseInt(notif.args.dual_id) && dojo.hasClass(cont_id,'flipped') ) {
                        dojo.removeClass(cont_id, 'flipped');
                    }
                }
                this.moveRoomToPlayer('room_'+notif.args.room_id, notif.args.destination );
            }
            this.constructTooltip('room_'+notif.args.room_id, _(this.gamedatas.rooms[notif.args.room_id]['nameTr']), this.gamedatas.rooms[notif.args.room_id]['text'], null);
            dojo.destroy('room_'+notif.args.room_id+'_flip');
       },

       notif_placeSpecialist: function(notif) {
            var isReplay = document.getElementById('previously_on').style.display == 'block' || document.getElementById("archivecontrol").style.display == 'block';

            if (notif.args.player_id != this.player_id || isReplay) {
                this.moveSpecialistToPlayer('specialist_'+notif.args.specialist_id, notif.args.destination );
            }

            dojo.query('#specialist_'+notif.args.specialist_id+' > .specialistdiscount').forEach(dojo.destroy);
            // add storage tiles if exists!!!
            var info = this.gamedatas.specialist[notif.args.specialist_id];
            if (info['ability'] != null) {
                if ( Object.keys(info['ability'])[0] == 'tile' ) {
                    var position =  notif.args.destination.split("_")[2]+'_'+notif.args.destination.split("_")[3];
                    this.addRoomTilesOnBoard('specialist_'+notif.args.specialist_id,notif.args.player_id , position, info['ability']['tile'][0], info['ability']['tile'][1]);
                }
            }

            if ($(notif.args.specialist_id+'_front')) {
                this.constructTooltip('specialist_'+notif.args.specialist_id+'_front', _(this.gamedatas.specialist[notif.args.specialist_id]['nameTr']), this.gamedatas.specialist[notif.args.specialist_id]['text'], null);
                dojo.destroy('specialistdiscount_'+notif.args.specialist_id);
            }

            // after Auctioneer update tooltips
            if (notif.args.player_id == this.player_id && this.gamedatas.specialist[notif.args.specialist_id]['name'] == 'Auctioneer') {
                this.playerHasAuctioneer = true;
                var cards = dojo.query('#player_cards .treasurecontainer');
                for (var i=0;i<cards.length;i++) {
                    var id = cards[i].id.split("_")[1];
                    this.constructTooltipTreasure(id, false, false);
                }
            }
        },

       notif_moveSpecialist: function(notif) {
           if (notif.args.destination == 'main_board') {
                this.moveItemOnBoard('specialist_'+notif.args.specialist_id, notif.args.destination, notif.args.destroy);
           } else {
                this.moveItemOnBoard('specialist_'+notif.args.specialist_id,  'tile_specialist_'+notif.args.destination, notif.args.destroy);
           }
           // update pos in gamedatas
           if (! notif.args.destroy) {
                this.gamedatas.specialist[notif.args.specialist_id]['location_arg'] = notif.args.destination;
                this.constructTooltip('specialist_'+notif.args.specialist_id+'_front', _(this.gamedatas.specialist[notif.args.specialist_id]['nameTr']), this.gamedatas.specialist[notif.args.specialist_id]['text'], this.gamedatas.specialist[notif.args.specialist_id]['discount']);
           } else {
                this.gamedatas.specialist[notif.args.specialist_id]['location'] = 'removed';
           }
        },

        notif_moveQuest: function(notif) {
            if (notif.args.destination == 'destroy') {
                this.moveItemOnBoard('quest_'+notif.args.quest_id, 'main_board', true);
           } else {
                this.moveItemOnBoard('quest_'+notif.args.quest_id,  'tile_quest_'+notif.args.destination, false);
           }
           // update pos in gamedatas
           if (notif.args.destination != 'destroy') {
                this.gamedatas.quest[notif.args.quest_id]['location_arg'] = notif.args.destination;
                this.constructTooltip('quest_'+notif.args.quest_id+'_front', _(this.gamedatas.quest[notif.args.quest_id]['nameTr']), this.gamedatas.quest[notif.args.quest_id]['text'], null);
                if (this.gamedatas.quest[notif.args.quest_id]['name'] != "The King's Funeral") {
                    for (var index = 0; index <  this.gamedatas.quest[notif.args.quest_id]['reward'].length; index++) {
                        this.constructTooltip('tile_quest_'+notif.args.quest_id+'_'+index, _(this.gamedatas.quest[notif.args.quest_id]['nameTr']), this.gamedatas.quest[notif.args.quest_id]['text'], null);
                    }
                }
            } else {
                this.gamedatas.quest[notif.args.quest_id]['location'] = 'removed';
           }
        },

       notif_updateSpecDiscount: function(notif) {
            if ( $('specialistdiscount_'+notif.args.specialist_id) ) {
                $('specialistdiscount_'+notif.args.specialist_id).innerText = parseInt($('specialistdiscount_'+notif.args.specialist_id).innerText)+1;
            } else {
                dojo.place( this.format_block( 'jstpl_specialistdiscount', {
                    id: notif.args.specialist_id,
                    value: 1,
                } ) , 'specialist_'+notif.args.specialist_id);
                resizeChildNode('specialistdiscount_'+notif.args.specialist_id, this.sizeRatio, 1);
                this.recalculateFontSize( 'specialistdiscount_'+notif.args.specialist_id);
                this.placeOnObject( 'specialistdiscount_'+notif.args.specialist_id, 'specialist_'+notif.args.specialist_id);
            }
            this.gamedatas.specialist[notif.args.specialist_id]['discount'] = $('specialistdiscount_'+notif.args.specialist_id).innerText;
            this.constructTooltip('specialist_'+notif.args.specialist_id+'_front', _(this.gamedatas.specialist[notif.args.specialist_id]['nameTr']), this.gamedatas.specialist[notif.args.specialist_id]['text'],$('specialistdiscount_'+notif.args.specialist_id).innerText); 
       },

       notif_chooseResource: function(notif) {
            this.selectedResourcesId.push(notif.args.resource);
            var cs = constructClientState('gather', {'replaceTrigger': notif.args.trigger_replace, 'maxReached': notif.args.max_gather_reached });
            this.setClientState(cs['name'], cs['parameters']);
       },

       notif_takeResource: function(notif) {
           for (var i = 0; i<notif.args.resource_id.length;i++) {
                this.moveResource('resource_'+notif.args.resource_id[i], notif.args.destination[i] );
                //player panel update
                var item_id = notif.args.player_id+'_resCounter_'+ notif.args.resource_type[i];
                $(item_id).innerText =  parseInt($(item_id).innerText) +1;
           }
       },

       notif_returnResource: function(notif){
            for (var i = 0; i<notif.args.resource_id.length;i++) {
                this.moveResource('resource_'+notif.args.resource_id[i], notif.args.destination[i] );
                //player panel update
                var item_id = notif.args.player_id+'_resCounter_'+ notif.args.resource_type[i];
                $(item_id).innerText =  parseInt($(item_id).innerText) -1;
            }
       },

       notif_stealResource: function(notif) {
            this.moveResource(notif.args.resource_id, notif.args.destination );
            //player panel update
            var item_id = notif.args.player_id+'_resCounter_'+ notif.args.resource_type;
            $(item_id).innerText =  parseInt($(item_id).innerText) +1;
            item_id = notif.args.player_id_from+'_resCounter_'+ notif.args.resource_type;
            $(item_id).innerText =  parseInt($(item_id).innerText) -1;
       },

       notif_logInfo: function(notif) {

       },

       notif_expandHand: function(notif) { 
            var slot_number = parseInt($(notif.args.player_id+'_maxhand').innerText);                                                                
            if(notif.args.player_id == this.player_id) {
                for (var i=0;i<notif.args.number;i++) {
                    this.addCardTileOnBoard(slot_number+i, dojo.style('tile_card_0', 'width'), dojo.style('tile_card_0', 'height') );
                }

                if(dojo.hasClass('ebd-body', 'mobile_version')) {
                    if (slot_number+notif.args.number < 10) {
                        resizeNode('player_cards', this.sizeRatio*0.85, this.sizeRatio );
                    } else {
                        resizeNode('player_cards', this.sizeRatio*0.7, this.sizeRatio );
                    }
                } else {
                    if (slot_number+notif.args.number < 10) {
                        resizeNode('player_cards', this.sizeRatio*0.9, this.sizeRatio );
                    } else {
                        resizeNode('player_cards', this.sizeRatio*0.8, this.sizeRatio );
                    }
                }

                var w = (slot_number+notif.args.number)*dojo.style('tile_card_0', 'width')+(slot_number+notif.args.number)*20;
                dojo.style('player_cards', 'width', w+'px');

                $(notif.args.player_id+'_maxhand').innerText = slot_number+notif.args.number;
            } else {
                $(notif.args.player_id+'_maxhand').innerText = slot_number+notif.args.number;
            }
       },

       notif_itemToPlace: function(notif) {
           if (notif.args.item_type == 'room') {
                if( this.gamedatas.rooms[notif.args.item_id].doubleroom) {
                    var room_size = 'doubleroom';
                } else { var room_size = 'single'; }
                if(this.gamedatas.rooms[notif.args.item_id].cathegory == 'master') {
                    var tile_from = 'tile_masterroom_'+this.gamedatas.rooms[notif.args.item_id].location_arg;
                } else { var tile_from = 'tile_room_'+this.gamedatas.rooms[notif.args.item_id].location_arg}

                var cs = constructClientState('placeRoom', {'room_size': room_size, 'possibleTiles': notif.args.possible_tiles, "item_id": notif.args.item_id, "tile_from": tile_from, "two_sided":this.gamedatas.rooms[notif.args.item_id].two_sided });
                this.setClientState(cs['name'], cs['parameters']);
           } else {
                var tile_from = 'tile_specialist_'+this.gamedatas.specialist[notif.args.item_id].location_arg;
                var cs = constructClientState('placeSpecialist', { "possibleTiles": notif.args.possible_tiles, "item_id": notif.args.item_id, "tile_from": tile_from, "cancel" : true });
                this.setClientState(cs['name'], cs['parameters']);
           }

       },

       notif_updateGold: function(notif) {
            $(notif.args.player_id+'_gold').innerText = parseInt($(notif.args.player_id+'_gold').innerText) + notif.args.value
       },

       notif_selectCraftItem: function(notif) {
            this.selectedCraftItem[0] = notif.args.quest_id;
            this.selectedCraftItem[1] = notif.args.item_id;
            if (notif.args.second_item) {
                var cs = constructClientState('craftItem', { "quest_id": notif.args.quest_id, "item_id": notif.args.item_id, "second_item": true, "thug": notif.args.thug_present, "msg": _('Craft second item also?') });
                this.setClientState(cs['name'], cs['parameters']);
            } else {
                if (notif.args.thug_present == null) {
                    dojo.query('.tilequest').removeClass('selection');
                    dojo.query('#tile_quest_'+ notif.args.quest_id+'_'+ notif.args.item_id).addClass('selection');
                } else {
                    var cs = constructClientState('craftItem', { "quest_id": notif.args.quest_id, "item_id": notif.args.item_id, "second_item": false, "thug": notif.args.thug_present, "msg": _('You must choose item to craft') });
                    this.setClientState(cs['name'], cs['parameters']);
                }
            }
       },

       notif_craftItem: function(notif) {
            if (notif.args.quest_completed) {
                //move quest card to player and inc counters
                if (notif.args.sigilForReturn_player != null ) {     
                    this.moveSigil(notif.args.sigilForReturn_player, notif.args.sigilForReturn_player.split("_")[1]+'_sigilplace', false);            
                    this.moveCardAndDestroy(notif.args.quest_id, 'quest', 'overall_player_board_'+notif.args.sigilForReturn_player.split("_")[1]);
                    this.incrementQuestCounters(notif.args.quest_id, notif.args.sigilForReturn_player.split("_")[1]);
                    this.updateGamedatas('quest', notif.args.quest_id, {'location': notif.args.sigilForReturn_player.split("_")[1]});
                } else {
                    this.moveCardAndDestroy(notif.args.quest_id, 'quest', 'overall_player_board_'+notif.args.player_id);
                    this.incrementQuestCounters(notif.args.quest_id, notif.args.player_id);
                    this.updateGamedatas('quest', notif.args.quest_id, {'location': notif.args.player_id});
                }
            }

            if (notif.args.quest) {
                this.moveCardAndDestroy(notif.args.quest_id, 'quest', 'overall_player_board_'+notif.args.player_id);
                this.incrementQuestCounters(notif.args.quest_id, notif.args.player_id);
                this.updateGamedatas('quest', notif.args.quest_id, {'location': notif.args.player_id});
            }

            if (notif.args.sigilToAdd != null) {
                //add sigil to crafted item
                this.moveSigil(notif.args.sigilToAdd, 'tile_quest_'+notif.args.quest_id+"_"+notif.args.item_id, false);
            }
       }, 

        notif_sellTreasureMenu: function(notif) {
            this.addCardSelectionMenu(notif.args.item_number);
        },

        notif_thisPlayerChooseTreasure: function(notif) {
            // move card from selection menu to player hand (or new sell menu) and destroy other
            if ( notif.args.treasure_location.split("_")[2] >= 20 && $('card_menu0')) {
                this.moveItemOnBoard('treasure_'+notif.args.treasure_id_keep, 'tile_card_'+parseInt(parseInt(notif.args.treasure_location.split("_")[2])+20), false);
            } else {
                this.moveItemOnBoard('treasure_'+notif.args.treasure_id_keep, notif.args.treasure_location, false);
            }
            this.moveItemOnBoard('treasure_'+notif.args.treasure_id_give, 'overall_player_board_'+notif.args.player_id_give, true);
            this.constructTooltipTreasure(notif.args.treasure_id_keep, false, false);
            $(notif.args.player_id_give+'_cards'+this.gamedatas.treasure[notif.args.treasure_id_give].color).innerText = parseInt($(notif.args.player_id_give+'_cards'+this.gamedatas.treasure[notif.args.treasure_id_give].color).innerText) +1;
            $(this.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id_give].color).innerText = parseInt($(this.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id_give].color).innerText) -1;
            if (notif.args.destroy_menu) {
                dojo.destroy('card_menu0');
            }
        },

        notif_playerChooseRelics: function(notif) {
            if (notif.args.player_id != this.player_id) {
                for (var i = 0; i<notif.args.relics_keep.length;i++) {
                    this.moveItemOnBoard('treasure_'+notif.args.relics_keep[i], 'overall_player_board_'+notif.args.player_id, true);   
                    $(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.relics_keep[i]].color).innerText = parseInt($(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.relics_keep[i]].color).innerText) +1;
                }
            }
        },

        notif_thisPlayerChooseRelics: function(notif) {
            for (var i = 0; i<notif.args.relics_keep.length;i++) {
                if ( notif.args.relics_positions[i] >= 20 && $('card_menu0')) {
                    this.moveItemOnBoard('treasure_'+notif.args.relics_keep[i], 'tile_card_'+parseInt(notif.args.relics_positions[i]+20), false);
                } else {
                    this.moveItemOnBoard('treasure_'+notif.args.relics_keep[i], 'tile_card_'+notif.args.relics_positions[i], false);
                } 
                $(this.player_id+'_cards'+this.gamedatas.treasure[notif.args.relics_keep[i]].color).innerText = parseInt($(this.player_id+'_cards'+this.gamedatas.treasure[notif.args.relics_keep[i]].color).innerText) +1;
            }

            for (var i = 0; i<notif.args.relics_return.length;i++) {
                this.attachToNewParent('treasure_'+notif.args.relics_return[i], 'discard');
                this.getSmallerTreasureCard(notif.args.relics_return[i]);
                this.moveItemOnBoard('treasure_'+notif.args.relics_return[i], 'discard', false);
            }
         
            if ($('card_menu0') ) {
                dojo.destroy('card_menu0');
            }
        },

        notif_thisPlayerGetTreasureFromPlayer: function(notif) {   
            // move card from one player to another player hand (or sell menu)
            this.updateGamedatas('treasure',notif.args.treasure_id, notif.args.card_info);
            this.drawAndMoveCardToPlayerHand(notif.args.treasure_id, notif.args.treasure_name.replace(/ /g, '').replace(/'/g, ''), this.gamedatas.treasure[notif.args.treasure_id].color, 'overall_player_board_'+notif.args.player_id_from, notif.args.treasure_location);
            // add tooltip
            this.constructTooltipTreasure(notif.args.treasure_id, false, false);
            $(this.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText = parseInt($(this.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText) +1;
            $(notif.args.player_id_from+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText =  parseInt($(notif.args.player_id_from+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText) -1;
        },

        notif_treasureHandle: function(notif) {
            // move card back from one player to another and destroy
            this.addTreasureOnBoard(notif.args.treasure_id2, null, this.gamedatas.treasure[notif.args.treasure_id2].color, 'overall_player_board', notif.args.player_from, 0) 
            this.moveItemOnBoard('treasureback_'+notif.args.treasure_id2, 'overall_player_board_'+notif.args.player_id, true);
            $(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id2].color).innerText = parseInt($(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id2].color).innerText) +1;
            $(notif.args.player_from+'_cards'+this.gamedatas.treasure[notif.args.treasure_id2].color).innerText =  parseInt($(notif.args.player_from+'_cards'+this.gamedatas.treasure[notif.args.treasure_id2].color).innerText) -1;
        },


        notif_sellTreasure: function(notif){
            if(notif.args.player_id == this.player_id) {
                // only move treasure card from hand to discard pile
                this.getSmallerTreasureCard(notif.args.treasure_id);
                this.moveItemOnBoard('treasure_'+notif.args.treasure_id, 'discard', false);
                this.constructTooltipTreasure(notif.args.treasure_id, true, false);
                $(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText =  parseInt($(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText) -1;

                if (dojo.query('#handcontainer .treasurecontainer').length < 1 ) {
                    this.toggleCards(false, false);
                }
            } else {
                // update gamedatas
                this.updateGamedatas('treasure', notif.args.treasure_id, notif.args.treasure_info );
                // add card to player board
                this.addTreasureOnBoard(notif.args.treasure_id, notif.args.treasure_info.name.replace(/ /g, ''), this.gamedatas.treasure[notif.args.treasure_id].color, 'overall_player_board_'+notif.args.player_id, notif.args.player_id,1 )
                // move to discard
                dojo.removeClass('treasure_'+notif.args.treasure_id, 'flipped');
                this.moveItemOnBoard('treasure_'+notif.args.treasure_id, 'discard', false);
                $(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText =  parseInt($(notif.args.player_id+'_cards'+this.gamedatas.treasure[notif.args.treasure_id].color).innerText) - 1;
                // add tooltip
                this.constructTooltipTreasure(notif.args.treasure_id, true, false);
            }
        },


        notif_updateHand: function(notif) {
            this.moveItemOnBoard('treasure_'+notif.args.treasure_id, notif.args.new_location, false);
        },

        notif_reorderRelics: function(notif) {
            for (var i=20;i < (20+notif.args.relics.length); i++) {
                this.enlargeTreasure(notif.args.relics[i-20]);
                this.moveItemOnBoard('treasure_'+notif.args.relics[i-20], 'tile_card_'+i, false);
            }
        },

        notif_moveThug: function(notif) {
                if (notif.args.move_back) {
                    if ($('thugicon')) {
                        // thug move back to guild
                        this.moveItemOnBoard('thugicon', 'specialist_'+this.getThugId(), false );
                    } else {
                        //addonboard
                        this.addThugOnBoard('specialist', this.getThugId() );
                    }
                } else {
                    if (this.player_id!= notif.args.player_id) {
                        //move thug to quest
                        this.moveItemOnBoard('thugicon', 'quest_'+notif.args.quest_id, false);
                    }
                }
        },

        notif_cancelClientState: function(notif) {
            this.restoreServerGameState();
        },

        notif_oracleShow: function(notif) {
            // Create the new dialog over the play zone. You should store the handler in a member variable to access it later
            this.myDlg = new ebg.popindialog();
            this.myDlg.create( 'overseerwindow' );
            this.myDlg.setTitle( _("Oracle reveals") );
            // this.myDlg.setMaxWidth( 500 ); // Optional
            htmlA = ''; htmlB = '';
            // Create the HTML of my dialog. 
            if (notif.args.questCard1 != null ) {
                var htmlA = this.format_block( 'jstpl_overseeritem', { 
                    id: 'overseer1',
                    specType: 'bg-'+notif.args.questCard1.name.replace(/ /g, '').replace(/'/g, ''),
                } );  
            }
            if (notif.args.questCard2 != null ) {
                var htmlB = this.format_block( 'jstpl_overseeritem', { 
                    id: 'overseer2',
                    specType: 'bg-'+notif.args.questCard2.name.replace(/ /g, '').replace(/'/g, ''),
                } ); 
            }
            var wrap = '<div class="overseer_wrap">'+htmlA+htmlB+'</div>';
            // Show the dialog
            this.myDlg.setContent( wrap ); 
            if (notif.args.questCard1 != null ) {
                adjustBackgroundPosition( 'overseer1', this.questSizeCoef);
            }
            if (notif.args.questCard2 != null ) {
                adjustBackgroundPosition( 'overseer2', this.questSizeCoef);
            }
            this.myDlg.show();
        },

        notif_discardFuneral: function(notif) {
            this.moveItemOnBoard('quest_'+notif.args.funeral_id, 'main_board', true);
        },

        notif_updateScore: function(notif) {
            if (notif.args.inc) {
                this.scoreCtrl[ notif.args.player_id].incValue( notif.args.value );
            } else {
                this.scoreCtrl[ notif.args.player_id].setValue( notif.args.value );
            }
        },

        notif_makeOffering: function(notif) {
            //move player sigil
            this.moveSigil(notif.args.sigilToAdd, 'quest_'+notif.args.quest_id, true);
        },

        notif_questAndSigilMovement: function(notif) { 
            this.moveSigil(notif.args.sigil_id, notif.args.player_id+'_sigilplace', false);           
            this.moveCardAndDestroy(notif.args.quest_id, 'quest', 'overall_player_board_'+notif.args.player_id);
            this.incrementQuestCounters(notif.args.quest_id, notif.args.player_id);
            this.updateGamedatas('quest', notif.args.quest_id, {'location': notif.args.player_id});
        },

        notif_reshuffleDeck: function(notif) {
            ordered_ids = this.orderTreasureCards(notif.args.cards);

            for(var i = 0;i<ordered_ids.length;i++) {
                var treasure = notif.args.cards[ ordered_ids[i]];
                // destroy discarded card
                dojo.destroy('treasure_'+treasure.id);
                // update gamedatas
                // this.updateGamedatas('treasure', ordered_ids[i], treasure );
                this.gamedatas.treasure[ordered_ids[i] ] = treasure;
                // add new one to the deck
                this.addTreasureOnBoard(treasure.id, treasure.name, treasure.color, treasure.location, treasure.location_arg, treasure.visible  );
            }

        },

        notif_updateBard: function(notif) {
            this.gamedatas.gamestate.args.possibleTiles = notif.args.tiles;
        },

        notif_resetPotions: function(notif) {
            this.fortunePotionActive = false;
            this.luckyPotionActive = false;
        },

        notif_soloExpand: function(notif) {
            this.gamedatas.soloExpandSecondPart = "1";
        },

        notif_soloKingsFuneral: function(notif) {
            this.gamedatas.soloKingsFuneral = "1";
        }

   });   
});
