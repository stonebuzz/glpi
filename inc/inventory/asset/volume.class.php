<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
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

namespace Glpi\Inventory\Asset;

use \Glpi\Inventory\Conf;

class Volume extends InventoryAsset
{
   public function prepare() :array {
      $serials = [];
      $mapping = [
         'volumn'         => 'device',
         'filesystem'     => 'filesystems_id',
         'total'          => 'totalsize',
         'free'           => 'freesize',
         'encrypt_name'   => 'encryption_tool',
         'encrypt_algo'   => 'encryption_algorithm',
         'encrypt_status' => 'encryption_status',
         'encrypt_type'   => 'encryption_type'
      ];

      foreach ($this->data as $k => &$val) {
         foreach ($mapping as $origin => $dest) {
            if (property_exists($val, $origin)) {
               $val->$dest = $val->$origin;
            }
         }

         if (property_exists($val, 'label') && !empty($val->label)) {
            $val->name = $val->label;
         } else if ((!property_exists($val, 'volumn') || empty($val->volumn))
                  && property_exists($val, 'letter')) {
            $val->name = $val->letter;
         } else if (property_exists($val, 'type')) {
            $val->name = $val->type;
         } else if (property_exists($val, 'volumn')) {
            $val->name = $val->volumn;
         }

         if (!property_exists($val, 'mountpoint')) {
            if (property_exists($val, 'letter')) {
               $val->mountpoint = $val->letter;
            } else if (property_exists($val, 'type')) {
               $val->mountpoint = $val->type;
            }
         }

         if (property_exists($val, 'encryption_status')) {
            //Encryption status
            if ($val->encryption_status == "Yes") {
               $val->encryption_status = \Item_Disk::ENCRYPTION_STATUS_YES;
            } else if ($val->encryption_status == "Partially") {
               $val->encryption_status = \Item_Disk::ENCRYPTION_STATUS_PARTIALLY;
            } else {
               $val->encryption_status = \Item_Disk::ENCRYPTION_STATUS_NO;
            }
         }
      }

      return $this->data;
   }

   public function handle() {
      global $DB;

      $itemDisk = new \Item_Disk();
      $db_itemdisk = [];

      //if ($no_history === false) {
         $iterator = $DB->request([
            'SELECT' => ['id', 'name', 'device', 'mountpoint'],
            'FROM'   => 'glpi_items_disks',
            'WHERE'  => [
               'items_id' => $this->item->fields['id'],
               'itemtype' => $this->item->getType(),
               'is_dynamic'   => 1
            ]
         ]);
         while ($data = $iterator->next()) {
            $dbid = $data['id'];
            unset($data['id']);
            $db_itemdisk[$dbid] = array_map('strtolower', $data);
         }
      //}

      $value = $this->data;
      foreach ($value as $key => $val) {
         $db_elt = [];
         foreach (['name', 'device', 'mountpoint'] as $field) {
            if (property_exists($val, $field)) {
               $db_elt[$key][$field] = strtolower($val->$field);
            }
         }

         foreach ($db_itemdisk as $keydb => $arraydb) {
            if ($db_elt == $arraydb) {
               $input = (array)$val + [
                  'id'           => $keydb,
                  '_no_history'  => true
               ];
               $itemDisk->update($input, false);
               unset($value[$key]);
               unset($db_itemdisk[$keydb]);
               break;
            }
         }
      }

      // TODO: Handle remainings
      if (count($value) || count($db_itemdisk)) {
         if (count($db_itemdisk) != 0) {
            // Delete Item_Disk in DB
            foreach ($db_itemdisk as $dbid => $data) {
               $itemDisk->delete(['id' => $dbid], 1);
            }
         }
         if (count($value)) {
            foreach ($value as $val) {
               $input = (array)$val + [
                  'items_id'     => $this->item->fields['id'],
                  'itemtype'     => $this->item->getType(),
                  'is_dynamic'   => 1/*,
                  '_no_history'  => $no_history*/
               ];

               $itemDisk->add($input, []/*, !$no_history*/);
            }
         }
      }
   }

   public function checkConf(Conf $conf) {
      return $conf->import_volume == 1;
   }
}
