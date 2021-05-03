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

namespace Glpi\Inventory\Asset;

use DBmysqlIterator;
use Glpi\Inventory\Conf;
use IPAddress;
use IPNetwork;
use Item_DeviceNetworkCard;
use NetworkName;
use NetworkPort;
use QueryParam;
use Toolbox;
use Unmanaged;

trait InventoryNetworkPort {
   protected $ports = [];
   protected $ipnetwork_stmt;
   protected $idevice_stmt;
   protected $networks = [];
   protected $itemtype;
   private $items_id;

   public function handle() {
      parent::handle();
      $this->handlePorts();
   }

   /**
    * Get network ports
    *
    * @return array
    */
   public function getNetworkPorts() :array {
      return $this->ports;
   }

   private function isMainPartial(): bool {
      if ($this instanceof MainAsset) {
         return $this->isPartial();
      } else {
         if (isset($this->main_asset) && method_exists($this->main_asset, 'isPartial')) {
            return $this->main_asset->isPartial();
         }
      }

      return false;
   }

   /**
    * Manage network ports
    *
    * @param string  $itemtype Item type, will take current item per default
    * @param integer $items_id Item ID, will take current item per default
    *
    * @return void
    */
   public function handlePorts($itemtype = null, $items_id = null) {
      $this->itemtype = $itemtype ?? $this->item->getType();
      $this->items_id = $items_id ?? $this->item->fields['id'];

      if (!$this->isMainPartial()) {
         $this->cleanUnmanageds();
      }
      $this->handleIpNetworks();
      $this->handleUpdates();
      $this->handleCreates();
      if (method_exists($this, 'handleAggregations')) {
         $this->handleAggregations();
      }

      $this->itemtype = null;
      $this->items_id = null;
   }

   /**
    * Handle devices that would no longer be unmanaged
    *
    * @return void
    */
   private function cleanUnmanageds() {
      global $DB;

      $networkport = new NetworkPort();
      $unmanaged = new Unmanaged();

      $criteria = [
         'FROM'   => NetworkPort::getTable(),
         'WHERE'  => [
            'itemtype'  => 'Unmanaged',
            'mac'       => new QueryParam()
         ]
      ];

      $it = new DBmysqlIterator(null);
      $it->buildQuery($criteria);
      $query = $it->getSql();
      $stmt = $DB->prepare($query);

      foreach ($this->ports as $port) {
         if (property_exists($port, 'mac') && $port->mac != '') {
            $stmt->bind_param(
               's',
               $port->mac
            );
            $stmt->execute();
            $results = $stmt->get_result();

            if ($results->num_rows > 0) {
               $row = $results->fetch_object();
               $unmanageds_id = $row->items_id;
               $input = [
                  'logical_number'  => $port->logical_number,
                  'itemtype'        => $this->itemtype,
                  'items_id'        => $this->items_id,
                  'is_dynamic'      => 1,
                  'name'            => $port->name
               ];

               $networkport->update($input, $this->withHistory());
               $unmanaged->delete(['id' => $unmanageds_id], true);
            }
         }
      }
   }

   /**
    * Store IP networks and prepare ports to manage later
    *
    * @return void
    */
   private function handleIpNetworks() {
       global $DB;

      $ipnetwork = new IPNetwork();
      foreach ($this->ports as $port) {
         if (!property_exists($port, 'gateway') || $port->gateway != ''
               || property_exists($port, 'netmask') || $port->netmask != ''
               || property_exists($port, 'subnet') ||  $port->subnet  != ''
         ) {
            continue;
         }

         if ($this->ipnetwork_stmt == null) {
            $criteria = [
               'COUNT'  => 'cnt',
               'FROM'   => IPNetwork::getTable(),
               'WHERE'  => [
                  'entities_id'  => $this->entities_id,
                  'address'      => new QueryParam(),
                  'netmask'      => new QueryParam(),
                  'gateway'      => new QueryParam(),
               ]
            ];

            $it = new DBmysqlIterator(null);
            $it->buildQuery($criteria);
            $query = $it->getSql();
            $stmt = $DB->prepare($query);
            $this->ipnetwork_stmt = $stmt;
         }
         $stmt = $this->ipnetwork_stmt;

         $stmt->bind_param(
            'sss',
            $port->subnet,
            $port->netmask,
            $port->gateway
         );
         $stmt->execute();
         $results = $stmt->get_result();

         $row = $results->fetch_object();
         $count = $row->cnt;

         if ($count == 0) {
            $input = [
               'name'         => sprintf('%s/%s - %s', $port->subnet, $port->netmask, $port->gateway),
               'network'      => sprintf('%s/%s', $port->subnet, $port->netmask),
               'gateway'      => $port->gateway,
               'entities_id'  => $this->entities_id
            ];
            $ipnetwork->add($input, [], $this->withHistory());
         }
      }
   }

   /**
    * Add a network port into dtaabase
    *
    * @param \stdClass $port Port data
    *
    * @return integer
    */
   private function addNetworkPort(\stdClass $port) {
      $networkport = new NetworkPort();

      $input  = (array)$port;
      foreach ($input as $key => $data) {
         if (is_array($data)) {
            unset($input[$key]);
         }
      }
      $input = Toolbox::addslashes_deep($input);
      $input = array_merge(
         $input, [
            'entities_id'  => $this->entities_id,
            'items_id'     => $this->items_id,
            'itemtype'     => $this->itemtype,
            'is_dynamic'   => 1
         ]
      );

      if (!isset($input['trunk']) || empty($input['trunk'])) {
         $input['trunk'] = 0;
      }

      $netports_id = $networkport->add($input, [], $this->withHistory());
      return $netports_id;
   }

   /**
    * Add a network name into database
    *
    * @param integer $items_id Port id
    * @param string  $name     Network name name
    *
    * @return integer
    */
   protected function addNetworkName($items_id, $name = null) {
      $networkname = new NetworkName();
      $input = [
         'entities_id'  => $this->entities_id,
         'is_dynamic'   => 1,
         'items_id'     => $items_id,
         'is_recursive' => 0,
         'itemtype'     => 'NetworkPort'
      ];

      if ($name !== null) {
         $input['name'] = $name;
      }

      $netname_id = $networkname->add($input, [], $this->withHistory());
      return $netname_id;
   }

   /**
    * Add several ip addresses into database
    *
    * @param array   $ips      IP adresses to add
    * @param integer $items_id NetworkName id
    *
    * @return void
    */
   private function addIPAddresses(array $ips, $items_id) {
      $ipaddress = new IPAddress();
      foreach ($ips as $ip) {
         $input = [
            'items_id'     => $items_id,
            'itemtype'     => 'NetworkName',
            'name'         => $ip,
            'is_dynamic'   => 1
         ];
         $ipaddress->add($input, [], $this->withHistory());
      }
   }

   /**
    * Hanlde network instantiation
    *
    * @return void
    */
   private function handleUpdates() {
      global $DB;

      $db_ports = [];
      $networkport = new NetworkPort();

      $iterator = $DB->request([
         'SELECT' => ['id', 'name', 'mac', 'instantiation_type', 'logical_number'],
         'FROM'   => 'glpi_networkports',
         'WHERE'  => [
            'items_id'     => $this->items_id,
            'itemtype'     => $this->itemtype,
            'is_dynamic'   => 1
         ]
      ]);
      while ($row = $iterator->next()) {
         $id = $row['id'];
         unset($row['id']);
         if (is_null($row['mac'])) {
            $row['mac'] = '';
         }
         if (preg_match("/[^a-zA-Z0-9 \-_\(\)]+/", $row['name'])) {
            $row['name'] = Toolbox::addslashes_deep($row['name']);
         }
         $db_ports[$id] = array_map('strtolower', $row);
      }

      $netname_stmt = null;

      foreach ($this->ports as $key => $data) {
         foreach ($db_ports as $keydb => $datadb) {
            //keep trace of logical number from db
            $db_lnumber = $datadb['logical_number'];
            unset($datadb['logical_number']);

            $comp_data = [];
            foreach (['name', 'mac', 'instantiation_type'] as $field) {
               if (property_exists($data, $field)) {
                  $comp_data[$field] = strtolower($data->$field);
               }
            }

            //check if port exists in database
            if ($comp_data != $datadb) {
               continue;
            }

            //check for logical number change
            if (property_exists($data, 'logical_number') && $data->logical_number != $db_lnumber) {
               $networkport->update(
                  [
                     'id'              => $keydb,
                     'logical_number'  => $data->logical_number
                  ],
                  $this->withHistory()
               );
            }

            //handle instantiation type
            if (property_exists($data, 'instantiation_type')) {
               $type = $data->instantiation_type;

               //handle only ethernet and fiberchannel
               $this->handleInstantiation($type, $data, $keydb, true);
            }

            $ips = $data->ipaddress;
            if (count($ips)) {
               //handle network name
               if ($netname_stmt == null) {
                  $criteria = [
                     'SELECT' => 'id',
                     'FROM'   => NetworkName::getTable(),
                     'WHERE'  => [
                        'itemtype'  => 'NetworkPort',
                        'items_id'  => new QueryParam()
                     ]
                  ];

                  $it = new DBmysqlIterator(null);
                  $it->buildQuery($criteria);
                  $query = $it->getSql();
                  $netname_stmt = $DB->prepare($query);
               }

               $netname_stmt->bind_param(
                  's',
                  $keydb
               );
               $netname_stmt->execute();
               $results = $netname_stmt->get_result();

               if ($results->num_rows) {
                  $row = $results->fetch_object();
                  $netname_id = $row->id;
               } else {
                  $netname_id = $this->addNetworkName($keydb);
               }

               //Handle ipaddresses
               $db_addresses = [];
               $iterator = $DB->request([
                  'SELECT' => ['id', 'name'],
                  'FROM'   => 'glpi_ipaddresses',
                  'WHERE'  => [
                     'items_id'  => $netname_id,
                     'itemtype'  => 'NetworkName'
                  ]
               ]);

               while ($db_data = $iterator->next()) {
                  $db_addresses[$db_data['id']] = $db_data['name'];
               }

               foreach ($ips as $ip_key => $ip_data) {
                  foreach ($db_addresses as $db_ip_key => $db_ip_data) {
                     if ($ip_data == $db_ip_data) {
                        unset($ips[$ip_key]);
                        unset($db_addresses[$db_ip_key]);
                        //result found in db, useless to continue
                        break 1;
                     }
                  }
               }

               if (!$this->isMainPartial() && count($db_addresses) && count($ips)) {
                  $ipaddress = new IPAddress();
                  //deleted IP addresses
                  foreach (array_keys($db_addresses) as $id_ipa) {
                     $ipaddress->delete(['id' => $id_ipa], true);
                  }
               }

               if (count($ips)) {
                  $this->addIPAddresses($ips, $netname_id);
               }
            }

            unset($db_ports[$keydb]);
            unset($this->networks[$key]);
            unset($this->ports[$key]);

            $this->portUpdated($data, $keydb);
         }
      }

      //delete remaning network ports, if any
      if (!$this->isMainPartial() && count($db_ports)) {
         foreach ($db_ports as $netpid => $netpdata) {
            if ($netpdata['name'] != 'management') { //prevent removing internal management port
               $networkport->delete(['id' => $netpid], true);
            }
         }
      }
   }

   protected function portUpdated(\stdClass $port, int $netports_id) {
      //does nothing
   }

   /**
    * Handle network port instantiation
    *
    * @param string    $type     Instantiation class name
    * @param \stdClass $data     Data
    * @param integer   $ports_id NetworkPort id
    * @param boolean   $load     Whether to load db results
    *
    * @return void
    */
   private function handleInstantiation($type, $data, $ports_id, $load) {
      global $DB;

      if (!in_array($type, ['NetworkPortEthernet', 'NetworkPortFiberchannel','NetworkPortBnc'])) {
         return;
      }

      $instance = new $type();
      $input = [];

      if ($instance->getFromDB($ports_id)) {
         $input = $instance->fields;
      }
      $input['networkports_id'] = $ports_id;

      if (property_exists($data, 'speed')) {
         $input['speed'] = $data->speed;
         $input['speed_other_value'] = $data->speed;
      }

      if (property_exists($data, 'wwn')) {
         $input['wwn'] = $data->wwn;
      }

      if (property_exists($data, 'mac')) {
         if ($this->idevice_stmt == null) {
            $criteria = [
               'SELECT' => 'id',
               'FROM'   => Item_DeviceNetworkCard::getTable(),
               'WHERE'  => [
                  'itemtype'  => $this->itemtype,
                  'items_id'  => $this->items_id,
                  'mac'       => new QueryParam()
               ]
            ];

            $it = new DBmysqlIterator(null);
            $it->buildQuery($criteria);
            $query = $it->getSql();
            $this->idevice_stmt = $DB->prepare($query);
         }

         $stmt = $this->idevice_stmt;
         $stmt->bind_param(
            's',
            $data->mac
         );
         $stmt->execute();
         $results = $stmt->get_result();

         if ($results->num_rows > 0) {
            $row = $results->fetch_object();
            $input['items_devicenetworkcards_id'] = $row->id;
         }
      }

      //store instance
      if ($instance->isNewItem()) {
         $instance->add($input, [], $this->withHistory());
      } else {
         $instance->update($input, $this->withHistory());
      }
   }

   /**
    * Handle network ports, name and instantiation creation
    *
    * @return void
    */
   private function handleCreates() {
      foreach ($this->ports as $port) {
         $netports_id = $this->addNetworkPort($port);
         if (count($port->ipaddress)) {
            $netnames_id = $this->addNetworkName($netports_id, $port->netname ?? null);
            $this->addIPAddresses($port->ipaddress, $netnames_id);
         }

         if (property_exists($port, 'instantiation_type')) {
            $type = $port->instantiation_type;
            $this->handleInstantiation($type, $port, $netports_id, false);
         }
         $this->portCreated($port, $netports_id);
      }
   }

   protected function portCreated(\stdClass $port, int $netports_id) {
      //does nothing
   }

   public function checkConf(Conf $conf): bool {
      return $conf->component_networkcard == 1;
   }
}
