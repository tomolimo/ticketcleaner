<?php
/*
 * -------------------------------------------------------------------------
Ticket Cleaner plugin
Copyright (C) 2016-2021 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
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
      $menu = [];
      $menu['title'] = self::getMenuName();
      $menu['page']  = "$front_page/filter.php";

      $itemtypes = ['PluginTicketcleanerFilter' => 'ticketcleanerfilter'];

      foreach ($itemtypes as $itemtype => $option) {
         $menu['options'][$option]['title']           = $itemtype::getTypeName(Session::getPluralNumber());
         switch ($itemtype) {
            case 'PluginTicketcleanerFilter':
               $menu['options'][$option]['page']            = $itemtype::getSearchURL(false);
               $menu['options'][$option]['links']['search'] = $itemtype::getSearchURL(false);
               if ($itemtype::canCreate()) {
                  $menu['options'][$option]['links']['add'] = $itemtype::getFormURL(false);
               }
               break;
            default :
               $menu['options'][$option]['page']            = PluginTicketcleanerFilter::getSearchURL(false);
               break;
         }

      }
      return $menu;
   }


}
