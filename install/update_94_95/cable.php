<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2021 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

$default_charset = DBConnection::getDefaultCharset();
$default_collation = DBConnection::getDefaultCollation();

if (!$DB->tableExists('glpi_cabletypes')) {
   $query = "CREATE TABLE `glpi_cabletypes` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_cabletypes");
}

if (!$DB->tableExists('glpi_cablestrands')) {
   $query = "CREATE TABLE `glpi_cablestrands` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_cablestrands");
}

if (!$DB->tableExists('glpi_socketmodels')) {
   $query = "CREATE TABLE `glpi_socketmodels` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET= {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_socketmodels");
}

if (!$DB->tableExists('glpi_cables')) {
   $query = "CREATE TABLE `glpi_cables` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `rear_itemtype` varchar(255) DEFAULT NULL,
      `front_itemtype` varchar(255) DEFAULT NULL,
      `rear_items_id` int NOT NULL DEFAULT '0',
      `front_items_id` int NOT NULL DEFAULT '0',
      `rear_socketmodels_id` int NOT NULL DEFAULT '0',
      `front_socketmodels_id` int NOT NULL DEFAULT '0',
      `rear_sockets_id` int NOT NULL DEFAULT '0',
      `front_sockets_id` int NOT NULL DEFAULT '0',
      `cablestrands_id` int NOT NULL DEFAULT '0',
      `color` varchar(255) DEFAULT NULL,
      `otherserial` varchar(255) DEFAULT NULL,
      `states_id` int NOT NULL DEFAULT '0',
      `users_id_tech` int NOT NULL DEFAULT '0',
      `cabletypes_id` int NOT NULL DEFAULT '0',
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `item_rear` (`itemtype_rear`,`items_id_rear`),
      KEY `item_front` (`itemtype_front`,`items_id_front`),
      KEY `items_id_front` (`items_id_front`),
      KEY `items_id_rear` (`items_id_rear`),
      KEY `socketmodels_id_rear` (`socketmodels_id_rear`),
      KEY `front_socketmodels_id` (`front_socketmodels_id`),
      KEY `rear_sockets_id` (`rear_sockets_id`),
      KEY `front_sockets_id` (`front_sockets_id`),
      KEY `cablestrands_id` (`cablestrands_id`),
      KEY `states_id` (`states_id`),
      KEY `users_id_tech` (`users_id_tech`),
      KEY `cabletypes_id` (`cabletypes_id`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_cables");

   $migration->addField('glpi_states', 'is_visible_cable', 'bool', [
      'value' => 1,
      'after' => 'is_visible_appliance'
   ]);
   $migration->addKey('glpi_states', 'is_visible_cable');
}

if (!$DB->tableExists('glpi_networkportbnctypes')) {
   $query = "CREATE TABLE `glpi_networkportbnctypes` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_sockets");

if (!$DB->tableExists('glpi_networkportbncs')) {
   $query = "CREATE TABLE `glpi_networkportbncs` (
      `id` int NOT NULL AUTO_INCREMENT,
      `networkports_id` int NOT NULL DEFAULT '0',
      `items_devicenetworkcards_id` int NOT NULL DEFAULT '0',
      `networkportbnctypes_id` int NOT NULL DEFAULT '0',
      `speed` int NOT NULL DEFAULT '10' COMMENT 'Mbit/s: 10, 100, 1000, 10000',
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `networkports_id` (`networkports_id`),
      KEY `card` (`items_devicenetworkcards_id`),
      KEY `type` (`networkportbnctypes_id`),
      KEY `speed` (`speed`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
      ) ENGINE = InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_networkportbncs");
}

if (!$DB->tableExists('glpi_sockets') && $DB->tableExists('glpi_netpoints')) {

   //create socket table
   $query = "CREATE TABLE `glpi_sockets` (
      `id` int NOT NULL AUTO_INCREMENT,
      `position` int NOT NULL DEFAULT '0',
      `entities_id` int NOT NULL DEFAULT '0',
      `is_recursive` tinyint NOT NULL DEFAULT '0',
      `locations_id` int NOT NULL DEFAULT '0',
      `name` varchar(255) DEFAULT NULL,
      `socketmodels_id` int NOT NULL DEFAULT '0',
      `wiring_side` tinyint DEFAULT '1',
      `itemtype` varchar(255) DEFAULT NULL,
      `items_id` int NOT NULL DEFAULT '0',
      `networkports_id` int NOT NULL DEFAULT '0',
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `socketmodels_id` (`socketmodels_id`),
      KEY `complete` (`entities_id`,`locations_id`,`name`),
      KEY `is_recursive` (`is_recursive`),
      KEY `location_name` (`locations_id`,`name`),
      KEY `item` (`itemtype`,`items_id`),
      KEY `networkports_id` (`networkports_id`),
      KEY `wiring_side` (`wiring_side`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
    ) ENGINE=InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_sockets");


   //migrate link between NetworkPort and Socket
   // BEFORE : supported by NetworkPortEthernet / NetworkPortFiberchannel with 'sockets_id' foreign key
   // AFTER  : supported by Socket with (itemtype, items_id, networkports_id)
   $classes = [NetworkPortEthernet::getType(), NetworkPortFiberchannel::getType()];
   foreach ($classes as $itemtype) {

      $criteria = [
         'SELECT' => [
            $itemtype:: getTable().".networkports_id",
            $itemtype:: getTable() . ".netpoints_id,
            //load NetPoint infos from SQL because Netpoint PHP class no longer exist (rename To Socket)
            glpi_netpoints.locations_id AS netpoint_locations_id,
            glpi_netpoints.name AS netpoint_name,
            glpi_netpoints.entites_id AS netpoint_entites_id"],
         'FROM'      => $itemtype::getTable(),
         'LEFT JOIN' => [
            'glpi_netpoints' => [
               'FKEY' => [
                  'glpi_netpoints'        => 'id',
                  $itemtype::getTable()   => 'netpoints_id',
               ]
            ]
         ],
         'WHERE' => ['networkports_id'   => ['<>', 0],
         'netpoints_id'      => ['<>', 0]]
      ];

      foreach ($datas as $id => $values) {
         //Load NetworkPort to get associated item
         $networkport = new NetworkPort();
         if ($networkport->getFromDB($values['networkports_id'])) {
            $sockets_id = $values['netpoints_id'];
            $socket = new Socket();
            $socket->add([
               'id'              => $sockets_id,
               'position'        => $networkport->fields['logical_number'],
               'itemtype'        => $networkport->fields['itemtype'],
               'items_id'        => $networkport->fields['items_id'],
               'networkports_id' => $networkport->getID()
            ]);
         }
      }
   }

   //drop table glpi_netpoints
   $migration->dropTable('glpi_netpoints');
}

if (!$DB->tableExists('glpi_networkportfiberchanneltypes')) {
   $query = "CREATE TABLE `glpi_networkportfiberchanneltypes` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255) DEFAULT NULL,
      `comment` text,
      `date_mod` timestamp NULL DEFAULT NULL,
      `date_creation` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `name` (`name`),
      KEY `date_mod` (`date_mod`),
      KEY `date_creation` (`date_creation`)
      ) ENGINE = InnoDB DEFAULT CHARSET = {$default_charset} COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC;";
   $DB->queryOrDie($query, "10.0 add table glpi_networkportfiberchanneltypes");
}

$migration->addField('glpi_networkportfiberchannels', 'networkportfiberchanneltypes_id', 'int', ['after' => 'items_devicenetworkcards_id']);
$migration->addKey('glpi_networkportfiberchannels', 'networkportfiberchanneltypes_id', 'type');

$ADDTODISPLAYPREF['Socket'] = [5, 8, 6, 7];
$ADDTODISPLAYPREF['Cable'] = [4, 31, 6, 15, 24, 8, 10, 13, 14];

//rename profilerights values ('netpoint' to 'cable_management')
$migration->addPostQuery(
   $DB->buildUpdate(
      'glpi_profilerights',
      ['name' => 'cable_management'],
      ['name' => 'netpoint']
   )
);
