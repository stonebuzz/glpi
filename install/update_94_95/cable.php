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

/**
 * @var DB $DB
 * @var Migration $migration
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

if (!$DB->tableExists('glpi_sockets')) {

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

}

if ($DB->tableExists('glpi_netpoints')) {

   //migrate link between NetworkPort and Socket
   // BEFORE : supported by NetworkPortEthernet / NetworkPortFiberchannel with 'netpoints_id' foreign key
   // AFTER  : supported by Socket with (itemtype, items_id, networkports_id)
   $classes = [NetworkPortEthernet::getType(), NetworkPortFiberchannel::getType()];
   foreach ($classes as $itemtype) {
      if (!$DB->fieldExists($itemtype::getTable(), 'netpoints_id')) {
         continue;
      }
      $criteria = [
         'SELECT' => [
            "glpi_networkports.id AS networkports_id",
            "glpi_networkports.logical_number",
            "glpi_networkports.itemtype",
            "glpi_networkports.items_id",
            "glpi_netpoints.locations_id",
            "glpi_netpoints.name",
            "glpi_netpoints.entities_id",
            "glpi_netpoints.date_creation",
            "glpi_netpoints.date_mod",
         ],
         'FROM'      => $itemtype::getTable(),
         'INNER JOIN' => [
            'glpi_networkports' => [
               'FKEY' => [
                  'glpi_networkports'     => 'id',
                  $itemtype::getTable()   => 'networkports_id',
               ]
            ],
            'glpi_netpoints' => [
               'FKEY' => [
                  'glpi_netpoints'        => 'id',
                  $itemtype::getTable()   => 'netpoints_id',
               ]
            ],
         ]
      ];

      $iterator = $DB->request($criteria);

      while ($data = $iterator->next()) {
         $socket = new Socket();
         $input = [
            'name'            => $data['name'],
            'entities_id'     => $data['entities_id'],
            'locations_id'    => $data['locations_id'],
            'position'        => $data['logical_number'],
            'itemtype'        => $data['itemtype'],
            'items_id'        => $data['items_id'],
            'networkports_id' => $data['networkports_id'],
            'date_creation'   => $data['date_creation'],
            'date_mod'        => $data['date_mod'],
         ];

         $socket->add($input);
      }
   }
   //remove "useless "netpoints_id" field
   $migration->dropField('glpi_networkportethernets', 'netpoints_id');
   $migration->dropField('glpi_networkportfiberchannels', 'netpoints_id');
}

//drop table glpi_netpoints
$migration->dropTable('glpi_netpoints');

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
