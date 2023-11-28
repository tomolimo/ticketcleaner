<?php
/*
 * -------------------------------------------------------------------------
Ticket Cleaner plugin
Copyright (C) 2016-2023 by Raynet SAS a company of A.Raymond Network.

https://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Ticket Cleaner plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this plugin. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 */

use Glpi\Toolbox\Sanitizer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * process short summary.
 *
 * process description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginTicketcleanerFilter extends CommonDBTM {

   static $rightname = 'entity';


   const DESCRIPTION_TYPE = 0;
   const TITLE_TYPE   = 1;


   static function getTypeName($nb = 0) {
      global $LANG;

      if ($nb>1) {
         return __('Filters', 'ticketcleaner');
      }
      return __('Filter', 'ticketcleaner');
   }

   /**
     * Summary of rawSearchOptions
    * @return mixed
    */
   function rawSearchOptions() {
      global $LANG;

      $tab = [];

      $tab[] = [
              'id'                 => 'common',
              'name'               =>__('Filter', 'ticketcleaner')
           ];

      $tab[] = [
        'id'                 => '1',
        'table'              => $this->getTable(),
        'field'              => 'name',
        'name'               => __('Name'),
        'datatype'           => 'itemlink',
        'searchtype'         => 'contains',
        'massiveaction'      => false,
        'itemlink_type'      => 'PluginTicketcleanerFilter'
        ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'massiveaction'      => true,
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'massiveaction'      => true,
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '19',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '900',
         'table'              => $this->getTable(),
         'field'              => 'type',
         'name'               => __('Type', 'ticketcleaner'),
         'massiveaction'      => false,
         'searchtype'         => 'equals',
         'datatype'           => 'specific'
      ];

      $tab[] = [
         'id'                 => '901',
         'table'              => $this->getTable(),
         'field'              => 'order',
         'name'               => __('Order', 'ticketcleaner'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '902',
         'table'              => $this->getTable(),
         'field'              => 'regex',
         'name'               => __('RegEx', 'ticketcleaner'),
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '903',
         'table'              => $this->getTable(),
         'field'              => 'replacement',
         'name'               => __('Replacement', 'ticketcleaner'),
         'massiveaction'      => false
      ];

      return $tab;
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
    **/
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {

         case 'type':
            return self::getFilterTypeName($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name            (default '')
    * @param $values          (default '')
    * @param $options   array
    *
    * @return string
    **/
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      switch ($field) {

         case 'type':
            $options['value'] = $values[$field];
            return self::dropdownType($name, $options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }



   /**
    * Dropdown of ticket type
    *
    * @param $name            select name
    * @param $options   array of options:
    *    - value     : integer / preselected value (default 0)
    *    - toadd     : array / array of specific values to add at the begining
    *    - on_change : string / value to transmit to "onChange"
    *    - display   : boolean / display or get string (default true)
    *
    * @return string id of the select
    **/
   static function dropdownType($name, $options = []) {

      $params['value']       = 0;
      $params['toadd']       = [];
      $params['on_change']   = '';
      $params['display']     = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $items = [];
      if (count($params['toadd']) > 0) {
         $items = $params['toadd'];
      }

      $items += self::getFilterTypes();

      return Dropdown::showFromArray($name, $items, $params);
   }

   /**
    * Get ticket types
    *
    * @return array of types
    **/
   static function getFilterTypes() {

      $options[self::DESCRIPTION_TYPE] = self::getFilterTypeName(self::DESCRIPTION_TYPE);
      $options[self::TITLE_TYPE]   = self::getFilterTypeName(self::TITLE_TYPE);

      return $options;
   }


   /**
    * Get ticket type Name
    *
    * @param $value type ID
    **/
   static function getFilterTypeName($value) {

      switch ($value) {
         case self::DESCRIPTION_TYPE :
            return __('Description', 'ticketcleaner');

         case self::TITLE_TYPE :
            return __('Title', 'ticketcleaner');

         default :
            // Return $value if not defined
            return $value;
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonGLPI::getTabNameForItem()
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (static::canView()) {
         $nb = 0;
         switch ($item->getType()) {

            case 'PluginTicketcleanerFilter' :
               return PluginTicketcleanerFilter::getTypeName(Session::getPluralNumber());
         }
      }
      return '';
   }


   function defineTabs($options = []) {

      //        $ong = array('empty' => $this->getTypeName(1));
      $ong = [];
      $this->addDefaultFormTab($ong);
      //$this->addStandardTab(__CLASS__, $ong, $options);

      //$this->addStandardTab('PluginTicketcleanerFilter', $ong, $options);

      return $ong;
   }

   function showForm ($ID, $options = ['candel'=>false]) {
      global $DB, $CFG_GLPI, $LANG;

      if ($ID > 0) {
         $this->check($ID, READ);
      }

      $canedit = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      $this->initForm($ID, $options);

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Name")."&nbsp;:</td>";
      echo "<td><input type='text' size='50' maxlength=250 name='name' value='".$this->fields["name"]."'></td>";
      echo "<td rowspan='4' class='middle'>".__("Comments")."&nbsp;:</td>";
      echo "<td class='center middle' rowspan='4'><textarea cols='40' rows='4' name='comment' >".$this->fields["comment"]."</textarea></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Active")."&nbsp;:</td>";
      echo "<td>";
      Html::showCheckbox(['name'    => 'is_active',
                          'checked' => $this->fields['is_active']
                         ]);
      echo "</td></tr>";

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('Type', 'ticketcleaner')."&nbsp;:</td>";
      echo "<td>";
      $opt = ['value' => $this->fields["type"]];
      self::dropdownType('type', $opt);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('Order', 'ticketcleaner')."&nbsp;:</td>";
      echo "<td><input type='text' size='10' maxlength=10 name='order' value='".$this->fields["order"]."'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('RegEx', 'ticketcleaner')."&nbsp;:</td>";

      echo "<td colspan=3><textarea cols='150' rows='7' name='regex' >".Sanitizer::decodeHtmlSpecialChars($this->fields["regex"])."</textarea></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Replacement", 'ticketcleaner')."&nbsp;:</td>";
      echo "<td colspan=3><textarea cols='150' rows='7' name='replacement' >".Sanitizer::decodeHtmlSpecialChars($this->fields["replacement"])."</textarea></td>";
      echo "</tr>";

      if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td >".__('Last update')."&nbsp;:</td><td>";
         echo Html::convDateTime($this->fields["date_mod"]);
         echo "</td></tr>";
      }

      echo "<tr><td>&nbsp;";
      echo "</td></tr>";

      echo "<tr>";
      echo "</tr>";

      $this->showFormButtons($options );
   }

}

