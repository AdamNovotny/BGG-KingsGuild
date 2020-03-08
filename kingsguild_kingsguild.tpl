{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- kingsguild implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->


<div id="overall_game_content" style=" transform-origin: left top 0px;">
    <div id="main_board" class="mainboard">

            <div id="masterroomtoggler" class="masterroomtoggler">{MASTERROOMS}</div>

            <div class="masterroomwrap" id="masterromwrap"><div id="masterroombox" class="masterroombox">
            <!-- BEGIN mainBoardMasterTiles -->
                <div id="tile_{TYPE}_{POSITION}" class="tile mastertile" style="left: {LEFT}px; top: {TOP}px; width: {WIDTH}px; height: {HEIGHT}px;"></div>
            <!-- END mainBoardMasterTiles --> 
            </div></div>

            <!-- BEGIN mainBoardTiles -->
                <div id="tile_{TYPE}_{POSITION}" class="tile " style="left: {LEFT}px; top: {TOP}px; width: {WIDTH}px; height: {HEIGHT}px;"></div>
            <!-- END mainBoardTiles --> 

            <div id="discard" class="discardtile"></div>
    </div>

    <div id="playerboardwrap_{THISID}" class="playerboardwrap {SPECTATOR}">

        <div class="guildbackimage {THISGUILD}" id="guildbackimage_{THISID}">
        </div>
        
        <div style="position: absolute; left: 5%; top: 5%">
            <div  class="guildtext" style="position: relative; color: #{THISCOLOR}; font-size: 20px;">{THISNAME}</div>
        </div>

        <div class="showtreasure" id="showcompquests">{SHOW_COMPLETED_QUESTS}</div>

        <div id="player_mat_{THISID}" class="playerboard playerboard{THISMAT}">
            <!-- BEGIN playerBoardTiles -->
                <div id="tile_{TYPE}_{POSITIONX}_{POSITIONY}_{ID}" class="tile tile{TILETYPE} {TILETYPE}{ID}" style="left: {LEFT}px; top: {TOP}px; width: {WIDTH}px; height: {HEIGHT}px;"></div>
            <!-- END playerBoardTiles --> 
            <!-- BEGIN playerBoardDoubleTiles -->
                <div id="doubletile_{TYPE}_{POSITION}_{POSITION2}_{ID}" class="tile tile{TILETYPE} {TILETYPE}{ID}" style="left: {LEFT}px; top: {TOP}px; width: {WIDTH}px; height: {HEIGHT}px;"></div>
            <!-- END playerBoardDoubleTiles --> 
        </div>
    </div>

    <div id="handcontainer" class="handcontainer hidden">
        <div id="left_handcontainer" class="whiteblock handcontainer left">
            <h3>{HAND}</h3>
                <div id="player_cards" class="playercards">
            </div>
        </div>
    </div>


    <div id="rest_boards_wrap" class="restboardswrap">
        <!-- BEGIN playerBoards -->
        <div id="playerboardwrap_{OTHERID}" class="playerboardwrap">
            <div class="guildbackimage {OTHERGUILD}" id="guildbackimage_{OTHERID}">
            </div>
            <div style="position: absolute; left: 5%; top: 5%">
                <div class="guildtext" style="position: relative; color: #{OTHERCOLOR}; font-size: 20px;">{OTHERNAME}</div>
            </div>
            <div id="player_mat_{OTHERID}" class="playerboard playerboard{OTHERMAT}">
                <!-- BEGIN playerBoardTiles2 -->
                    <div id="tile_{TYPE}_{POSITIONX}_{POSITIONY}_{ID}" class="tile tile{TILETYPE} {TILETYPE}{ID}" style="left: {LEFT}px; top: {TOP}px; width: {WIDTH}px; height: {HEIGHT}px;"></div>
                <!-- END playerBoardTiles2 --> 
                <!-- BEGIN playerBoardDoubleTiles2 -->
                    <div id="doubletile_{TYPE}_{POSITION}_{POSITION2}_{ID}" class="tile tile{TILETYPE} {TILETYPE}{ID}" style="left: {LEFT}px; top: {TOP}px; width: {WIDTH}px; height: {HEIGHT}px;"></div>
                <!-- END playerBoardDoubleTiles2 --> 
            </div>
        </div>
        <!-- END playerBoards -->
    </div>    

    <div id="fakeplace" style="width: 0; height:0"></div>
</div>



<script type="text/javascript">

// Javascript HTML templates
var jstpl_resource = '<div class="resource ${type}" id="resource_${id}" style="left: ${left}px; top: ${top}px;"></div>';
var jstpl_room = '<div class="room ${cssType} ${size}" id="room_${id}"></div>';
var jstpl_room_dual ='<div class="roomcontainer ${size}" id="room_${id}">\
                        <div id="room_${id_back}" class="room roomback ${cssBackType} ${size}"><div id="room_${id_back}_flip" class="rotate back"></div></div>\
                        <div id="room_${id_front}" class="room ${cssType} ${size}"><div id="room_${id_front}_flip" class="rotate"></div></div></div>\
                        </div>';
var jstpl_flip = '<div id="room_${id}_flip" class="rotate {side}">';
var jstpl_specialist ='<div class="specialistcontainer specialistbase ${side}" id="specialist_${id}"><div id="specialist_${id}_back" class="specialist specialistback ${cssBackType} specialistbase"></div><div id="specialist_${id}_front" class="specialist ${cssType} specialistbase"></div></div>';
var jstpl_specialistbackonly ='<div id="specialistback_${id}" class="specialist ${cssBackType} specialistbase"></div>';
var jstpl_quest ='<div class="questcontainer ${side}" id="quest_${id}"><div id="quest_${id}_back" class="quest questback ${cssBackType}"></div><div id="quest_${id}_front" class="quest ${cssType}"></div></div>';
var jstpl_questbackonly = '<div id="questback_${id}" class="quest ${cssBackType}"></div>';
var jstpl_treasure ='<div class="treasurecontainer treasurebase ${side}" id="treasure_${id}"><div id="treasure_${id}_back" class="treasure treasureback treasurebase ${cssBackType}"></div><div id="treasure_${id}_front" class="treasure treasurebase ${cssType}"></div></div>';
var jstpl_treasurebackonly = '<div id="treasureback_${id}" class="treasure ${cssBackType} treasurebase"></div>';
var jstpl_rotate = '<div id="rotate_${type}_${id}" class="rotate"></div>';

var jstpl_storagetile = '<div id="tile_storage_${id}_${player_id}" class="tile tilestorage storage${player_id}" style="left: ${left}px; top: ${top}px; width: ${width}px; height: ${height}px;"></div>';
var jstpl_specialisttile = '<div id="tile_specialist_${id}_${player_id}" class="tile tilespecialist specialist${player_id}" style="left: ${left}px; top: ${top}px; width: ${width}px; height: ${height}px;"></div>';
var jstpl_questtile = '<div id="tile_quest_${id}" class="tile tilequest" style="left: ${left}px; top: ${top}px; width: ${width}px; height: ${height}px;"></div>';
var jstpl_player_cardtile = '<div id="tile_card_${id}" class="cardtile" style="width: ${width}px; height: ${height}px;"></div>';
var jstpl_baggage = '<div id="specialist_${id}" class="baggage"></div>';
var jstpl_thug = '<div id="thugicon" class="thugicon"></div>';
var jstpl_sigil = '<div id="sigil_${player}_${id}" class="sigil ${size} ${guild}"></div>';

var jstpl_specialistdiscount = '<div id="specialistdiscount_${id}" class="logitem specialistdiscount">${value}</div>';

var jstpl_player_panel = '\<div class="cp_board">\
    <div class="row1">\
        <div id="${player}_gold" class="cellcount gold biggercell">0</div>\
        <div id="${player}_cardsblue" class="cellcount handcards  blue">0</div>\
        <div id="${player}_cardsred" class="cellcount handcards  red">0</div>\
        <div id="${player}_cardsyellow" class="cellcount handcards  yellow">0</div>\
        <div id="${player}_maxhand" class="cellcount handmaxcards biggercell">0</div>\
        <div id="${player}_sigilplace"></div>\
    </div>\
    <div class="row2" id="${player}_questsCount" >\
        <div class="cellcount mage smallcell" id="${player}_counter_mage"></div>\
        <div class="cell" id="${player}_questCounter_mage">0</div>\
        <div class="cellcount warrior smallcell" id="${player}_counter_warrior"></div>\
        <div class="cell" id="${player}_questCounter_warrior">0</div>\
        <div class="cellcount rogue smallcell" id="${player}_counter_rogue"></div>\
        <div class="cell" id="${player}_questCounter_rogue">0</div>\
        <div class="cellcount weapon smallcell" id="${player}_counter_weapon"></div>\
        <div class="cell" id="${player}_questCounter_weapon">0</div>\
        <div class="cellcount armor smallcell" id="${player}_counter_armor"></div>\
        <div class="cell" id="${player}_questCounter_armor">0</div>\
    </div>\
    <div class="row2" id="${player}_resCount" >\
        <div class="resource panelresource iron"></div>\
        <div class="cell cellres" id="${player}_resCounter_iron">0</div>\
        <div class="resource panelresource wood"></div>\
        <div class="cell cellres" id="${player}_resCounter_wood">0</div>\
        <div class="resource panelresource leather"></div>\
        <div class="cell cellres" id="${player}_resCounter_leather">0</div>\
        <div class="resource panelresource cloth"></div>\
        <div class="cell cellres" id="${player}_resCounter_cloth">0</div>\
        <div class="resource panelresource gem"></div>\
        <div class="cell cellres" id="${player}_resCounter_gem">0</div>\
        <div class="resource panelresource magic"></div>\
        <div class="cell cellres" id="${player}_resCounter_magic">0</div>\
    </div>\
</div>';

var jstpl_card_selector = '<div class="card_menu" id="card_menu${id}" style="width: ${width}px; height: ${height}px;" > <div class="cardmenuslider" id="cardmenuslider${id}">&larr;</div></div>';
var jstpl_overseeritem = '<div id="${id}" class="quest ${specType} tooltipitem"></div>';

var jstpl_tooltipItem = '<div class="tooltipwrap" style="max-width: ${maxWidth}px;">\
    <div class="tooltipHeader" style="max-width: ${maxWidthHeader}px;">${headerText}</div>\
    ${additionalLines}\
    <div class="tooltipMain">${main}</div>\
</div>';


var jstpl_actionbaritem = '<div class="actionbaritem">${item}</div>';
var jstpl_refcarddiv = '<div id="${textId}" class="refcardtext ${s}" style="left: ${left}; top: ${top}; width: ${width}; height: ${height};">${text}</div>';

</script>  

{OVERALL_GAME_FOOTER}
