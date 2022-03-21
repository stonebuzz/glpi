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

use Glpi\Socket;

/**
 * Update from 9.5.5 to 9.5.6
 *
 * @return bool for success (will die for most error)
 **/
function update955to956() {
   /** @global Migration $migration */
   global $DB, $migration, $CFG_GLPI;

   $current_config   = Config::getConfigurationValues('core');
   $updateresult     = true;
   $ADDTODISPLAYPREF = [];

   //TRANS: %s is the number of new version
   $migration->displayTitle(sprintf(__('Update to %s'), '9.5.6'));
   $migration->setVersion('9.5.6');

   // Change DC itemtype template_name search option ID from 50 to 61 to prevent duplicate IDs now that those itemtypes have Infocom search options.
   $migration->changeSearchOption(Enclosure::class, 50, 61);
   $migration->changeSearchOption(PassiveDCEquipment::class, 50, 61);
   $migration->changeSearchOption(PDU::class, 50, 61);
   $migration->changeSearchOption(Rack::class, 50, 61);

   /* Add `date` to some glpi_documents_items */
   if (!$DB->fieldExists('glpi_documents_items', 'date')) {
      $migration->addField('glpi_documents_items', 'date', 'timestamp');
      $migration->addKey('glpi_documents_items', 'date');

      // Init date from the parent followup
      $parent_date = new QuerySubQuery([
         'SELECT' => 'date',
         'FROM' => 'glpi_itilfollowups',
         'WHERE' => [
            'id' => new QueryExpression($DB->quoteName('glpi_documents_items.items_id'))
         ]
      ]);

      $migration->addPostQuery($DB->buildUpdate(
         'glpi_documents_items',
         ['date' => new QueryExpression($parent_date->getQuery())],
         ['itemtype' => ['ITILFollowup']]
      ));

      // Init date as the value of date_creation for others items
      $migration->addPostQuery($DB->buildUpdate(
         'glpi_documents_items',
         ['date' => new QueryExpression($DB->quoteName('glpi_documents_items.date_creation'))],
         ['itemtype' => ['!=', 'ITILFollowup']]
      ));
   }
   /* /Add `date` to glpi_documents_items */

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
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->queryOrDie($query, "10.0 add table glpi_socketmodels");
   }
   
   if (!$DB->tableExists('glpi_cables')) {
      $query = "CREATE TABLE `glpi_cables` (
         `id` int NOT NULL AUTO_INCREMENT,
         `name` varchar(255) DEFAULT NULL,
         `entities_id` int NOT NULL DEFAULT '0',
         `is_recursive` tinyint NOT NULL DEFAULT '0',
         `is_template` tinyint(1) NOT NULL DEFAULT '0',
         `template_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
         `itemtype_endpoint_a` varchar(255) DEFAULT NULL,
         `itemtype_endpoint_b` varchar(255) DEFAULT NULL,
         `items_id_endpoint_a` int NOT NULL DEFAULT '0',
         `items_id_endpoint_b` int NOT NULL DEFAULT '0',
         `socketmodels_id_endpoint_a` int NOT NULL DEFAULT '0',
         `socketmodels_id_endpoint_b` int NOT NULL DEFAULT '0',
         `sockets_id_endpoint_a` int NOT NULL DEFAULT '0',
         `sockets_id_endpoint_b` int NOT NULL DEFAULT '0',
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
         KEY `item_endpoint_a` (`itemtype_endpoint_a`,`items_id_endpoint_a`),
         KEY `item_endpoint_b` (`itemtype_endpoint_b`,`items_id_endpoint_b`),
         KEY `items_id_endpoint_b` (`items_id_endpoint_b`),
         KEY `items_id_endpoint_a` (`items_id_endpoint_a`),
         KEY `socketmodels_id_endpoint_a` (`socketmodels_id_endpoint_a`),
         KEY `socketmodels_id_endpoint_b` (`socketmodels_id_endpoint_b`),
         KEY `sockets_id_endpoint_a` (`sockets_id_endpoint_a`),
         KEY `sockets_id_endpoint_b` (`sockets_id_endpoint_b`),
         KEY `cablestrands_id` (`cablestrands_id`),
         KEY `states_id` (`states_id`),
         KEY `complete` (`entities_id`,`name`),
         KEY `is_recursive` (`is_recursive`),
         KEY `is_template` (`is_template`),
         KEY `users_id_tech` (`users_id_tech`),
         KEY `cabletypes_id` (`cabletypes_id`),
         KEY `date_mod` (`date_mod`),
         KEY `date_creation` (`date_creation`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->queryOrDie($query, "10.0 add table glpi_cables");



      $migration->addField('glpi_states', 'is_visible_cable', 'bool', [
         'value' => 1,
         'after' => 'is_visible_appliance'
      ]);
      $migration->addKey('glpi_states', 'is_visible_cable');
   }

   if (!$DB->fieldExists("glpi_cables", "is_template", false)) {
      $query = "ALTER TABLE `glpi_cables`
               ADD `is_template` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_recursive` ";
      $DB->queryOrDie($query, "4203");
      $migration->addKey('glpi_cables', 'is_template');
   }


   if (!$DB->fieldExists("glpi_cables", "template_name", false)) {
      $query = "ALTER TABLE `glpi_cables`
               ADD `template_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER `is_template` ";
      $DB->queryOrDie($query, "4203");
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
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->queryOrDie($query, "10.0 add table glpi_networkportfiberchanneltypes");
   }
   
   $migration->addField('glpi_networkportfiberchannels', 'networkportfiberchanneltypes_id', 'int', ['after' => 'items_devicenetworkcards_id']);
   $migration->addKey('glpi_networkportfiberchannels', 'networkportfiberchanneltypes_id', 'type');
   
   $ADDTODISPLAYPREF['Socket'] = [5, 6, 9, 8, 7];
   $ADDTODISPLAYPREF['Cable'] = [4, 31, 6, 15, 24, 8, 10, 13, 14];
   
   //rename profilerights values ('netpoint' to 'cable_management')
   $migration->addPostQuery(
      $DB->buildUpdate(
         'glpi_profilerights',
         ['name' => 'cable_management'],
         ['name' => 'netpoint']
      )
   );

   // ************ Keep it at the end **************
   $migration->executeMigration();

   return $updateresult;
}
