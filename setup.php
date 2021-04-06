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


// ----------------------------------------------------------------------
// Original Author of file: Olivier Moron
// Purpose of file: Provides cleaning of Ticket, for title, description
//                  and followups.
//                  It cleans text for creation and edition (from email or from web interface)
//                  and it cleans attached pictures to emails
//                  It has been succesfully tested with plain TEXT and HTML emails
// ----------------------------------------------------------------------
define ("PLUGIN_TICKETCLEANER_VERSION", "3.0.0");

/**
 * Summary of plugin_init_ticketcleaner
 * Initializes class, and plugin hooks
 */
function plugin_init_ticketcleaner() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $DEFAULT_PLURAL_NUMBER;

   if ((!isset($_SESSION["glpicronuserrunning"]) || (Session::getLoginUserID() != $_SESSION["glpicronuserrunning"])) && !isset($_SESSION['glpiticketcleanertranslationmode'])) {
      $_SESSION['glpiticketcleanertranslationmode'] = 0;
   }

   Plugin::registerClass('PluginTicketCleaner', ['classname' => 'PluginTicketCleaner']);

   $PLUGIN_HOOKS['csrf_compliant']['ticketcleaner'] = true;

   $PLUGIN_HOOKS['pre_item_add']['ticketcleaner'] = [
         'Ticket' => ['PluginTicketCleaner', 'plugin_pre_item_add_ticketcleaner'],
         'ITILFollowup' => ['PluginTicketCleaner', 'plugin_pre_item_add_ticketcleaner_followup']
   ];
   $PLUGIN_HOOKS['pre_item_update']['ticketcleaner'] = [
         'Ticket' => ['PluginTicketCleaner', 'plugin_pre_item_update_ticketcleaner'],
         'ITILFollowup' => ['PluginTicketCleaner', 'plugin_pre_item_update_ticketcleaner_followup']
   ];

   $plugin = new Plugin();
   if ($plugin->isInstalled('ticketcleaner')
       && $plugin->isActivated('ticketcleaner')
       && Session::getLoginUserID()
       && Config::canUpdate()) {

      // show tab in user config to show translation switch
      Plugin::registerClass('PluginTicketcleanerUser',
                            ['addtabon' => ['Preference', 'User']]);

      // Display a menu entry
      $PLUGIN_HOOKS['menu_toadd']['ticketcleaner'] = ['config' => 'PluginTicketcleanerMenu'];

      // if translation mode is ON, then add translation xx_XX fake language to session
      if (isset( $_SESSION['glpiticketcleanertranslationmode'] ) && $_SESSION['glpiticketcleanertranslationmode']) {
         $PLUGIN_HOOKS['add_javascript']['ticketcleaner'] = ['js/locales.js'];
         $CFG_GLPI["languages"]['xx_XX']= ['Translation', 'xx_XX.mo', 'xx', 'xx', 'translation' , $DEFAULT_PLURAL_NUMBER];
         $trytoload = 'en_GB';
         if (isset($_SESSION['glpilanguage'])) {
            $trytoload = $_SESSION["glpilanguage"];
         }

         // If not set try default lang file
         if (empty($trytoload)) {
            $trytoload = $CFG_GLPI["language"];
         }
         Plugin::loadLang( 'ticketcleaner', 'xx_XX', $trytoload );
      }
   }

}


/**
 * Summary of plugin_version_ticketcleaner
 * @return name and version of the plugin
 */
function plugin_version_ticketcleaner() {
   //global $LANG;

   return  ['name'           => 'Ticket Cleaner',
               'version'        => PLUGIN_TICKETCLEANER_VERSION,
               'author'         => 'Olivier Moron',
               'license'        => 'GPLv3+',
               'homepage'       => 'https://github.com/tomolimo/ticketcleaner',
               'requirements'   => [
                  'glpi'   => [
                     'min' => '9.5',
                     'max' => '9.6'
                  ],
               ]
            ];
}


/**
 * Summary of plugin_ticketcleaner_check_prerequisites
 * @return false when GLPI version is not ok!
 */
function plugin_ticketcleaner_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.5', 'lt') || version_compare(GLPI_VERSION, '9.6', 'ge')) {
      echo "This plugin requires GLPI >= 9.5 and < 9.6";
      return false;
   }
   return true;
}

/**
 * Summary of plugin_ticketcleaner_check_config
 * @return true
 */
function plugin_ticketcleaner_check_config() {
   return true;
}
