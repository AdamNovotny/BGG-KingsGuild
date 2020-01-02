/**
 *------
 * kingsguild implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * kgclientstates.js
 *
 * description of client states and some aux functions for kingsguild.js
 * 
 *
 */

function adaptViewportSize(nodes, mainBoardBaseSize, mainBoardRatio,  previousRatio) {
    var pageid = "page-content";

    var bodycoords = dojo.marginBox(pageid);
    var contentWidth = bodycoords.w;

    var spaceForBoard = mainBoardRatio*contentWidth;
    var ratio = spaceForBoard/mainBoardBaseSize;

    for(var i=0;i<nodes.length;i++) {
        if ($(nodes[i])) {
        resizeNode(nodes[i], ratio, previousRatio);
        }
    }

    return ratio;
};

function resizeNode(nodeid, coef, previousRatio) {
    if (nodeid != 'player_cards') {
        dojo.style(nodeid,'width','');
        dojo.style(nodeid,'height','');

        var actW = dojo.style(nodeid,'width');
        var actH = dojo.style(nodeid,'height');
        dojo.style(nodeid,'width', actW*coef+'px');
        dojo.style(nodeid,'height', actH*coef+'px');
    } else {
        var actW = dojo.style(nodeid,'width');
        var actH = dojo.style(nodeid,'height');
        dojo.style(nodeid,'width', actW*coef/previousRatio+'px');
        dojo.style(nodeid,'height', actH*coef/previousRatio+'px');
    }
    if (parseFloat(dojo.style(nodeid,'background-position')) != 0) {
        dojo.style(nodeid,'background-position', '');

        var backpos = dojo.style(nodeid,'background-position');
        var actBx = parseFloat(getBackgroundXPos(backpos));
        var actBy = parseFloat(getBackgroundYPos(backpos));
        dojo.style(nodeid,'background-position', actBx*coef+'px '+actBy*coef+'px');
    }

    var childs = dojo.query('#'+nodeid+' * ');

    for(var i=0;i<childs.length;i++) {
        if (childs[i].id && childs[i].id != 'hinttoggler' && childs[i].id != 'showtreasure' && childs[i].id != 'showcompquests' ) {
            resizeChildNode(childs[i].id, coef, previousRatio);
        }
    }
};

function getBackgroundXPos(backgroundpos) {
    return backgroundpos.split(" ")[0];
}

function getBackgroundYPos(backgroundpos) {
    return backgroundpos.split(" ")[1];
}

function resizeChildNode(nodeid, coef, previousRatio) {
    if (dojo.style(nodeid,'left') != 0) {
        var actL = dojo.style(nodeid,'left');
        dojo.style(nodeid,'left',(actL/previousRatio)*coef+'px');
    }
    if (dojo.style(nodeid,'top') != 0) {
        var actT = dojo.style(nodeid,'top');
        dojo.style(nodeid,'top',(actT/previousRatio)*coef+'px');
    }
    if (dojo.style(nodeid,'width') != 0) {
        var actW = dojo.style(nodeid,'width');
        dojo.style(nodeid,'width',(actW/previousRatio)*coef+'px');
    }
    if (dojo.style(nodeid,'height') != 0) {
        var actH = dojo.style(nodeid,'height');
        dojo.style(nodeid,'height',(actH/previousRatio)*coef+'px'); 
    } 
    
    var backpos = dojo.style(nodeid,'background-position');
    var actBx = parseFloat(getBackgroundXPos(backpos));
    var actBy = parseFloat(getBackgroundYPos(backpos));

    dojo.style(nodeid,'background-position', (actBx/previousRatio)*coef+'px '+(actBy/previousRatio)*coef+'px');
};

function getTileNumberFromResource(resource) {
    var tilestring = "tile_"
    switch (resource) {
        case "iron":
            return tilestring+"baseresource_0";
        case "wood":
            return tilestring+"baseresource_1";
        case "leather":
            return tilestring+"baseresource_2";
        case "cloth":
            return tilestring+"baseresource_3";
        case "gem":
            return tilestring+"advresource_0";
        case "magic":
            return tilestring+"advresource_1";
    }

    return "";
};

function getPositionForResource(resource, coef) {           // random resource placement in the given space
    var parentId = getTileNumberFromResource(resource);
    var actW = dojo.style(parentId,'width');
    var actH = dojo.style(parentId,'height');
    var childs  = dojo.query('#'+parentId+' > * ');

    var xDim = 50*coef;
    var yDim = 50*coef;

    var exclusionSize = 30 ;
    var xExclude = new Array();
    var yExclude = new Array();

    for (var i=0;i<childs.length;i++) {
        xExclude.push(dojo.style(childs[i].id,'left'));
        yExclude.push(dojo.style(childs[i].id,'top'));
    }

    var correctPosition = false;
    var run = 0;
    while(correctPosition == false) {
        var xPos = Math.floor( Math.random()*(actW-xDim+1) );
        var yPos = Math.floor( Math.random()*(actH-yDim+1) );
        correctPosition = true;
        run++;
        for (var i=0;i<childs.length;i++) {
            xOccup = dojo.style(childs[i].id,'left');
            yOccup = dojo.style(childs[i].id,'top');

            if  (xOccup-exclusionSize < xPos && xPos < xOccup+exclusionSize &&  yOccup-exclusionSize < yPos && yPos < yOccup+exclusionSize ) {
                correctPosition = false;
                break;
            }
        }

        if (run>1000) {
            correctPosition=true;
        }
    }
    return [xPos,yPos];
};

function adjustBackgroundPosition(id, coef) {       // adopt background image to given size
    dojo.style(id,'width','');
    dojo.style(id,'height','');
    dojo.style(id,'background-position','');
    
    var backpos = dojo.style(id,'background-position');
    var bX = parseFloat(getBackgroundXPos(backpos))*coef;
    var bY = parseFloat(getBackgroundYPos(backpos))*coef;

    dojo.style(id,'background-position', bX+'px '+bY+'px');
}

function getPercentageTileSize(itemsize, numberOfTiles, tiletype, baseW, baseH) {
    var top = [];
    var left = [];

    switch (tiletype) {
        case 'storage':
          if (itemsize == 'singleroom') {
            var w = (baseW/100)*19.3;
            var h = (baseH/100)*19.3;
            var toppercent = [33.6,52.2,52.2];
            var leftpercent = [40.3,29.2,52.4];

            for(var i=0;i<numberOfTiles;i++) {
                top.push((baseH/100)*toppercent[i]);
                left.push( (baseW/100)*leftpercent[i] );
            }

            return {w: w,h: h,top: top, left: left};
          }

          if (itemsize == 'doubleroom') {
            var w = (baseW/100)*10.6;
            var h = (baseH/100)*20.3;
            var gap  = numberOfTiles == 5 ? 2 : 0.5;
            var leftstart = numberOfTiles == 5 ? 9 : 4.5;

            for(var i=0;i<numberOfTiles;i++) {
                top.push((baseH/100)*56.8);
                left.push( (baseW/100)*leftstart+i*w+i*(baseW/100)*gap );
            }

            return {w: w,h: h,top: top, left: left};
          }

          if (itemsize == 'specialist') {
            var w = (baseW/100)*28;
            var h = (baseH/100)*28;
            var gap  = numberOfTiles == 1 ? 0 : 5;
            var leftstart = numberOfTiles == 1 ? 37 : 22;

            for(var i=0;i<numberOfTiles;i++) {
                top.push((baseH/100)*64);
                left.push( (baseW/100)*leftstart+i*w+i*(baseW/100)*gap );
            }

            return {w: w,h: h,top: top, left: left};
          }

        break;
        case 'specialist':
            if (itemsize == 'singleroom') {
                var w = (baseW/100)*80;
                var h = (baseH/100)*80;

                for(var i=0;i<numberOfTiles;i++) {
                    top.push((baseH/100)*18);
                    left.push( (baseW/100)*10 );
                }

                return {w: w,h: h,top: top, left: left};
            }

            if (itemsize == 'doubleroom') {
                var w = (baseW/100)*41.8;
                var h = (baseH/100)*80;
                var gap  = 4;
                var leftstart = 6;

                for(var i=0;i<numberOfTiles;i++) {
                    top.push((baseH/100)*18);
                    left.push( (baseW/100)*leftstart + i*w+i*(baseW/100)*gap );
                }

                return {w: w,h: h,top: top, left: left};
            }
        break;

        case 'item':
            if (itemsize == 'singleitem') {
                var w = (baseW/100)*56;
                var h = (baseH/100)*36;

                for(var i=0;i<numberOfTiles;i++) {
                    top.push((baseH/100)*59);
                    left.push( (baseW/100)*13);
                }

                return {w: w,h: h,top: top, left: left};
            }

            if (itemsize == 'doubleitem') {
                var w = (baseW/100)*40;
                var h = (baseH/100)*27;
                var gap  = 3;
                var leftstart = 7;

                for(var i=0;i<numberOfTiles;i++) {
                    top.push((baseH/100)*58);
                    left.push( (baseW/100)*leftstart + i*w+i*(baseW/100)*gap );
                }

                return {w: w,h: h,top: top, left: left};
            }
        break;

        default:
          // code block
      }
}

//------------------- client states ---------------------------------------//

function constructClientState(name, args) {
    var clientState = { parameters: {} };
    switch (name) {
        case 'takeResources':
            clientState['name'] = 'client_takeResources';
            clientState['parameters']["descriptionmyturn"] = _('${You} must choose starting resources');
            clientState['parameters']["possibleactions"] = ["takeResources"];
            clientState['parameters']["args"] = {"You": '', "resources": args.actionButtons, "startBonus": true};
        break;

        case 'chooseResources':
            clientState['name'] = 'client_takeResources';
            clientState['parameters']["descriptionmyturn"] = _('${You} must choose which resources take');
            clientState['parameters']["possibleactions"] = ["takeResources"];
            clientState['parameters']["args"] = {"You": '', "resources": args.actionButtons, 'replaceCount': args.triggerReplace};
        break;

        case 'dropRes':
            clientState['name'] = 'client_takeResources';
            clientState['parameters']["descriptionmyturn"] = _('${You} must choose which resource to lose');
            clientState['parameters']["possibleactions"] = ["chooseResource"];
            clientState['parameters']["args"] = {"You": '', "resources": args.actionButtons, "drop" : true };
        break;

        case 'shrineRoom':
            clientState['name'] = 'client_placeRoom';
            clientState['parameters']["descriptionmyturn"] = _('${You} must place Shrine');
            clientState['parameters']["possibleactions"] = ["placeRoom"];
            clientState['parameters']["args"] = {"You": '', "room": 'single', "possibleTiles": args.highligth, "room_id": args.roomId }
        break;

        case 'placeStatue':
            clientState['name'] = 'client_placeRoom';
            clientState['parameters']["descriptionmyturn"] = _('${You} must place King\'s Statue to your guild');
            clientState['parameters']["possibleactions"] = ["placeRoom"];
            clientState['parameters']["args"] = {"You": '', "room": 'single', "possibleTiles": args.possibleTiles, "room_id": args.item_id }
        break;

        case 'placeBaggage':
            clientState['name'] = 'client_placeSpecialist';
            clientState['parameters']["descriptionmyturn"] = _('${You} must place Aristocrat\'s baggage');
            clientState['parameters']["possibleactions"] = ["placeSpecialist"];
            clientState['parameters']["args"] = {"You": '', "possibleTiles": args.possibleTiles, "specialist_id": args.item_id,  "cancel" : false}
        break;

        case 'takeTreasure':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${You} must choose one treasure card');
            clientState['parameters']["possibleactions"] = ["takeTreasure"];
            clientState['parameters']["args"] = {"You": '', "treasure": args.actionButtons, "pass": false}  ;
        break;

        case 'take2treasures':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${You} must choose which treasure cards to take');
            clientState['parameters']["possibleactions"] = ["takeTreasure"];
            clientState['parameters']["args"] = {"You": '', "treasure": args.actionButtons, "pass": false, "cards_sel": args.selected}  ;
        break;

        case 'take3give3':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${You} must choose 3 treasure cards');
            clientState['parameters']["possibleactions"] = ["takeTreasure"];
            clientState['parameters']["args"] = {"You": '', "treasure": args.actionButtons, "pass": false, "merchant": true }  ;
        break;


        case 'appraisertakeTreasure':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${specialist_name}: ${You} can choose one treasure card:');
            clientState['parameters']["possibleactions"] = ["takeTreasure", "pass"];
            clientState['parameters']["args"] = {"You": '',  "specialist_name": args.specialist_name , "treasure": args.actionButtons, "pass": true}  ;
        break;

        case 'takeTreasureFromCardPay':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${You} can pay ${gold} and gain one treasure card:');
            clientState['parameters']["possibleactions"] = ["takeTreasure", "pass"];
            clientState['parameters']["args"] = {"You": '',  "gold": 'gold_1' , "treasure": args.actionButtons, "pass": true}  ;
        break;

        case 'takeTreasureFromCard':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${You} may gain ${gold} and choose one treasure card:');
            clientState['parameters']["possibleactions"] = ["takeTreasure"];
            clientState['parameters']["args"] = {"You": '', "gold": 'gold_1' , "treasure": args.actionButtons, "pass": false}  ;
        break;

        case 'replaceBonus':
            clientState['name'] = 'client_playerGatherAndReplace';
            // clientState['parameters']["descriptionmyturn"] = _('${You} can replace one of your resources by bonus resource ${resource} or pass');
            clientState['parameters']["descriptionmyturn"] = _('${You} need to replace resources to take all bonus resources');
            clientState['parameters']["possibleactions"] = ["chooseResource", "takeResourcesAndReplace", "takeResources", 'cancel'];
            clientState['parameters']["args"] = {"You": '',  "number": args.number, "alreadySelected": args.alreadySelected, "selectedResources": args.selectedResources, "treasureVariant": true, "bonusVariant": true}  ;
        break;

        case 'replaceRes':
            clientState['name'] = 'client_playerGatherAndReplace';
            clientState['parameters']["descriptionmyturn"] = _('${You} need to replace resources to take all treasure card resources');
            clientState['parameters']["possibleactions"] = ["chooseResource", "takeResourcesAndReplace", 'cancel'];
            clientState['parameters']["args"] = {"You": '',  "number": args.number, "alreadySelected": args.alreadySelected, "selectedResources": args.selectedResources, "treasureVariant": true}  ;
        break;

        case 'gatherAndReplace':
            clientState['name'] = 'client_playerGatherAndReplace';
            clientState['parameters']["descriptionmyturn"] =_('${You} must choose ${number} resources to return');
            clientState['parameters']["possibleactions"] = ["chooseResource", "takeResourcesAndReplace", 'cancel', "pass"];
            clientState['parameters']["args"] = {"You": '', "number": args.number, "alreadySelected": args.alreadySelected, "selectedResources": args.selectedResources}  ;
        break;

        case 'gather':
            clientState['name'] = 'client_playerGather';
            clientState['parameters']["descriptionmyturn"] =_('${You} must choose resources to gather');
            clientState['parameters']["possibleactions"] = ["chooseResource", "takeResources", "cancel", "pass"];
            clientState['parameters']["args"] = {"You": '', "replaceTrigger": args.replaceTrigger, "maxReached": args.maxReached}  ;
        break;

        case 'placeRoom':
            clientState['name'] = 'client_placeRoom';
            clientState['parameters']["descriptionmyturn"] = _('${You} must place room to your guild');
            clientState['parameters']["possibleactions"] = ["placeRoom", "cancel"];
            clientState['parameters']["args"] = {"You": '', "room": args.room_size, "possibleTiles": args.possibleTiles, "room_id": args.item_id, "tile_from": args.tile_from, "dualside": args.two_sided, "cancel": true}  ;
        break;

        case 'hireSpecialist':
            clientState['name'] = 'client_hireSpecialist';
            clientState['parameters']["descriptionmyturn"] = _('${You} must select specialist to hire');
            clientState['parameters']["possibleactions"] = ["selectExpandItem"];
            clientState['parameters']["args"] = {"You": '',  }  ;
        break;

        case 'placeSpecialist':
            clientState['name'] = 'client_placeSpecialist';
            clientState['parameters']["descriptionmyturn"] = _('${You} must place specialist to your guild');
            clientState['parameters']["possibleactions"] = ["placeSpecialist", "cancel"];
            clientState['parameters']["args"] = {"You": '', "possibleTiles": args.possibleTiles, "specialist_id": args.item_id, "tile_from": args.tile_from,  "cancel" : args.cancel }  ;
        break;

        case 'selectSpecialistBardAction':
            clientState['name'] = 'client_selectSpecialist';
            clientState['parameters']["descriptionmyturn"] = _('${You} must select specialist');
            clientState['parameters']["possibleactions"] = ["selectSpecialist", "cancel"];
            clientState['parameters']["args"] = {"You": '', "possibleTiles": args.possibleTiles }  ;
        break;

        case 'placeSpecialistBardAction':
            clientState['name'] = 'client_placeSpecialist';
            clientState['parameters']["descriptionmyturn"] = _('${You} must replace specialist');
            clientState['parameters']["possibleactions"] = ["placeSpecialist", "cancel"];
            clientState['parameters']["args"] = {"You": '', "possibleTiles": args.possibleTiles, "specialist_id": args.specialist_id, "tile_from": args.tile_from, "bard": true,  "cancel" : true }  ;
        break;

        case 'traderesources':
            clientState['name'] = 'client_tradeResources';
            clientState['parameters']["descriptionmyturn"] = _('${You} can trade one of your resources');
            clientState['parameters']["possibleactions"] = ["chooseTradeResource", "takeResourcesAndReplace", "pass"];
            clientState['parameters']["args"] = {"You": '', "forSell": args.forSell, "forBuy": args.forBuy, }  ;
        break;

        case 'steal':
            clientState['name'] = 'client_stealResource';
            clientState['parameters']["descriptionmyturn"] = _('${You} must steal one resource from other player');
            clientState['parameters']["possibleactions"] = ["selectResource", "stealResource", "pass"];
            clientState['parameters']["args"] = {"You": '', "selected": args.selected, "replaceTrigger": args.triggerReplace, "selectedForReplace": args.selectedForReplace }  ;
        break;

        case 'craftItem':
            clientState['name'] = 'client_craftItem';
            clientState['parameters']["descriptionmyturn"] = args.msg;
            clientState['parameters']["possibleactions"] = ["craftItem", "confirm", "cancel"];
            clientState['parameters']["args"] = {"you": '', "quest_id": args.quest_id, "item_id": args.item_id, "second_item": args.second_item, "thug": args.thug }  ;
        break;

        case 'playerSellTreasure':
            clientState['name'] = 'client_playerSellTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${you}  must sell ${cards_needed_to_sell} treasure card(s)');
            clientState['parameters']["possibleactions"] = [ "selectTreasureCards", "confirm"];
            clientState['parameters']["args"] = {"you": '', "cards_needed_to_sell": args.cards_needed_to_sell, "possible_treasures": args.possible_treasures, "pass": false }  ;
        break;

        case 'appraiserDiscard':
            clientState['name'] = 'client_playerSellTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${you}  must discard 1 treasure card(s)');
            clientState['parameters']["possibleactions"] = [ "selectTreasureCards", "confirm"];
            clientState['parameters']["args"] = {"you": '', "cards_needed_to_sell": args.cards_needed_to_sell, "possible_treasures": args.possible_treasures, "pass": false }  ;
        break;

        case 'return3cards':
            clientState['name'] = 'client_playerSellTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${you}  must discard ${cards_needed_to_sell} treasure cards');
            clientState['parameters']["possibleactions"] = [ "selectTreasureCards", "confirm"];
            clientState['parameters']["args"] = {"you": '', "cards_needed_to_sell": args.cards_needed_to_sell, "possible_treasures": args.possible_treasures, "pass": false }  ;
        break;

        case 'discardTreasure':
            clientState['name'] = 'client_playerSellTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${you}  must select card to be discarded');
            clientState['parameters']["possibleactions"] = [ "selectTreasureCards", "confirm"];
            clientState['parameters']["args"] = {"you": '', "cards_needed_to_sell": 1, "possible_treasures": args.possible_treasures, "pass": false, "selected_treasure": args.selected }  ;
        break;

        case 'buydiscardrelics':
            clientState['name'] = 'client_playerSellTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${you}  can buy relics for ${gold} ');
            clientState['parameters']["possibleactions"] = [ "selectTreasureCards", "confirm", "pass"];
            clientState['parameters']["args"] = {"you": '', "gold": 'gold_3', "possible_treasures": args.possible_treasures, "pass": true, "dontdestroy": true }  ;
        break;

        case 'goldfortreasure':
            clientState['name'] = 'client_takeTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${specialist_name}: ${you}  can spend ${gold} to recieve treasure card');
            clientState['parameters']["possibleactions"] = [ "takeTreasure", "pass"];
            clientState['parameters']["args"] = {"you": '', "gold": 'gold_1', "specialist_name": args.specialist, "treasure": args.actionButtons, "pass": true}  ;
        break;

        case 'destroyscroll':          
            clientState['name'] = 'client_playerSellTreasure';
            clientState['parameters']["descriptionmyturn"] = _('${specialist_name}: ${you} can destroy one scroll to get both effects of the scroll');
            clientState['parameters']["possibleactions"] = [ "selectTreasureCards", "confirm", "pass"];
            clientState['parameters']["args"] = {"you": '', "specialist_name": args.specialist,  "cards_needed_to_sell": 1, "possible_treasures": args.scrolls, "pass": true}  ;
        break;

        case 'thugaction':           // not done
            clientState['name'] = 'client_placeThug';
            clientState['parameters']["descriptionmyturn"] = _('${specialist_name}: ${you} can place Thug on a quest');
            clientState['parameters']["possibleactions"] = [ "selectQuest", "confirm", "pass"];
            clientState['parameters']["args"] = {"you": '', "specialist_name": args.specialist, 'possibleQuests': args.possibleQuests }  ;
        break;

        case 'craft':           
            clientState['name'] = 'client_playerCraft';
            clientState['parameters']["descriptionmyturn"] = _('${You} can take craft action');
            clientState['parameters']["possibleactions"] = [ "craftItem", "confirm", "pass", "cancel"];
            clientState['parameters']["args"] = {"you": '', }  ;
        break;

        case 'treasureCardPlay':           
            clientState['name'] = 'client_playTreasureCard';
            clientState['parameters']["descriptionmyturn"] = _('Play card effect or sell for gold?');
            clientState['parameters']["possibleactions"] = [ "playTreasureCard", "cancel", "pass"];
            clientState['parameters']["args"] = {'selectedCard': args.selectedCard }  ;
        break;

        case 'select_questcard' :
            clientState['name'] = 'client_selectQuestcard';
            clientState['parameters']["descriptionmyturn"] = _('${You} must select quest to take');
            clientState['parameters']["possibleactions"] = [ "selectQuest", "confirm", "pass"];
            clientState['parameters']["args"] = {'possibleQuests': args.possibleQuests }  ;
        break;

        default:
          // code block
        break;
      }




    return clientState;
}