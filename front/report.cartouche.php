<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015-2016 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.
 
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */


/** @file
* @brief
* @since version 0.84
*/

include ('../inc/includes.php');
global $DB;

Session::checkRight("reports", READ);

Html::header(Report::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "tools", "report");

if (!isset($_GET["id"])) {
   $_GET["id"] = 0;
}

Report::title();

global $DB;

$query = "select c.id as id, c.name, c.ref, t.name as type ,m.name as manufacturer ,l.name as location from glpi_cartridgeitems as c 
join glpi_locations as l on c.locations_id = l.id
join glpi_cartridgeitemtypes as t on c.cartridgeitemtypes_id = t.id
join glpi_manufacturers as m on c.manufacturers_id = m.id";

$result = $DB->query($query);

echo "<table class='tab_cadre'>
         <tr>
            <th class='center'>Nom</th>
            <th class='center'>Référence</th>
            <th class='center'>Type</th>
            <th class='center'>Fabricant</th>
            <th class='center'>Lieu</th>
            <th class='center' colspan='4'>Cartouches</th>
         </tr>";


while ($row = mysqli_fetch_assoc($result)) {
   echo "<tr>";

   echo "<td>".$row['name']."</td>";
   echo "<td>".$row['ref']."</td>";
   echo "<td>".$row['type']."</td>";
   echo "<td>".$row['manufacturer']."</td>";
   echo "<td>".$row['location']."</td>";

   $total  = CartridgeItem::getCount($row['id']);
   $unused = Cartridge::getUnusedNumber($row['id']);
   $used   = Cartridge::getUsedNumber($row['id']);
   $old    = Cartridge::getOldNumber($row['id']);
   
   if($total != 0){
      echo "<th>Total : ".$total." ( ".$unused." neuve(s), ".$used." utilisée(s), ".$old." usagées(s))</th>";
      echo "<th>Statut</th><th>Date de début</th><th>Date de fin</th>";
   }else{
      echo "<th>Aucune cartouche</th>";
      echo "<th></th><th></th><th></th>";
   }

   echo"</tr>";

   if($used > 0){

      for ($i=0; $i < $used; $i++) { 
         $queryUsed = "SELECT id ,date_use,date_out
                   FROM `glpi_cartridges`
                   WHERE (`cartridgeitems_id` = '".$row['id']."'
                          AND `date_use` IS NOT NULL
                          AND `date_out` IS NULL)";
         $resultUsed = $DB->query($queryUsed);

         while ($rowUsed = mysqli_fetch_assoc($resultUsed)) {
            echo "<tr>";
            echo "<td></td>";
            echo "<td></td>";         
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td>En cours d'utilisation</td><td>".$rowUsed['date_use']."</td><td></td>";
            echo "</tr>";

         }
      }
   }

   if($old > 0){

      for ($i=0; $i < $old; $i++) { 
         $queryOld = "SELECT id ,date_use,date_out
                   FROM `glpi_cartridges`
                   WHERE (`cartridgeitems_id` = '".$row['id']."'
                          AND `date_use` IS NOT NULL
                          AND `date_out` IS NOT NULL)";
         $resultOld = $DB->query($queryOld);

         while ($rowOld = mysqli_fetch_assoc($resultOld)) {
            echo "<tr>";
            echo "<td></td>";
            echo "<td></td>";         
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td></td>";
            echo "<td>Usagée</td><td>".$rowOld['date_use']."</td><td>".$rowOld['date_out']."</td>";
            echo "</tr>";

         }

      }
   }
}

echo "</table>";

Html::footer();
?>