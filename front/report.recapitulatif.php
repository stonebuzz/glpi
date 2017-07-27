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



$debutT1 = date("Y")."-01-01";
$finT1   = date("Y")."-03-31";

$debutT2 = date("Y")."-04-01";
$finT2   = date("Y")."-06-31";

$debutT3 = date("Y")."-07-01";
$finT3   = date("Y")."-09-31";

$debutT4 = date("Y")."-10-01";
$finT4   = date("Y")."-12-31";

$debutS1 = date("Y")."-01-01";
$finS1   = date("Y")."-06-31";

$debutS2 = date("Y")."-07-01";
$finS2   = date("Y")."-12-31";


$t = trimestre();
$startT = ${"debut".$t};
$endT   = ${"fin".$t};



$s = semestre();
$startS = ${"debut".$s};
$endS   = ${"fin".$s};





echo '<a style="padding-left:300px;"" href="#" class="export">Export CSV</a>';


echo "<div id='dvData'>";
echo "<table class='tab_cadre'>
         <tr>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>".date("Y/m/d")."</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Mode de calcul</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Résultats</td>
            <td style='font-weight:bold; background-color: #F1F1F1;' class='center'>Observations</td>
         </tr>";


//total des interventions
$query = "select count(id) as count from glpi_tickets where date between '".$startT."' and '".$endT."' ";
$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $totalInter = $row['count'];
}

//total intervention dans les délais
$query = "select count(id) as count from glpi_tickets 
where date between '".$startT."' and '".$endT."'
and `due_date` IS NOT NULL
and `status` <> 4
AND (`solvedate` < `due_date`
OR  (`solvedate` IS NULL AND `due_date` < NOW()))";
$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $totalInterOK = $row['count'];
}

$percent = (100 * $totalInterOK) / $totalInter;


echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre interventions réalisées dans les delais <br> Nombre total de demandes d'intervention</td>";
echo "<td>".$percent."%</td>";
echo "<td></td>";
echo "</tr>";


//??????
echo "<tr>";
echo "<td>Semestrielle</td>";
echo "<td>Nombre de poste de + de 5 ans renouvelés <br> Nombre total de poste de plus de 5 ans</td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";


$query = "
   SELECT count(c_3.id) as count
   FROM glpi_computers as c_3
   LEFT JOIN glpi_states st ON c_3.states_id = st.id
   LEFT JOIN glpi_infocoms as i_5 ON i_5.itemtype     = 'Computer' AND i_5.items_id   = c_3.id AND DATE_ADD(i_5.warranty_date, INTERVAL i_5.warranty_duration MONTH) >= NOW()
   WHERE st.name = 'EN FONCTION'
   AND c_3.is_deleted = 0";
$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $affected = $row['count'];
}


echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre total d'ordinateurs <br> affectés sous garantie</td>";
echo "<td>".$affected."</td>";
echo "<td></td>";
echo "</tr>";



$query = "SELECT count(c_3.id) as count
   FROM glpi_computers as c_3
   LEFT JOIN glpi_states st ON c_3.states_id = st.id
   LEFT JOIN glpi_infocoms as i_35 ON i_35.itemtype = 'Computer' AND i_35.items_id = c_3.id AND i_35.use_date BETWEEN DATE_SUB(NOW(),INTERVAL 5 YEAR) AND DATE_SUB(NOW(),INTERVAL 3 YEAR) 
   WHERE st.name = 'EN FONCTION'
   AND c_3.is_deleted = 0";
$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $affected = $row['count'];
}


echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre total d'ordinateurs affectés de 3 à 5 ans</td>";
echo "<td>".$affected."</td>";
echo "<td></td>";
echo "</tr>";


$query = "SELECT count(c_3.id) as count
   FROM glpi_computers as c_3
   LEFT JOIN glpi_states st ON c_3.states_id = st.id
   LEFT JOIN glpi_infocoms as i_35 ON i_35.itemtype = 'Computer' AND i_35.items_id = c_3.id AND i_35.use_date > DATE_SUB(NOW(),INTERVAL 5 YEAR)  
   WHERE st.name = 'EN FONCTION'
   AND c_3.is_deleted = 0";
$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $affected = $row['count'];
}


echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre total d'ordinateurs affectés de plus de 5ans </td>";
echo "<td>".$affected."</td>";
echo "<td></td>";
echo "</tr>";


$query = "SELECT count(m.id) as count
    from  glpi_monitors as m 
  LEFT JOIN glpi_states st ON m.states_id = st.id
   LEFT JOIN glpi_infocoms i_w ON i_w.itemtype = 'Monitor' AND i_w.items_id = m.id AND DATE_ADD(i_w.warranty_date, INTERVAL i_w.warranty_duration MONTH) >= NOW()
   WHERE st.name = 'EN FONCTION'
   AND m.is_deleted = 0
";

$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $affected = $row['count'];
}



echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre total de moniteurs affectés sous garantie</td>";
echo "<td>".$affected."</td>";
echo "<td></td>";
echo "</tr>";

$query = "SELECT count(m.id) as count
    from  glpi_printers as m 
  LEFT JOIN glpi_states st ON m.states_id = st.id
   LEFT JOIN glpi_infocoms i_w ON i_w.itemtype = 'Printer' AND i_w.items_id = m.id AND DATE_ADD(i_w.warranty_date, INTERVAL i_w.warranty_duration MONTH) >= NOW()
   WHERE st.name = 'EN FONCTION'
   AND m.is_deleted = 0
";

$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $affected = $row['count'];
}

echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre total d'imprimantes en service sous garantie</td>";
echo "<td>".$affected."</td>";
echo "<td></td>";
echo "</tr>";


$query = "SELECT count(m.id) as count
    from  glpi_printers as m 
    LEFT JOIN glpi_printertypes as pt ON m.printertypes_id = pt.id
  LEFT JOIN glpi_states st ON m.states_id = st.id
   LEFT JOIN glpi_infocoms i_w ON i_w.itemtype = 'Printer' AND i_w.items_id = m.id AND DATE_ADD(i_w.warranty_date, INTERVAL i_w.warranty_duration MONTH) >= NOW()
   WHERE st.name = 'EN FONCTION'
   AND pt.name = 'XXX'
   AND m.is_deleted = 0
";

$result = $DB->query($query);

while ($row = mysqli_fetch_assoc($result)) {
  $affected = $row['count'];
}




echo "<tr>";
echo "<td>Trimestrielle</td>";
echo "<td>Nombre total de copieurs en service sous garantie</td>";
echo "<td>".$affected."</td>";
echo "<td></td>";
echo "</tr>";



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


function trimestre(){


$mois_actuel = date("n");
 
if($mois_actuel < 4)
  return "T1";
else if($mois_actuel < 7)
  return "T2";
else if($mois_actuel < 10)
  return "T3";
else
  return "T4";


} 


function semestre(){


$mois_actuel = date("n");
 
if($mois_actuel < 6)
  return "S1";
else
  return "S2";


} 

?>