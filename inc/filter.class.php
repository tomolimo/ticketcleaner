<?php

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


   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return __('Filters','ticketcleaner');
      }
      return __('Filter','ticketcleaner');
   }

   /**
    * Summary of getSearchOptions
    * @return mixed
    */
   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = __('Filter','ticketcleaner');

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = __('Name');
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['searchtype']           = 'contains';
      $tab[1]['massiveaction']        = false;
      $tab[1]['itemlink_type'] = $this->getType();

      $tab[8]['table']         = $this->getTable();
      $tab[8]['field']         = 'is_active';
      $tab[8]['name']          = __('Active');
      $tab[8]['massiveaction'] = true;
      $tab[8]['datatype']      = 'bool';

      $tab[4]['table']        = $this->getTable();
      $tab[4]['field']        =  'comment';
      $tab[4]['name']         =  __('Comments');
      $tab[4]['massiveaction'] = true;
      $tab[4]['datatype']     =  'text';

      $tab[19]['table']               = $this->getTable();
      $tab[19]['field']               = 'date_mod';
      $tab[19]['name']                = __('Last update');
      $tab[19]['datatype']            = 'datetime';
      $tab[19]['massiveaction']       = false;

      //$tab[802]['table']               = $this->getTable();
      //$tab[802]['field']               = 'css_selector_value';
      //$tab[802]['name']                = __('Value CSS selector', 'ticketcleaner');
      //$tab[802]['massiveaction']       = false;
      //$tab[802]['datatype']            = 'dropdown';

      $tab[900]['table']               = $this->getTable();
      $tab[900]['field']               = 'type';
      $tab[900]['name']                = __('Type', 'ticketcleaner');
      $tab[900]['massiveaction']       = false;
      $tab[900]['searchtype']        = 'equals';
      $tab[900]['datatype']          = 'specific';

      $tab[901]['table']               = $this->getTable();
      $tab[901]['field']               = 'order';
      $tab[901]['name']                = __('Order', 'ticketcleaner');
      $tab[901]['massiveaction']       = false;
      //$tab[901]['searchtype']        = 'equals';

      $tab[902]['table']               = $this->getTable();
      $tab[902]['field']               = 'regex';
      $tab[902]['name']                = __('RegEx', 'ticketcleaner');
      $tab[902]['massiveaction']       = false;

      $tab[903]['table']               = $this->getTable();
      $tab[903]['field']               = 'replacement';
      $tab[903]['name']                = __('Replacement', 'ticketcleaner');
      $tab[903]['massiveaction']       = false;


      return $tab;
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
    **/
   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
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
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
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
   static function dropdownType($name, $options=array()) {

      $params['value']       = 0;
      $params['toadd']       = array();
      $params['on_change']   = '';
      $params['display']     = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $items = array();
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
            return __('Title','ticketcleaner');

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
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (static::canView()) {
         $nb = 0;
         switch ($item->getType()) {

            case 'PluginTicketcleanerFilter' :
               return PluginTicketcleanerFilter::getTypeName(Session::getPluralNumber());
         }
      }
      return '';
   }


   function defineTabs($options=array()) {

      //        $ong = array('empty' => $this->getTypeName(1));
      $ong = array();
      $this->addDefaultFormTab($ong);
      //$this->addStandardTab(__CLASS__, $ong, $options);

      //$this->addStandardTab('PluginTicketcleanerFilter', $ong, $options);

      return $ong;
   }

   function showForm ($ID, $options=array('candel'=>false)) {
      global $DB, $CFG_GLPI, $LANG;

      if ($ID > 0) {
         $this->check($ID,READ);
      }

      $canedit = $this->can($ID,UPDATE);
      $options['canedit'] = $canedit ;

      $this->initForm($ID, $options);


      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Name")."&nbsp;:</td>";
      echo "<td><input type='text' size='50' maxlength=250 name='name' value='".htmlentities($this->fields["name"], ENT_QUOTES)."'></td>";
      echo "<td rowspan='4' class='middle'>".__("Comments")."&nbsp;:</td>";
      echo "<td class='center middle' rowspan='4'><textarea cols='40' rows='4' name='comment' >".htmlentities($this->fields["comment"], ENT_QUOTES)."</textarea></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Active")."&nbsp;:</td>";
      echo "<td>" ;
      Html::showCheckbox(array('name'           => 'is_active',
                                     'checked'        => $this->fields['is_active']
                                     ));
      echo "</td></tr>";

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('Type', 'ticketcleaner')."&nbsp;:</td>";
      //echo "<td><input type='text' size='50' maxlength=200 name='type' value='".$this->fields["type"]."'></td>";
      echo "<td>";
      $opt = array('value' => $this->fields["type"]);
      self::dropdownType('type', $opt);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('Order', 'ticketcleaner')."&nbsp;:</td>";
      echo "<td><input type='text' size='10' maxlength=10 name='order' value='".$this->fields["order"]."'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__('RegEx', 'ticketcleaner')."&nbsp;:</td>";

      //echo "<td colspan=3><input type='text' size='150' maxlength=255 name='regex' value='".htmlentities($this->fields["regex"], ENT_QUOTES)."'></td>";
      echo "<td colspan=3><textarea cols='150' rows='7' name='regex' >".htmlentities($this->fields["regex"], ENT_QUOTES)."</textarea></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Replacement", 'ticketcleaner')."&nbsp;:</td>";
      //echo "<td colspan=3><input type='text' size='150' maxlength=255 name='replacement' value='". htmlentities($this->fields["replacement"], ENT_QUOTES)."'></td>";
      echo "<td colspan=3><textarea cols='150' rows='7' name='replacement' >".htmlentities($this->fields["replacement"], ENT_QUOTES)."</textarea></td>";
      echo "</tr>";

      if( version_compare(GLPI_VERSION,'9.1','lt') ) {
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
      //$this->addDivForTabs();

   }

   function prepareInputForAdd($input) {
      if(isset( $input['regex'] ) ) {
         $input['regex'] = html_entity_decode( $input['regex']) ;
      }
      return $input ;
   }

   function prepareInputForUpdate($input) {
      if(isset( $input['regex'] ) ) {
         $input['regex'] = html_entity_decode( $input['regex']) ;
      }

      return $input ;
   }


}

