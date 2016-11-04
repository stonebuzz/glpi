<?php

      class FollowupNotify extends CommonDBTM {

         static function setNotifyControl() {

            // Booleans
            $users_requester     = 0;
            $groups_requester    = 0;
            $users_watcher       = 0;
            $groups_watcher      = 0;
            $users_assigned      = 0;
            $groups_assigned     = 0;
            $suppliers_assigned  = 0;

            if(isset($_POST['_requester_control'])){
               // Check follower allowed notifications
               switch ($_POST['_requester_control']) {
                  case '_users' :
                     $users_requester     = 1;
                     break;
                  case '_groups' :
                     $groups_requester    = 1;
                     break;
                  case '_users_groups' :
                     $users_requester     = 1;
                     $groups_requester    = 1;
                     break;
               }               
            }


            if(isset($_POST['_watcher_control'])){
               switch ($_POST['_watcher_control']) {
                  case '_users' :
                     $users_watcher       = 1;
                     break;
                  case '_groups' :
                     $groups_watcher      = 1;
                     break;
                  case '_users_groups' :
                     $users_watcher       = 1;
                     $groups_watcher      = 1;
                     break;
               }
            }

            if(isset($_POST['_assigned_control'])){
               switch ($_POST['_assigned_control']) {
                  case '_users' :
                     $users_assigned      = 1;
                     break;
                  case '_groups' :
                     $groups_assigned     = 1;
                     break;
                  case '_suppliers' :
                     $suppliers_assigned  = 1;
                     break;
                  case '_users_groups' :
                     $users_assigned      = 1;
                     $groups_assigned     = 1;
                     break;
                  case '_users_suppliers' :
                     $users_assigned      = 1;
                     $suppliers_assigned  = 1;
                     break;
                  case '_groups_suppliers' :
                     $groups_assigned     = 1;
                     $suppliers_assigned  = 1;
                     break;
                  case '_users_groups_suppliers' :
                     $users_assigned      = 1;
                     $groups_assigned     = 1;
                     $suppliers_assigned  = 1;
                     break;
               }
            }

            // Define "notify_control" array
            $aNotify = array(
               '_users_id_requester'   => $users_requester,
               '_groups_id_requester'  => $groups_requester,
               '_users_id_observer'    => $users_watcher,
               '_groups_id_observer'   => $groups_watcher,
               '_users_id_assign'      => $users_assigned,
               '_groups_id_assign'     => $groups_assigned,
               '_suppliers_id_assign'  => $suppliers_assigned
            );

            return json_encode($aNotify);

         }

         static function getNotifyControl($config=null) {
            // Decoding "notify_control" from JSON format
            return json_decode($config);
         }

         static function getUsersNotifyControl() {
            $user = new User();
            // Get current user from DB
            $user->getFromDB(Session::getLoginUserID());
            // Get + Decode + Return current user's personnal notifying configuration
            return self::getNotifyControl($user->getField('notify_control'));
         }

         static function showForm($form_config=null, $form_type=null) {

            // Define lists options
            $options_requester = array(
               '_no'                      => __('No'),
               '_users'                   => __('User').'s',
               '_groups'                  => __('Group').'s',
               '_users_groups'            => __('User').'s '.__('and').' '.__('Group').'s'
            );

            $options_watcher = array(
               '_no'                      => __('No'),
               '_users'                   => __('User').'s',
               '_groups'                  => __('Group').'s',
               '_users_groups'            => __('User').'s '.__('and').' '.__('Group').'s'
            );

            $options_assigned = array(
               '_no'                      => __('No'),
               '_users'                   => __('User').'s',
               '_groups'                  => __('Group').'s',
               '_suppliers'               => __('Supplier').'s',
               '_users_groups'            => __('User').'s '.__('and').' '.__('Group').'s',
               '_users_suppliers'         => __('User').'s '.__('and').' '.__('Supplier').'s',
               '_groups_suppliers'        => __('Group').'s '.__('and').' '.__('Supplier').'s',
               '_users_groups_suppliers' 
                  => __('User').'s, '.__('Group').'s '.__('and').' '.__('Supplier').'s'
            );

            // Get notify_control by form type
            switch ($form_config) {
               // Get followup notify configuration
               case 'followup_update' :
                  $fup = new TicketFollowUp();
                  // Get current Followup
                  $fup->getFromDB($_POST['id']);

                  if($fup->getField('notify_control') == null ){
                     $default = Config::getConfigurationValues('core', array('notify_control'));
                     // Attempting to get followup's notification configs
                     $notify_control = self::getNotifyControl($default['notify_control']);
                  }else{
                     // Attempting to get followup's notification configs
                     $notify_control = self::getNotifyControl($fup->getField('notify_control'));

                  }

                  break;
               // Get GLPi's general notifying configuration
               case 'general_config' :
                  // Get default config from DB
                  $default = Config::getConfigurationValues('core', array('notify_control'));
                  $notify_control = self::getNotifyControl($default['notify_control']);
                  break;
               // Get current user's personnal notifying configuration
               case 'user_config' :
                  $user = new User();
                  // Get user from DB
                  $user->getFromDB(Session::getLoginUserID());
                  // Get user's config
                  $notify_control = self::getNotifyControl($user->getField('notify_control'));
                  // If user's config isn't set, get general config
                  if (!isset($notify_control)) {
                     $default = Config::getConfigurationValues('core', array('notify_control'));
                     $notify_control = self::getNotifyControl($default['notify_control']);
                  }
                  break;
               // Get GLPi's general notifying configuration by default
               default :
                  // Get default config from DB
                  $default = Config::getConfigurationValues('core', array('notify_control'));
                  $notify_control = self::getNotifyControl($default['notify_control']);
                  break;
            }

            // Set default options selected to prevent exceptions
            $requester_value  = '_no';
            $observer_value   = '_no';
            $assigned_value   = '_no';

            // Define actors notification booleans
            $notify_user_assign     = $notify_control->_users_id_assign;
            $notify_supplier_assign = $notify_control->_suppliers_id_assign;
            $notify_group_assign    = $notify_control->_groups_id_assign;
            $notify_user_requester  = $notify_control->_users_id_requester;
            $notify_group_requester = $notify_control->_groups_id_requester;
            $notify_user_observer   = $notify_control->_users_id_observer;
            $notify_group_observer  = $notify_control->_groups_id_observer;

            // REQUESTERS config
            if ($notify_user_requester == 1 &&
                  $notify_group_requester == 1) {
               $requester_value = '_users_groups';
            }
            else if ($notify_user_requester == 1) {
               $requester_value = '_users';
            }
            else if ($notify_group_requester == 1) {
               $requester_value = '_groups';
            }

            // OBSERVERS config
            if ($notify_user_observer == 1 &&
                  $notify_group_observer == 1) {
               $observer_value = '_users_groups';
            }
            else if ($notify_user_observer == 1) {
               $observer_value = '_users';
            }
            else if ($notify_group_observer == 1) {
               $observer_value = '_groups';
            }

            // ASSIGNED config
            if ($notify_user_assign == 1 &&
                  $notify_group_assign == 1 &&
                  $notify_supplier_assign == 1) {
               $assigned_value = '_users_groups_suppliers';
            }
            else if ($notify_group_assign == 1 &&
                     $notify_supplier_assign == 1) {
               $assigned_value = '_groups_suppliers';
            }
            else if ($notify_user_assign == 1 &&
                     $notify_supplier_assign == 1) {
               $assigned_value = '_groups_suppliers';
            }
            else if ($notify_user_assign == 1 &&
                     $notify_group_assign == 1) {
               $assigned_value = '_users_groups';
            }
            else if ($notify_user_assign == 1) {
               $assigned_value = '_users';
            }
            else if ($notify_group_assign == 1) {
               $assigned_value = '_groups';
            }
            else if ($notify_supplier_assign == 1) {
               $assigned_value = '_suppliers';
            }

            if ($form_type == 'followup') {
               // Display form in followup
               echo "<tr><th colspan='2'>".__('Notifications')."</th></tr>";
               echo "<tr><td colspan='2'>";
               echo "<table width='100%'>";
               echo "<tr><td>".__('Assigned')."(s)</td><td>";
               Dropdown::showFromArray('_assigned_control',
                                       $options_assigned,
                                       array('value'=>$assigned_value));
               echo "</td></tr>";
               echo "<tr><td>".__('Requester')."(s)</td><td>";
               Dropdown::showFromArray('_requester_control',
                                       $options_requester,
                                       array('value'=>$requester_value));
               echo "</td></tr>";
               echo "<tr><td>".__('Watcher')."(s)</td><td>";
               Dropdown::showFromArray('_watcher_control',
                                       $options_watcher,
                                       array('value'=>$observer_value));
               echo "</td></tr></table>";
            }
            else {
               // Display form in configuration editor
               echo "<tr class='headerRow'><th colspan='4'>".__('Notifications')."</th></tr>";
               echo "<tr><td colspan='4'>";
               echo "<table class='tab_cadre_fixe'>";
               echo "<tr class='center'>";
               echo "<td>".__('Assigned')."(s)</td>";
               echo "<td>".__('Requester')."(s)</td>";
               echo "<td>".__('Watcher')."(s)</td>";
               echo "<tr class='center'>";
               echo "<td>";
               Dropdown::showFromArray('_assigned_control',
                                       $options_assigned,
                                       array('value'=>$assigned_value));
               echo "</td>";
               echo "<td>";
               Dropdown::showFromArray('_requester_control',
                                       $options_requester,
                                       array('value'=>$requester_value));
               echo "</td>";
               echo "<td>";
               Dropdown::showFromArray('_watcher_control',
                                       $options_watcher,
                                       array('value'=>$observer_value));
               echo "</td>";
               echo "</tr></table>";
            }

         }

      }

?>