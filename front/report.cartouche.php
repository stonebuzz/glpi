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


echo '<a style="padding-left:300px;"" href="#" class="export">Export CSV</a>';


echo "<div id='dvData'>";
echo "<table class='tab_cadre'>
         <tr>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Nom</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Référence</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Type</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Fabricant</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Lieu</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Statut</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Date d'entrée</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Date de début d'utilisation</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Date de fin d'utilisation</td>
         </tr>";


while ($row = mysqli_fetch_assoc($result)) {
   echo "<tr>";

   echo "<td class='center' style='font-weight:bold;'>".$row['name']."</td>";
   echo "<td class='center' style='font-weight:bold;'>".$row['ref']."</td>";
   echo "<td class='center' style='font-weight:bold;'>".$row['type']."</td>";
   echo "<td class='center' style='font-weight:bold;'>".$row['manufacturer']."</td>";
   echo "<td class='center' style='font-weight:bold;'>".$row['location']."</td>";

   $total  = CartridgeItem::getCount($row['id']);
   $unused = Cartridge::getUnusedNumber($row['id']);
   $used   = Cartridge::getUsedNumber($row['id']);
   $old    = Cartridge::getOldNumber($row['id']);

   echo "<td></td><td></td><td></td><td></td>";


   echo"</tr>";

   if($used > 0){

      $queryUsed = "SELECT id ,date_use,date_out,date_in
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

         echo "<td class='center'>En cours d'utilisation</td><td class='center'>".$rowUsed['date_in']."</td><td class='center'>".$rowUsed['date_use']."</td><td></td>";
         echo "</tr>";

      }

   }

   if($old > 0){

      $queryOld = "SELECT id ,date_use,date_out,date_in
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
   
         echo "<td class='center'>Usagée</td><td class='center'>".$rowOld['date_in']."</td><td class='center'>".$rowOld['date_use']."</td><td class='center'>".$rowOld['date_out']."</td>";
         echo "</tr>";

      }
   }

   if($unused > 0){

      $queryUnused = "SELECT id , date_in
             FROM `glpi_cartridges`
             WHERE (`cartridgeitems_id` = '".$row['id']."'
                    AND `date_use` IS NULL
                    AND `date_out` IS NULL)";
      $resultUnused = $DB->query($queryUnused);

      while ($rowUnused = mysqli_fetch_assoc($resultUnused)) {
         echo "<tr>";
         echo "<td></td>";
         echo "<td></td>";         
         echo "<td></td>";
         echo "<td></td>";
         echo "<td></td>";

         echo "<td class='center'>Neuve</td><td class='center'>".$rowUnused['date_in']."</td><td></td><td></td>";
         echo "</tr>";

      }
   }

   echo "<tr>";
   echo "<td></td>";
   echo "<td></td>";
   echo "<td></td>";
   echo "<td></td>";
   echo "<td></td>";

   if($total != 0){
      echo "<td class='center' colspan='5' style='font-weight:bold;'>Total : ".$total." ( ".$unused." neuve(s), ".$used." utilisée(s), ".$old." usagées(s))</td>";
   }else{
      echo "<td class='center' colspan='5' style='font-weight:bold;'>Aucune cartouche</td>";
   }

   echo"</tr>";
}

echo "</table>";
echo "</div>";

$export = <<<MY_MARKER
<script type="text/javascript">
$(document).ready(function () {

    function exportTableToCSV(table, filename) {
var BOM = "\uFEFF";
        var rows = table.find('tr:has(td)'),

            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\\r\\n"',

            // Grab text from table into CSV formatted string
            csv = '"' + rows.map(function (i, row) {
                var row = $(row),
                    cols = row.find('td');

                return cols.map(function (j, col) {
                    var col = $(col),
                        text = col.text();

                    return text.replace(/"/g, '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '"';

            // Deliberate 'false', see comment below
        if (false && window.navigator.msSaveBlob) {

                  var blob = new Blob([BOM +decodeURIComponent(csv)], {
                 type: 'text/csv;charset=utf8'
            });
            
            // Crashes in IE 10, IE 11 and Microsoft Edge
            // See MS Edge Issue #10396033: https://goo.gl/AEiSjJ
            // Hence, the deliberate 'false'
            // This is here just for completeness
            // Remove the 'false' at your own risk
            window.navigator.msSaveBlob(blob, filename);
            
        } else if (window.Blob && window.URL) {
                  // HTML5 Blob        
            var blob = new Blob([BOM +csv], { type: 'data:application/csv;charset=utf-8' });
            var csvUrl = URL.createObjectURL(blob);

            $(this)
                  .attr({
                     'download': filename,
                     'href': csvUrl
                  });
            } else {
            // Data URI
            var csvData = 'data:application/csv;charset=utf-8,' + BOM +encodeURIComponent(csv);

                  $(this)
                .attr({
                       'download': filename,
                    'href': csvData,
                    'target': '_blank'
                  });
        }
    }

    // This must be a hyperlink
    $(".export").on('click', function (event) {
         console.log('ok');
        // CSV
        var args = [$('#dvData>table'), 'export.csv'];
        
        exportTableToCSV.apply(this, args);
        
        // If CSV, don't do event.preventDefault() or return false
        // We actually need this to be a typical hyperlink
    });
});


</script>
MY_MARKER;

echo $export;

Html::footer();
?>