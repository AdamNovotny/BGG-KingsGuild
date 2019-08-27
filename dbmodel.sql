
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- kingsguild implementation : © Adam Novotny <adam.novotny.ck@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql
ALTER TABLE `player` ADD `player_mat` VARCHAR(1) NOT NULL;
ALTER TABLE `player` ADD `player_guild` TINYINT UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_hand_size` TINYINT UNSIGNED NOT NULL DEFAULT '6';
ALTER TABLE `player` ADD `player_gold` SMALLINT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_replace_res` VARCHAR(40) NULL;
ALTER TABLE `player` ADD `player_specialist_craftaction` VARCHAR(20) NULL;
ALTER TABLE `player` ADD `player_active_potions` VARCHAR(20) NULL;
ALTER TABLE `player` ADD `player_funeralbid` SMALLINT(20) UNSIGNED NULL;
ALTER TABLE `player` ADD `player_offering` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `room` (
  `room_id` SMALLINT unsigned NOT NULL AUTO_INCREMENT,
  `room_type` SMALLINT NOT NULL,
  `room_location` VARCHAR(20) NOT NULL,
  `room_location_arg` VARCHAR(3) NOT NULL,
  `room_side` TINYINT UNSIGNED NULL,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `specialistandquest` (
  `specialistandquest_id` SMALLINT unsigned NOT NULL AUTO_INCREMENT,
  `specialistandquest_type` VARCHAR(20) NOT NULL,
  `specialistandquest_type_arg` SMALLINT NOT NULL,
  `specialistandquest_location` VARCHAR(20) NOT NULL,
  `specialistandquest_location_arg` VARCHAR(10) NOT NULL,
  `specialistandquest_visible` TINYINT UNSIGNED NOT NULL DEFAULT '0',
  `specialistandquest_discount` TINYINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`specialistandquest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `treasure` (
  `treasure_id` SMALLINT unsigned NOT NULL AUTO_INCREMENT,
  `treasure_type` SMALLINT NOT NULL,
  `treasure_color` VARCHAR(10) NOT NULL,
  `treasure_location` VARCHAR(15) NOT NULL,
  `treasure_location_arg` SMALLINT UNSIGNED NOT NULL,
  `treasure_visible` TINYINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`treasure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tokens` (
  `token_id` SMALLINT unsigned NOT NULL AUTO_INCREMENT,
  `token_type` VARCHAR(15) NOT NULL,
  `token_type_arg` VARCHAR(15) NOT NULL,
  `token_location` VARCHAR(15) NOT NULL,
  `token_location_arg` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


