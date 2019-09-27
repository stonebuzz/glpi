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

namespace Glpi\Inventory;

/**
 * Handle inventory request
 */
class Inventory
{
    const FULL_MODE = 0;
    const INCR_MODE = 1;

    /** @var integer */
   private $mode;
    /** @var array */
   private $raw_data = null;
    /** @var array */
   private $metadata;
    /** @var array */
   private $errors = [];

    /**
     * @param mixed   $data   Inventory data, optionnal
     * @param integer $mode   One of self::*_MODE
     * @param integer $format One of Request::*_MODE
     */
   public function __construct($data = null, $mode = self::FULL_MODE, $format = Request::JSON_MODE) {
       $this->mode = $mode;

      if (null !== $data) {
          $this->setData($data, $format);
          $this->extractMetadata();
          $this->doInventory();
      }
   }

    /**
     * Set data, and convert them if we're using legacy format
     *
     * @param mixed   $data   Inventory data, optionnal
     * @param integer $format One of self::*_FORMAT
     *
     * @return boolean
     */
   public function setData($data, $format = Request::JSON_MODE) :bool {
       $converter = new Converter;
      if (Request::XML_MODE === $format) {
          //convert legacy format
          $data = $converter->convert($data->asXML());
      }

      try {
          $converter->validate($data);
      } catch (\RuntimeException $e) {
          $this->errors[] = $e->getMessage();
          \Toolbox::logError($e->printStackTrace());
          return false;
      }

       $this->raw_data = json_decode($data);
       return true;
   }

    /**
     * Prepare inventory data
     *
     * @return array
     */
   public function extractMetadata() :array {
       //check
      if ($this->inError()) {
          throw new \RuntimeException('Previous error(s) exists!');
      }

       $this->metadata = [
           'agent_id'  => $this->raw_data['deviceid'],
           'version'   => $this->raw_data['versionclient']
       ];

       return $this->metadata;
   }

    /**
     * Do inventory
     *
     * @return array
     */
   public function doInventory() {
       //check
      if ($this->inError()) {
          throw new \RuntimeException('Previous error(s) exists!');
      }

      try {
          $DB->beginTransaction();

          //TODO: the magic!
          $this->errors[] = 'Inventory is not yet implemented';

          $DB->commit();
      } catch (\Exception $e) {
          $DB->rolback();
      }

       return [];
   }

    /**
     * Get error
     *
     * @return array
     */
   public function getErrors() :array {
       return $this->errors;
   }

    /**
     * Check if erorrs has been throwed
     *
     * @return boolean
     */
   public function inError() :bool {
       return (bool)count($this->errors);
   }
}
