<?php
class PluginTicketcleanerMenu extends CommonGLPI {
   static $rightname = 'entity';

   static function getMenuName() {
      return __("Ticket Cleaner", "ticketcleaner");
   }

   static function getMenuContent() {

      if (!Session::haveRight('entity', READ)) {
         return;
      }

      $front_page = "/plugins/ticketcleaner/front";
      $menu = array();
      $menu['title'] = self::getMenuName();
      $menu['page']  = "$front_page/filter.php";

      $itemtypes = array('PluginTicketcleanerFilter' => 'ticketcleanerfilter');

      foreach ($itemtypes as $itemtype => $option) {
         $menu['options'][$option]['title']           = $itemtype::getTypeName(Session::getPluralNumber());
         switch( $itemtype ) {
            case 'PluginTicketcleanerFilter':
               $menu['options'][$option]['page']            = $itemtype::getSearchURL(false);
               $menu['options'][$option]['links']['search'] = $itemtype::getSearchURL(false);
               if ($itemtype::canCreate()) {
                  $menu['options'][$option]['links']['add'] = $itemtype::getFormURL(false);
               }
               break ;
            default :
               $menu['options'][$option]['page']            = PluginTicketcleanerFilter::getSearchURL(false);
               break ;
         }

      }
      return $menu;
   }


}