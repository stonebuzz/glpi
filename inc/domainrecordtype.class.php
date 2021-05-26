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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class DomainRecordType extends CommonDropdown
{
   static $rightname = 'dropdown';

   static public $knowtypes = [
      [
         'id'        => 1,
         'name'      => 'A',
         'comment'   => 'Host address'
      ], [
         'id'        => 2,
         'name'      => 'AAAA',
         'comment'   => 'IPv6 host address'
      ], [
         'id'        => 3,
         'name'      => 'ALIAS',
         'comment'   => 'Auto resolved alias'
      ], [
         'id'        => 4,
         'name'      => 'CNAME',
         'comment'   => 'Canonical name for an alias',
      ], [
         'id'        => 5,
         'name'      => 'MX',
         'comment'   => 'Mail eXchange'
      ], [
         'id'        => 6,
         'name'      => 'NS',
         'comment'   => 'Name Server'
      ], [
         'id'        => 7,
         'name'      => 'PTR',
         'comment'   => 'Pointer'
      ], [
         'id'        => 8,
         'name'      => 'SOA',
         'comment'   => 'Start Of Authority',
      ], [
         'id'        => 9,
         'name'      => 'SRV',
         'comment'   => 'Location of service'
      ], [
         'id'        => 10,
         'name'      => 'TXT',
      'comment'    => 'Descriptive text'
      ]
   ];


   function getAdditionalFields() {
      return [
         [
            'name'  => 'fields',
            'label' => __('Fields'),
            'type'  => 'fields',
         ]
      ];
   }

   public function displaySpecificTypeField($ID, $field = [], $options = []) {
      $field_name  = $field['name'];
      $field_type  = $field['type'];
      $field_value = $this->fields[$field_name];

      switch ($field_type) {
         case 'fields':
            $printable = json_encode(json_decode($field_value), JSON_PRETTY_PRINT);
            echo '<textarea name="' . $field_name . '" cols="75" rows="25">' . $printable . '</textarea >';
            break;
      }
   }

   public function prepareInputForAdd($input) {
      if (!array_key_exists('fields', $input)) {
         $input['fields'] = '[]';
      } else {
         $input['fields'] = Toolbox::cleanNewLines($input['fields']);
      }

      if (!$this->validateFieldsDescriptor($input['fields'])) {
         return false;
      }

      return parent::prepareInputForAdd($input);
   }

   public function prepareInputForUpdate($input) {
      if (array_key_exists('fields', $input)) {
         $input['fields'] = Toolbox::cleanNewLines($input['fields']);
         if (!$this->validateFieldsDescriptor($input['fields'])) {
            return false;
         }
      }

      return parent::prepareInputForUpdate($input);
   }

   public function post_updateItem($history = 1) {
      global $DB;

      if (in_array('fields', $this->updates)) {
         $old_fields = self::decodeFields($this->oldvalues['fields']);
         $new_fields = self::decodeFields($this->fields['fields']);

         // Checks only for keys changes as fields order, label, placeholder or quote_value properties changes
         // should have no impact on object representation.
         $old_keys = array_column($old_fields, 'key');
         $new_keys = array_column($new_fields, 'key');
         sort($old_keys);
         sort($new_keys);

         if ($old_keys != $new_keys) {
            // Remove data stored as obj as properties changed.
            // Do not remove data stored as string as this representation may still be valid.
            $DB->update(
               DomainRecord::getTable(),
               ['data_obj' => null],
               [self::getForeignKeyField() => $this->fields['id']]
            );
         }
      }
   }

   /**
    * Validate fields descriptor.
    *
    * @param string $fields_str  Value of "fields" field (should be a JSON encoded string).
    *
    * @return bool
    */
   private function validateFieldsDescriptor($fields_str): bool {
      if (!is_string($fields_str)) {
         Session::addMessageAfterRedirect(__('Invalid JSON used to define fields.'), true, ERROR);
         return false;
      }

      $fields = self::decodeFields($fields_str);
      if (!is_array($fields)) {
         Session::addMessageAfterRedirect(__('Invalid JSON used to define fields.'), true, ERROR);
         return false;
      }

      foreach ($fields as $field) {
         if (!is_array($field)
             || !array_key_exists('key', $field) || !is_string($field['key'])
             || !array_key_exists('label', $field) || !is_string($field['label'])
             || (array_key_exists('placeholder', $field) && !is_string($field['placeholder']))
             || (array_key_exists('quote_value', $field) && !is_bool($field['quote_value']))
             || (array_key_exists('is_fqdn', $field) && !is_bool($field['is_fqdn']))
             || count(array_diff(array_keys($field), ['key', 'label', 'placeholder', 'quote_value', 'is_fqdn'])) > 0) {
            Session::addMessageAfterRedirect(
               __('Valid field descriptor properties are: key (string, mandatory), label (string, mandatory), placeholder (string, optionnal), quote_value (boolean, optional), is_fqdn (boolean, optional).'),
               true,
               ERROR
            );
            return false;
         }
      }

      return true;
   }

   /**
    * Decode JSON encoded fields.
    * Handle decoding of sanitized value.
    * Returns null if unable to decode.
    *
    * @param string $json_encoded_fields
    *
    * @return array|null
    */
   public static function decodeFields(string $json_encoded_fields): ?array {
      $fields = json_decode($json_encoded_fields, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
         $fields_str = stripslashes(preg_replace('/(\\\r|\\\n)/', '', $json_encoded_fields));
         $fields = json_decode($fields_str, true);
      }
      if (json_last_error() !== JSON_ERROR_NONE || !is_array($fields)) {
         return null;
      }

      return $fields;
   }

   static function getTypeName($nb = 0) {
      return _n('Record type', 'Records types', $nb);
   }

   public static function getDefaults() {
      return array_map(
         function($e) {
            $e['is_recursive'] = 1;
            return $e;
         },
         self::$knowtypes
      );
   }

}
