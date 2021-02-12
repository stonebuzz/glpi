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

// Check PHP version not to have trouble
// Need to be the very fist step before any include
if (version_compare(PHP_VERSION, '7.2.0') < 0) {
   die('PHP >= 7.2.0 required');
}


use Glpi\Event;

//Load GLPI constants
define('GLPI_ROOT', __DIR__);
include (GLPI_ROOT . "/inc/based_config.php");

define('DO_NOT_CHECK_HTTP_REFERER', 1);

// If config_db doesn't exist -> start installation
if (!file_exists(GLPI_CONFIG_DIR . "/config_db.php")) {
   Html::redirect("install/install.php");
   die();

} else {
   $TRY_OLD_CONFIG_FIRST = true;
   include (GLPI_ROOT . "/inc/includes.php");
   $_SESSION["glpicookietest"] = 'testcookie';

   // For compatibility reason
   if (isset($_GET["noCAS"])) {
      $_GET["noAUTO"] = $_GET["noCAS"];
   }

   if (!isset($_GET["noAUTO"])) {
      Auth::redirectIfAuthenticated();
   }
   Auth::checkAlternateAuthSystems(true, isset($_GET["redirect"])?$_GET["redirect"]:"");

   // Send UTF8 Headers
   header("Content-Type: text/html; charset=UTF-8");

   // Start the page
   echo "<!DOCTYPE html>\n";
   echo "<html lang=\"{$CFG_GLPI["languages"][$_SESSION['glpilanguage']][3]}\" class='loginpage'>";
   echo '<head><title>'.__('GLPI - Authentication').'</title>'."\n";
   echo '<meta charset="utf-8"/>'."\n";
   echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
   echo '<link rel="shortcut icon" type="images/x-icon" href="'.$CFG_GLPI["root_doc"].
          '/pics/favicon.ico" />';

   // auto desktop / mobile viewport
   echo "<meta name='viewport' content='width=device-width, initial-scale=1'/>";

   // Appel CSS
   echo Html::scss('css/styles');
   if (isset($_SESSION['glpihighcontrast_css']) && $_SESSION['glpihighcontrast_css']) {
      echo Html::scss('css/highcontrast');
   }
   $theme = isset($_SESSION['glpipalette']) ? $_SESSION['glpipalette'] : 'auror';
   echo Html::scss('css/palettes/' . $theme);
   // external libs CSS
   echo Html::css('public/lib/base.css');

   // Custom CSS for root entity
   $entity = new Entity();
   $entity->getFromDB('0');
   echo $entity->getCustomCssTag();

   // CFG
   echo Html::getCoreVariablesForJavascript();

   echo Html::script("public/lib/base.js");
   echo Html::script("public/lib/fuzzy.js");
   echo Html::script('js/common.js');

   echo "</head>";

   echo "<body>";
   echo "<div id='firstboxlogin'>";
   echo "<h1 id='logo_login'><img src='".$CFG_GLPI['root_doc']."/pics/login_logo_glpi.png' alt='GLPI' title='GLPI' /></h1>";
   echo "<div id='text-login'>";
   echo nl2br(Toolbox::unclean_html_cross_side_scripting_deep($CFG_GLPI['text_login']));
   echo "</div>";

   echo "<div id='boxlogin'>";

   $_SESSION['namfield'] = $namfield = uniqid('fielda');
   $_SESSION['pwdfield'] = $pwdfield = uniqid('fieldb');
   $_SESSION['rmbfield'] = $rmbfield = uniqid('fieldc');

   // Other CAS
   if (isset($_GET["noAUTO"])) {
      echo "<input type='hidden' name='noAUTO' value='1' />";
   }
   // redirect to ticket
   if (isset($_GET["redirect"])) {
      Toolbox::manageRedirect($_GET["redirect"]);
      echo '<input type="hidden" name="redirect" value="'.Html::entities_deep($_GET['redirect']).'"/>';
   }


   // Add dropdown for auth (local, LDAPxxx, LDAPyyy, imap...)
   if ($CFG_GLPI['display_login_source']) {
      Auth::dropdownLogin();
   }


   $js = "$(function() {
      $('#login_name').focus();
   });";
   echo Html::scriptBlock($js);

   echo "</div>";  // end login box


   echo "<div class='error'>";
   echo "<noscript><p>";
   echo __('You must activate the JavaScript function of your browser');
   echo "</p></noscript>";

   if (isset($_GET['error']) && isset($_GET['redirect'])) {
      switch ($_GET['error']) {
         case 1 : // cookie error
            echo __('You must accept cookies to reach this application');
            break;

         case 2 : // GLPI_SESSION_DIR not writable
            echo __('Checking write permissions for session files');
            break;

         case 3 :
            echo __('Invalid use of session ID');
            break;
      }
   }
   echo "</div>";

   // Display FAQ is enable
   if ($CFG_GLPI["use_public_faq"]) {
      echo '<div id="box-faq">'.
            '<a href="front/helpdesk.faq.php">[ '.__('Access to the Frequently Asked Questions').' ]';
      echo '</a></div>';
   }

   echo "<div id='display-login'>";
   Plugin::doHook('display_login');
   echo "</div>";


   echo "</div>"; // end contenu login

   echo "<div id='footer-login' class='home'>" . Html::getCopyrightMessage(false) . "</div>";

}
// call cron
if (!GLPI_DEMO_MODE) {
   CronTask::callCronForce();
}

echo "</body></html>";
