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

class Monitor extends InventoryAsset
{
   private $import_monitor_on_partial_sn = false;

   public function prepare() :array {
      $serials = [];
      $mapping = [
         'caption'      => 'name',
         'manufacturer' => 'manufacturers_id',
         'description'  => 'comment'
      ];

      foreach ($this->data as $k => &$val) {
         foreach ($mapping as $origin => $dest) {
            if (property_exists($val, $origin)) {
               $val->$dest = $val->$origin;
            }
         }

         if (!property_exists($val, 'name')) {
            $val->name = '';
         }

         if (property_exists($val, 'comment')) {
            if ($val->name == '') {
               $val->name = $val->comment;
            }
            unset($val->comment);
         }

         if (!property_exists($val, 'serial')) {
            $val->serial = '';
         }

         if (!property_exists($val, 'manufacturers_id')) {
            $val->manufacturers_id = '';
         }

         if (!isset($serials[$val->serial])) {
            $this->linked_items['Monitor'][] = $val;
            $serials[$val->serial] = 1;
         }
      }

      return $this->data;
   }

   public function handle() {
      global $DB;

      $entities_id = $this->entities_id;
      $monitor = new \Monitor();
      $computer_Item = new \Computer_Item();
      $rule = new \RuleImportComputerCollection();
      $monitors = [];

      foreach ($this->data as $key => $val) {
         $input = [
            'itemtype'     => 'Monitor',
            'name'         => $val->name,
            'serial'       => $val->serial ?? '',
            'is_dynamic'   => 1,
            'entities_id'  => $entities_id
         ];
         $data = $rule->processAllRules($input, [], ['class' => $this, 'return' => true]);

         if (isset($data['found_inventories'])) {
            if ($data['found_inventories'][0] == 0) {
               // add monitor
               $val->entities_id = $entities_id;
               /*$val->otherserial = PluginFusioninventoryToolbox::setInventoryNumber(
                  'Monitor', '', $entities_id);*/
               $monitors[] = $monitor->add((array)$val);
            } else {
               $monitors[] = $data['found_inventories'][0];
            }
            //TODO
            /*if (isset($_SESSION['plugin_fusioninventory_rules_id'])) {
               $pfRulematchedlog = new PluginFusioninventoryRulematchedlog();
               $inputrulelog = [];
               $inputrulelog['date'] = date('Y-m-d H:i:s');
               $inputrulelog['rules_id'] = $_SESSION['plugin_fusioninventory_rules_id'];
               if (isset($_SESSION['plugin_fusioninventory_agents_id'])) {
                  $inputrulelog['plugin_fusioninventory_agents_id'] =
                                 $_SESSION['plugin_fusioninventory_agents_id'];
               }
               $inputrulelog['items_id'] = end($a_monitors);
               $inputrulelog['itemtype'] = "Monitor";
               $inputrulelog['method'] = 'inventory';
               $pfRulematchedlog->add($inputrulelog, [], false);
               $pfRulematchedlog->cleanOlddata(end($a_monitors), "Monitor");
               unset($_SESSION['plugin_fusioninventory_rules_id']);
            }*/
         }
      }

      $db_monitors = [];
      $iterator = $DB->request([
         'SELECT'    => [
            'glpi_monitors.id',
            'glpi_computers_items.id AS link_id'
         ],
         'FROM'      => 'glpi_computers_items',
         'LEFT JOIN' => [
            'glpi_monitors' => [
               'FKEY' => [
                  'glpi_monitors'         => 'id',
                  'glpi_computers_items'  => 'items_id'
               ]
            ]
         ],
         'WHERE'     => [
            'itemtype'                          => 'Monitor',
            'computers_id'                      => $this->item->getType(),
            'entities_id'                       => $entities_id,
            'glpi_computers_items.is_dynamic'   => 1,
            'glpi_monitors.is_global'           => 0
         ]
      ]);

      while ($data = $iterator->next()) {
         $idtmp = $data['link_id'];
         unset($data['link_id']);
         $db_monitors[$idtmp] = $data['id'];
      }

      if (count($db_monitors) == 0) {
         foreach ($monitors as $monitors_id) {
            $input = [
               'computers_id' => $this->item->fields['id'],
               'itemtype'     => 'Monitor',
               'items_id'     => $monitors_id,
               'is_dynamic'   => 1/*,
               '_no_history'  => $no_history*/
            ];
            $computer_Item->add($input, []/*, !$no_history*/);
         }
      } else {
         // Check all fields from source:
         foreach ($monitors as $key => $monitors_id) {
            foreach ($db_monitors as $keydb => $monits_id) {
               if ($monitors_id == $monits_id) {
                  unset($monitors[$key]);
                  unset($db_monitors[$keydb]);
                  break;
               }
            }
         }

         // Delete monitors links in DB
         foreach ($db_monitors as $idtmp => $monits_id) {
            $computer_Item->delete(['id'=>$idtmp], 1);
         }

         foreach ($monitors as $key => $monitors_id) {
            $input = [
               'computers_id' => $this->item->fields['id'],
               'itemtype'     => 'Monitor',
               'items_id'     => $monitors_id,
               'is_dynamic'   => 1/*,
               '_no_history'  => $no_history*/
            ];
            $computer_Item->add($input, []/*, !$no_history*/);
         }
      }

   }

   public function checkConf(Conf $conf) {
      $this->import_monitor_on_partial_sn = $conf->import_monitor_on_partial_sn;
      return true;
   }
}
