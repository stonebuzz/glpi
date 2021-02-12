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

/**
 * Central class
**/
class Central extends CommonGLPI {


   static function getTypeName($nb = 0) {

      // No plural
      return __('Standard interface');
   }


   function defineTabs($options = []) {

      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         $tabs = [
            1 => __('Personal View'),
            2 => __('Group View'),
            3 => __('Global View'),
            4 => _n('RSS feed', 'RSS feeds', Session::getPluralNumber()),
         ];

         $grid = new Glpi\Dashboard\Grid('central');
         if ($grid->canViewOneDashboard()) {
            array_unshift($tabs, __('Dashboard'));
         }

         return $tabs;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 0 :
               $item->showGlobalDashboard();
               break;

            case 1 :
               $item->showMyView();
               break;

            case 2 :
               $item->showGroupView();
               break;

            case 3 :
               $item->showGlobalView();
               break;

            case 4 :
               $item->showRSSView();
               break;
         }
      }
      return true;
   }

   public function showGlobalDashboard() {
      echo "<table class='tab_cadre_central'>";
      Plugin::doHook('display_central');
      echo "</table>";

      self::showMessages();

      $default   = Glpi\Dashboard\Grid::getDefaultDashboardForMenu('central');
      $dashboard = new Glpi\Dashboard\Grid($default);
      $dashboard->show();
   }


   /**
    * Show the central global view
   **/
   static function showGlobalView() {

      $showticket  = Session::haveRight("ticket", Ticket::READALL);
      $showproblem = Session::haveRight("problem", Problem::READALL);

      echo "<table class='tab_cadre_central'><tr class='noHover'>";
      echo "<td class='top' width='50%'>";
      echo "<table class='central'>";
      echo "<tr class='noHover'><td>";
      if ($showticket) {
         Ticket::showCentralCount();
      } else {
         Ticket::showCentralCount(true);
      }
      if ($showproblem) {
         Problem::showCentralCount();
      }
      if (Contract::canView()) {
         Contract::showCentral();
      }
      echo "</td></tr>";
      echo "</table></td>";

      if (Session::haveRight("logs", READ)) {
         echo "<td class='top'  width='50%'>";

         //Show last add events
         Event::showForUser($_SESSION["glpiname"]);
         echo "</td>";
      }
      echo "</tr></table>";

      if ($_SESSION["glpishow_jobs_at_login"] && $showticket) {
         echo "<br>";
         Ticket::showCentralNewList();
      }
   }


   /**
    * Show the central personal view
   **/
   static function showMyView() {
      $showticket  = Session::haveRightsOr("ticket",
                                           [Ticket::READMY, Ticket::READALL, Ticket::READASSIGN]);

      $showproblem = Session::haveRightsOr('problem', [Problem::READALL, Problem::READMY]);

      echo "<table class='tab_cadre_central'>";

      Plugin::doHook('display_central');

      echo "<tr><th colspan='2'>";
      self::showMessages();
      echo "</th></tr>";

      echo "<tr class='noHover'><td class='top' width='50%'><table class='central'>";
      echo "<tr class='noHover'><td>";
      if (Session::haveRightsOr('ticketvalidation', TicketValidation::getValidateRights())) {
         Ticket::showCentralList(0, "tovalidate", false);
      }
      if ($showticket) {

         if (Ticket::isAllowedStatus(Ticket::SOLVED, Ticket::CLOSED)) {
            Ticket::showCentralList(0, "toapprove", false);
         }

         Ticket::showCentralList(0, "survey", false);

         Ticket::showCentralList(0, "validation.rejected", false);
         Ticket::showCentralList(0, "solution.rejected", false);
         Ticket::showCentralList(0, "requestbyself", false);
         Ticket::showCentralList(0, "observed", false);

         Ticket::showCentralList(0, "process", false);
         Ticket::showCentralList(0, "waiting", false);

         TicketTask::showCentralList(0, "todo", false);

      }
      if ($showproblem) {
         Problem::showCentralList(0, "process", false);
         ProblemTask::showCentralList(0, "todo", false);
      }
      echo "</td></tr>";
      echo "</table></td>";
      echo "<td class='top'  width='50%'><table class='central'>";
      echo "<tr class='noHover'><td>";
      Planning::showCentral(Session::getLoginUserID());
      Reminder::showListForCentral();
      if (Session::haveRight("reminder_public", READ)) {
         Reminder::showListForCentral(false);
      }
      echo "</td></tr>";
      echo "</table></td></tr></table>";
   }


   /**
    * Show the central RSS view
    *
    * @since 0.84
   **/
   static function showRSSView() {

      echo "<table class='tab_cadre_central'>";

      echo "<tr class='noHover'><td class='top' width='50%'>";
      RSSFeed::showListForCentral();
      echo "</td><td class='top' width='50%'>";
      if (RSSFeed::canView()) {
         RSSFeed::showListForCentral(false);
      } else {
         echo "&nbsp;";
      }
      echo "</td></tr>";
      echo "</table>";
   }


   /**
    * Show the central group view
   **/
   static function showGroupView() {

      $showticket = Session::haveRightsOr("ticket", [Ticket::READALL, Ticket::READASSIGN]);

      $showproblem = Session::haveRightsOr('problem', [Problem::READALL, Problem::READMY]);

      echo "<table class='tab_cadre_central'>";
      echo "<tr class='noHover'><td class='top' width='50%'><table class='central'>";
      echo "<tr class='noHover'><td>";
      if ($showticket) {
         Ticket::showCentralList(0, "process", true);
         TicketTask::showCentralList(0, "todo", true);
      }
      if (Session::haveRight('ticket', Ticket::READGROUP)) {
         Ticket::showCentralList(0, "waiting", true);
      }
      if ($showproblem) {
         Problem::showCentralList(0, "process", true);
         ProblemTask::showCentralList(0, "todo", true);
      }

      echo "</td></tr>";
      echo "</table></td>";
      echo "<td class='top' width='50%'><table class='central'>";
      echo "<tr class='noHover'><td>";
      if (Session::haveRight('ticket', Ticket::READGROUP)) {
         Ticket::showCentralList(0, "observed", true);
         Ticket::showCentralList(0, "toapprove", true);
         Ticket::showCentralList(0, "requestbyself", true);
      } else {
         Ticket::showCentralList(0, "waiting", true);
      }
      echo "</td></tr>";
      echo "</table></td></tr></table>";
   }


   static function showMessages() {
      global $DB, $CFG_GLPI;

      $warnings = [];

      $user = new User();
      $user->getFromDB(Session::getLoginUserID());
      if ($user->fields['authtype'] == Auth::DB_GLPI && $user->shouldChangePassword()) {
         $expiration_msg = sprintf(
            __('Your password will expire on %s.'),
            Html::convDateTime(date('Y-m-d H:i:s', $user->getPasswordExpirationTime()))
         );
         $warnings[] = $expiration_msg
            . ' '
            . '<a href="' . $CFG_GLPI['root_doc'] . '/front/updatepassword.php">'
            . __('Update my password')
            . '</a>';
      }

      if (Session::haveRight("config", UPDATE)) {
         $logins = User::checkDefaultPasswords();
         $user   = new User();
         if (!empty($logins)) {
            $accounts = [];
            foreach ($logins as $login) {
               $user->getFromDBbyNameAndAuth($login, Auth::DB_GLPI, 0);
               $accounts[] = $user->getLink();
            }
            $warnings[] = sprintf(__('For security reasons, please change the password for the default users: %s'),
                               implode(" ", $accounts));
         }

         //create a context to set timeout
         $context = stream_context_create([
            'http' => [
               'timeout' => 2.0
            ]
         ]);
         $protocol = 'http';
         if (isset($_SERVER['HTTPS'])) {
            $protocol = 'https';
         }
         $uri = $protocol . '://' . $_SERVER['SERVER_NAME'] . $CFG_GLPI['root_doc'];
         if ($fic = fopen($uri.'/files/_log/php-errors.log', 'r', false, $context)) {
            fclose($fic);
            $warnings[] = sprintf( __('Web access to the files directory should not be allowed')."\n".
               __('Check the .htaccess file and the web server configuration.')."\n");
         }

         if (file_exists(GLPI_ROOT . "/install/install.php")) {
            $warnings[] = sprintf(__('For security reasons, please remove file: %s'),
                               "install/install.php");
         }

         $myisam_tables = $DB->getMyIsamTables();
         if (count($myisam_tables)) {
            $warnings[] = sprintf(
               __('%1$s tables not migrated to InnoDB engine.'),
               count($myisam_tables)
            );
         }
         if ($DB->areTimezonesAvailable()) {
            $not_tstamp = $DB->notTzMigrated();
            if ($not_tstamp > 0) {
                $warnings[] = sprintf(
                    __('%1$s columns are not compatible with timezones usage.'),
                    $not_tstamp
                );
            }
         }
      }

      if ($DB->isSlave()
          && !$DB->first_connection) {
         $warnings[] = __('SQL replica: read only');
      }

      if (count($warnings)) {
         echo "<div class='warning'>";
         echo "<i class='fa fa-exclamation-triangle fa-5x'></i>";
         echo "<ul><li>" . implode('</li><li>', $warnings) . "</li></ul>";
         echo "<div class='sep'></div>";
         echo "</div>";
      }
   }

}
