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
// Purpose of file: script to be used to purge logos (or any images) from DB
// ----------------------------------------------------------------------

// Ensure current directory as run command prompt
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));


define('DO_NOT_CHECK_HTTP_REFERER', 1);
define('GLPI_ROOT', '../..');
include (GLPI_ROOT . "/inc/includes.php");

include_once( 'hook.php' );
loadSha1IntoDB(); // to be sure sha1 are up to date

// get hashes from DB
$res = $DB->request('glpi_plugin_ticketcleaner_picturehashes');

foreach ($res as $file_hash) {
   // search if this document is already in the DB

   $doc = new Document();

   $criteria = ['sha1sum' => $file_hash['hash'] ];

   foreach ($DB->request($doc->getTable(), $criteria) as $data) {
      $doc->fields = $data;
      echo "Document: ".$doc->fields['users_id']." - ".$doc->fields['id']." - ".$doc->fields['tickets_id']." - ".$doc->fields['name']." - ".$doc->fields['filename']." - ".$doc->fields['filepath'];
      if ($doc->fields['users_id'] == 0) { // 0 means cron_mailgate
         if ($doc->deleteFromDB(1)) {
            // logs history
            $changes[0] = 0;
            $changes[1] = $changes[2] = "";
            Log::history($doc->fields["id"], $doc->getType(), $changes, 0, Log::HISTORY_DELETE_ITEM);
            echo " --> Deleted\n";
         } else {
            echo " --> Not Deleted\n";
         }
      } else {
         echo " --> Kept\n";
      }

   }

}
