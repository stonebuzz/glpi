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

use Glpi\Event;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/// Socket class
class Socket extends CommonDropdown {

   // From CommonDBTM
   public $dohistory          = true;

   static $rightname          = 'netpoint';

   public $can_be_translated  = false;

   const REAR    = 1;
   const FRONT   = 2;


   function getAdditionalFields() {

      return [['name'  => 'locations_id',
               'label' => Location::getTypeName(1),
               'type'  => 'dropdownValue',
               'list'  => true],
               ['name'  => 'socketmodels_id',
               'label' => SocketModel::getTypeName(1),
               'type'  => 'dropdownValue',
               'list'  => true],
               ['name'  => 'wiring_side',
               'label' => __('Wiring side'),
               'type'  => ' '],
               ['name'  => 'networkports_id',
               'label' => _n('Network port', 'Network ports', Session::getPluralNumber()),
               'type'  => ' ']];
   }

   function displaySpecificTypeField($ID, $field = []) {
      if ($field['name'] == 'wiring_side') {
         self::dropdownType($field['name'], ['value' => $this->fields['wiring_side']]);
      }

      if ($field['name'] == 'networkports_id') {
         self::showNetworkPortForm($this->fields['itemtype'], $this->fields['items_id'], $this->fields['networkports_id']);
      }
   }

   /**
   * NetworkPort Form
   * @return string ID of the select
   **/
   static function showNetworkPortForm($itemtype, $items_id, $networkports_id = 0) {
      global $CFG_GLPI;

      $rand_itemtype = Dropdown::showFromArray('itemtype', self::getAssets(), ['value'                => $itemtype,
                                                                               'display_emptychoice'  => true]);

      $params = ['itemtype' => '__VALUE__',
                 'action'   => 'getItemsFromItemtype'];
      Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand_itemtype",
                                    "show_items_id_field",
                                    $CFG_GLPI["root_doc"]."/ajax/networkport.php",
                                    $params);

      echo "<span id='show_items_id_field'>";
      if (!empty($itemtype)) {
         $rand_items_id =  $itemtype::dropdown(['name'                  => 'items_id',
                                                'value'                 => $items_id,
                                                'display_emptychoice'   => true]);

         $params = ['items_id'   => '__VALUE__',
                    'itemtype'   => $itemtype,
                    'action'     => 'getNetworkPortFromItem'];

         Ajax::updateItemOnSelectEvent("dropdown_items_id$rand_items_id",
                                       "show_networkport_field",
                                       $CFG_GLPI["root_doc"]."/ajax/networkport.php",
                                       $params);
      }
      echo "</span>";

      echo "<span id='show_networkport_field'>";

      $rand_items_id =  NetworkPort::dropdown(['name'                => 'networkports_id',
                                                'value'               => $networkports_id,
                                                'display_emptychoice' => true,
                                                'condition' => ["items_id" => $items_id,
                                                "itemtype"  => $itemtype]]);

      echo "</span>";

   }

   /**
    * Get sides
    * @return array Array of types
   **/
   static function getAssets() {

      $assets  = [Computer::gettype()           => Computer::getTypeName(),
                  NetworkEquipment::gettype()   => NetworkEquipment::getTypeName(),
                  Peripheral::gettype()         => Peripheral::getTypeName(),
                  Phone::gettype()              => Phone::getTypeName(),
                  Printer::gettype()            => Printer::getTypeName()];

      return $assets;
   }

   /**
    * Dropdown of blacklist types
    *
    * @param string $name   select name
    * @param array  $options possible options:
    *    - value       : integer / preselected value (default 0)
    *    - toadd       : array / array of specific values to add at the beginning
    *    - on_change   : string / value to transmit to "onChange"
    *    - display
    *
    * @return string ID of the select
   **/
   static function dropdownType($name, $options = []) {

      $params = [
         'value'     => 0,
         'toadd'     => [],
         'on_change' => '',
         'display'   => true,
      ];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $items = [];
      if (count($params['toadd'])>0) {
         $items = $params['toadd'];
      }

      $items += self::getSides();

      return Dropdown::showFromArray($name, $items, $params);
   }

   /**
    * Get sides
    * @return array Array of types
   **/
   static function getSides() {

      $options = [
         self::REAR   => __('Rear'),
         self::FRONT  => __('Front'),
      ];

      return $options;
   }


   static function getTypeName($nb = 0) {
      return _n('Socket', 'Sockets', $nb);
   }


   function rawSearchOptions() {
      $tab  = parent::rawSearchOptions();

      $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

      foreach ($tab as &$t) {
         if ($t['id'] == 3) {
            $t['datatype']      = 'itemlink';
            break;
         }
      }

      return $tab;
   }


   /**
    * Handled Multi add item
    *
    * @since 0.83 (before addMulti)
    *
    * @param $input array of values
   **/
   function executeAddMulti(array $input) {

      $this->check(-1, CREATE, $input);
      for ($i=$input["_from"]; $i<=$input["_to"]; $i++) {
         $input["name"] = $input["_before"].$i.$input["_after"];
         $this->add($input);
      }
      Event::log(0, "dropdown", 5, "setup",
                 sprintf(__('%1$s adds several sockets'), $_SESSION["glpiname"]));
   }


   /**
    * Print out an HTML "<select>" for a dropdown with preselected value
    *
    * @param string  $myname             the name of the HTML select
    * @param integer $value              the preselected value we want (default 0)
    * @param integer $locations_id       default location ID for search (default -1)
    * @param boolean $display_comment    display the comment near the dropdown (default 1)
    * @param integer $entity_restrict    Restrict to a defined entity(default -1)
    * @param string  $devtype            (default '')
    *
    * @return integer random part of elements id
   **/
   static function dropdownSocket($myname, $value = 0, $locations_id = -1, $display_comment = 1,
                                    $entity_restrict = -1, $devtype = '') {
      global $CFG_GLPI;

      $rand          = mt_rand();
      $name          = Dropdown::EMPTY_VALUE;
      $comment       = "";
      if (empty($value)) {
         $value = 0;
      }
      if ($value > 0) {
         $tmpname = Dropdown::getDropdownName("glpi_sockets", $value, 1);
         if ($tmpname["name"] != "&nbsp;") {
            $name          = $tmpname["name"];
            $comment       = $tmpname["comment"];
         }
      }

      $field_id = Html::cleanId("dropdown_".$myname.$rand);
      $param    = ['value'             => $value,
                   'valuename'         => $name,
                   'entity_restrict'   => $entity_restrict,
                   'devtype'           => $entity_restrict,
                   'locations_id'      => $locations_id];
      echo Html::jsAjaxDropdown($myname, $field_id,
                                $CFG_GLPI['root_doc']."/ajax/getDropdownSocket.php",
                                $param);

      // Display comment
      if ($display_comment) {
         $comment_id = Html::cleanId("comment_".$myname.$rand);
         Html::showToolTip($comment, ['contentid' => $comment_id]);

         $item = new self();
         if ($item->canCreate()) {
            echo "<span class='fa fa-plus pointer' title=\"".__s('Add')."\" ".
                  "onClick=\"".Html::jsGetElementbyID('socket'.$rand).".dialog('open');\">" .
                  "<span class='sr-only'>" . __s('Add') . "</span></span>";
            Ajax::createIframeModalWindow('socket'.$rand,
                                          $item->getFormURL());

         }
         $paramscomment = [
            'value'       => '__VALUE__',
            'itemtype'    => Socket::getType(),
            '_idor_token' => Session::getNewIDORToken("Socket")
         ];
         echo Ajax::updateItemOnSelectEvent($field_id, $comment_id,
                                            $CFG_GLPI["root_doc"]."/ajax/comments.php",
                                            $paramscomment, false);
      }
      return $rand;
   }


   /**
    * check if a socket already exists (before import)
    *
    * @param $input array of value to import (name, locations_id, entities_id)
    *
    * @return integer the ID of the new (or -1 if not found)
   **/
   function findID(array &$input) {
      global $DB;

      if (!empty($input["name"])) {
         $iterator = $DB->request([
            'SELECT' => 'id',
            'FROM'   => $this->getTable(),
            'WHERE'  => [
               'name'         => $input['name'],
               'locations_id' => $input["locations_id"] ?? 0
            ] + getEntitiesRestrictCriteria($this->getTable(), $input['entities_id'], $this->maybeRecursive())
         ]);

         // Check twin :
         if (count($iterator)) {
            $result = $iterator->next();
            return $result['id'];
         }
      }
      return -1;
   }


   function post_addItem() {

      $parent = $this->fields['locations_id'];
      if ($parent) {
         $changes[0] = '0';
         $changes[1] = '';
         $changes[2] = addslashes($this->getNameID());
         Log::history($parent, 'Location', $changes, $this->getType(), Log::HISTORY_ADD_SUBITEM);
      }

   }

   function post_deleteFromDB() {

      $parent = $this->fields['locations_id'];
      if ($parent) {
         $changes[0] = '0';
         $changes[1] = addslashes($this->getNameID());
         $changes[2] = '';
         Log::history($parent, 'Location', $changes, $this->getType(), Log::HISTORY_DELETE_SUBITEM);
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         $nb = 0;
         switch ($item->getType()) {
            case 'Location' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb =  countElementsInTable($this->getTable(),
                                              ['locations_id' => $item->getID()]);
               }
               return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Location') {
         self::showForLocation($item);
      }
      return true;
   }


   /**
    * Print the HTML array of the Socket associated to a Location
    *
    * @param $item Location
    *
    * @return void
   **/
   static function showForLocation($item) {
      global $DB;

      $ID       = $item->getField('id');
      $socket = new self();
      $item->check($ID, READ);
      $canedit  = $item->canEdit($ID);

      if (isset($_GET["start"])) {
         $start = intval($_GET["start"]);
      } else {
         $start = 0;
      }
      $number = countElementsInTable('glpi_sockets', ['locations_id' => $ID ]);

      if ($canedit) {
         echo "<div class='first-bloc'>";
         // Minimal form for quick input.
         echo "<form action='".$socket->getFormURL()."' method='post'>";
         echo "<br><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2 center'>";
         echo "<td class='b'>"._n('Network socket', 'Network sockets', 1)."</td>";
         echo "<td>".__('Name')."</td><td>";
         Html::autocompletionTextField($item, "name", ['value' => '']);
         echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
         echo "<input type='hidden' name='locations_id' value='$ID'></td>";
         echo "<td><input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>\n";
         echo "</table>\n";
         Html::closeForm();

         // Minimal form for massive input.
         echo "<form action='".$socket->getFormURL()."' method='post'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2 center'>";
         echo "<td class='b'>"._n('Network socket', 'Network sockets', Session::getPluralNumber())."</td>";
         echo "<td>".__('Name')."</td><td>";
         echo "<input type='text' maxlength='100' size='10' name='_before'>&nbsp;";
         Dropdown::showNumber('_from', ['value' => 0,
                                             'min'   => 0,
                                             'max'   => 400]);
         echo "&nbsp;-->&nbsp;";
         Dropdown::showNumber('_to', ['value' => 0,
                                           'min'   => 0,
                                           'max'   => 400]);
         echo "&nbsp;<input type='text' maxlength='100' size='10' name='_after'><br>";
         echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
         echo "<input type='hidden' name='locations_id' value='$ID'>";
         echo "<input type='hidden' name='_method' value='AddMulti'></td>";
         echo "<td><input type='submit' name='execute' value=\""._sx('button', 'Add')."\"
                    class='submit'>";
         echo "</td></tr>\n";
         echo "</table>\n";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";

      if ($number < 1) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>".self::getTypeName(1)."</th>";
         echo "<th>".__('No item found')."</th></tr>";
         echo "</table>\n";
      } else {
         Html::printAjaxPager(sprintf(__('Network sockets for %s'), $item->getTreeLink()),
                              $start, $number);

         if ($canedit) {
            $rand = mt_rand();
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams
               = ['num_displayed'
                           => min($_SESSION['glpilist_limit'], $number),
                       'container'
                           => 'mass'.__CLASS__.$rand,
                       'specific_actions'
                           => ['purge' => _x('button', 'Delete permanently')]];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixe'><tr>";

         if ($canedit) {
            echo "<th width='10'>";
            echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            echo "</th>";
         }

         echo "<th>".__('Name')."</th>"; // Name
         echo "<th>".__('Comments')."</th>"; // Comment
         echo "</tr>\n";

         $crit = ['locations_id' => $ID,
                       'ORDER'        => 'name',
                       'START'        => $start,
                       'LIMIT'        => $_SESSION['glpilist_limit']];

         Session::initNavigateListItems('Socket',
         //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         foreach ($DB->request('glpi_sockets', $crit) as $data) {
            Session::addToNavigateListItems('Socket', $data["id"]);
            echo "<tr class='tab_bg_1'>";

            if ($canedit) {
               echo "<td>".Html::getMassiveActionCheckBox(__CLASS__, $data["id"])."</td>";
            }

            echo "<td><a href='".$socket->getFormURL();
            echo '?id='.$data['id']."'>".$data['name']."</a></td>";
            echo "<td>".$data['comment']."</td>";
            echo "</tr>\n";
         }

         echo "</table>\n";

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         Html::printAjaxPager(sprintf(__('Network sockets for %s'), $item->getTreeLink()),
                              $start, $number);

      }

      echo "</div>\n";
   }


   /**
    * @since 0.84
    *
    * @param $itemtype
    * @param $base            HTMLTableBase object
    * @param $super           HTMLTableSuperHeader object (default NULL
    * @param $father          HTMLTableHeader object (default NULL)
    * @param $options   array
   **/
   static function getHTMLTableHeader($itemtype, HTMLTableBase $base,
                                      HTMLTableSuperHeader $super = null,
                                      HTMLTableHeader $father = null, array $options = []) {

      $column_name = __CLASS__;

      if (isset($options['dont_display'][$column_name])) {
         return;
      }

      $base->addHeader($column_name, _n('Network socket', 'Network sockets', 1), $super, $father);

   }


   /**
    * @since 0.84
    *
    * @param $row             HTMLTableRow object (default NULL)
    * @param $item            CommonDBTM object (default NULL)
    * @param $father          HTMLTableCell object (default NULL)
    * @param $options   array
   **/
   static function getHTMLTableCellsForItem(HTMLTableRow $row = null, CommonDBTM $item = null,
                                            HTMLTableCell $father = null, $options = []) {

      $column_name = __CLASS__;

      if (isset($options['dont_display'][$column_name])) {
         return;
      }

      $row->addCell($row->getHeaderByName($column_name),
                    Dropdown::getDropdownName("glpi_sockets", $item->fields["sockets_id"]),
                    $father);
   }

}
